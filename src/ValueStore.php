<?php
/**
 * ValueStore
 * Copyright 2020 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @link        https://www.originphp.com
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

declare(strict_types=1);
namespace Origin\ValueStore;

use Countable;
use ArrayAccess;
use ArrayIterator;
use Origin\Xml\Xml;
use JsonSerializable;
use Origin\Yaml\Yaml;
use IteratorAggregate;
use InvalidArgumentException;
use Origin\ValueStore\Exception\ValueStoreException;

/**
 * ValueStore is key-value store
 */
class ValueStore implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable
{
    /**
     * ValueStore data
     *
     * @var array
     */
    private $data = [];

    /**
     * File where value-store is stored
     *
     * @var string
     */
    private $file = null;

    /**
     * Type of value-store json,php,xml,yml
     *
     * @var string
     */
    private $type;

    /**
     * XML Root e.g. root, value-store, record, settings
     *
     * @var string
     */
    private $root;

    /**
     * JSON excape slashes
     *
     * @var boolean
     */
    private $escape = false;

    /**
     * Extensions for detection
     */
    const TYPES = ['json','php','xml','yml'];

    /**
     * @param string $file
     * @param array $options
     *  type: how the data is stored. default:json xml, json, yml. Will be autodetected from extension
     *  root: name of root element for xml exports
     *  escape: option for JSON wether to escape forward slashes. From what I understand this is a security
     * measure to allow embedding `</script>` inside json.
     */
    public function __construct(string $file = null, array $options = [])
    {
        $options += [
            'type' => $file ? $this->detectType($file) : 'json',
            'root' => 'root',  // xml root name
            'escape' => $this->escape  // json option for escaping forward slashes
        ];

        $this->file = $file;
        $this->type = $options['type'];
        $this->root = $options['root'];
        $this->escape = $options['escape'];

        if (! in_array($options['type'], self::TYPES)) {
            throw new InvalidArgumentException('Unkown type ' . $options['type']);
        }

        if ($file && file_exists($file)) {
            $this->load();
        }
    }
   
    /**
     * Checks if a key exists
     *
     * @param string $key
     * @return boolean
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Gets the count of items in the store
     *
     * @return integer
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Gets a key from the value-store
     *
     * @param string $key
     * @return mixed
     */
    public function &get(string $key)
    {
        $value = null;
        
        if (isset($this->data[$key])) {
            $value = &$this->data[$key];
        }

        return $value;
    }

    /**
     * Sets a key-value in the value-store
     *
     * @param string|array $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value = null): void
    {
        $data = is_array($key) ? $key : [$key => $value];

        $this->validate($data);

        foreach ($data as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Validates data in the array that it is either null, integer, float, string or boolean.
     *
     * @param array $data
     * @throws \Origin\ValueStore\Exception\ValueStoreException
     * @return void
     */
    private function validate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->validate($value);
                continue;
            }
   
            if (! is_null($value) && ! is_scalar($value)) {
                throw new ValueStoreException('Non scalar value passed for key ' . $key);
            }
        }
    }

    /**
     * Saves to disk
     *
     * @return boolean
     */
    public function save(): bool
    {
        if ($this->file) {
            if (file_put_contents($this->file, $this->serialize(), LOCK_EX) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Serializes this object
     *
     * @return string
     */
    protected function serialize(): string
    {
        switch ($this->type) {
            case 'json':
            default:
                return $this->toJson(['pretty' => true, 'escape' => $this->escape]);
            break;
            case 'php':
                return $this->toPhp();
            break;
            case 'xml':
                return $this->toXml(['pretty' => true]);
            break;
            case 'yml':
                return $this->toYaml();
            break;
        }
    }

    /**
     * Loads the data
     *
     * @return void
     */
    protected function load(): void
    {
        if ($this->type === 'php') {
            $data = include $this->file;
            
            if (! is_array($data)) {
                throw new ValueStoreException('PHP value-store does not return an array');
            }
        } else {
            $data = $this->deserialize(file_get_contents($this->file));
        }
        
        $this->data = $data;
    }

    /**
     * Deserializes this object
     *
     * @param string $contents
     * @return array|null
     */
    protected function deserialize(string $contents): ?array
    {
        if (empty($contents)) {
            return [];
        }

        switch ($this->type) {
            case 'json':
            default:
                return json_decode($contents, true);
            break;
            case 'xml':
                return Xml::toArray($contents)[$this->root] ?? [];
            break;
            case 'yml':
                return Yaml::toArray($contents);
            break;
        }
    }

    /**
     * Converts this key-value store into JSON
     *
     * @param array $options Supported options are
     *   - pretty: default:false for json pretty print
     *   - escape: default:false. Escape forward slashes e.g. url
     * @return string
     */
    public function toJson(array $options = []): string
    {
        $options += ['pretty' => false,'escape' => false];

        $flags =
            ($options['pretty'] ? JSON_PRETTY_PRINT : 0) |
            ($options['escape'] === false ? JSON_UNESCAPED_SLASHES: 0);
            
        return json_encode($this->data, $flags);
    }

    /**
     * Converts to YAML
     *
     * @return string
     */
    public function toYaml(): string
    {
        return Yaml::fromArray($this->data);
    }

    /**
     * Converts to XML
     *
     * @param array $options The following keys are supported
     *   - pretty: default:false for pretty print
     *  - root: XML root name
     * @return string
     */
    public function toXml(array $options = []): string
    {
        $options += ['pretty' => false,'root' => $this->root];

        return Xml::fromArray([$options['root'] => $this->data], $options);
    }

    /**
     * Coverts to a PHP string
     *
     * @return string
     */
    public function toPhp(): string
    {
        return '<?php' . PHP_EOL . 'return ' . var_export($this->data, true) . ';';
    }

    /**
     * Converts this to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Increments a key value
     *
     * @param string $key
     * @param integer $amount
     * @return int
     */
    public function increment(string $key, int $amount = 1): int
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = 0;
        }

        $this->validateInteger($this->data[$key]);
      
        $value = (int) $this->data[$key];
        $value += $amount;

        return $this->data[$key] = $value;
    }

    /**
     * Decreases a key value
     *
     * @param string $key
     * @param integer $amount
     * @return int
     */
    public function decrement(string $key, int $amount = 1): int
    {
        if (! isset($this->data[$key])) {
            $this->data[$key] = 0;
        }

        $this->validateInteger($this->data[$key]);

        $value = (int) $this->data[$key];
        $value -= $amount;

        return $this->data[$key] = $value;
    }

    /**
     * Validates integer values for increment and decrement
     *
    * @throws \Origin\ValueStore\Exception\ValueStoreException
     * @return void
     */
    private function validateInteger($value): void
    {
        if (! is_int($value) && ! ctype_digit($value)) {
            throw new ValueStoreException('Value is not an integer');
        }
    }

    /**
     * Deletes a key from the value-store
     *
     * @param string $key
     * @return boolean
     */
    public function unset(string $key): bool
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);

            return true;
        }

        return false;
    }

    /**
     * Clears all data in the value-store
     *
     * @return void
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Magic method for setting data on inaccessible properties.
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set(string $property, $value)
    {
        $this->set($property, $value);
    }

    /**
     * Magic method to get data from inaccessible properties.
     *
     * @param string $property
     * @return mixed
     */
    public function &__get(string $property)
    {
        return $this->get($property);
    }

    /**
     * Magic method is triggered by calling isset() or empty() on inaccessible properties.
     *
     * @param string $property
     * @return boolean
     */
    public function __isset(string $property)
    {
        return $this->has($property);
    }

    /**
     * Magic method is triggered by unset on inaccessible properties.
     *
     * @param string $property
     * @return boolean
     */
    public function __unset(string $property)
    {
        $this->unset($property);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Automatically detect the type based upon the extension
     *
     * @param string $file
     * @return string
     */
    private function detectType(string $file): string
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $type = 'json';
        if ($extension) {
            $key = array_search(strtolower($extension), self::TYPES);
            if ($key !== false) {
                $type = self::TYPES[$key];
            }
        }

        return $type;
    }

    /**
     * Magic method called by var_dump
     */
    public function __debugInfo()
    {
        return $this->data;
    }

    /**
     * JsonSerializable Interface for json_encode($store). Returns the properties that will be
     * serialized as JSON
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data;
    }

    /**
     * ArrayAcces Interface for isset($store);
     *
     * @param mixed $offset
     * @return bool result
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * ArrayAccess Interface for $store[$offset];
     *
     * @param mixed $offset
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * ArrayAccess Interface for $store[$offset] = $value;
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * ArrayAccess Interface for unset($store[$offset]);
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->unset($offset);
    }

    /**
     * IteratorAggregate Interface
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }
}
