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

use stdClass;
use InvalidArgumentException;
use Origin\ValueStore\Exception\ValueStoreException;

class ValueStoreTest extends \PHPUnit\Framework\TestCase
{
    public function testAsObject()
    {
        $settings = new ValueStore();

        // set / get
        $settings->foo = 'bar';
        $this->assertEquals('bar', $settings->foo);

        // has / unset
        $this->assertTrue(isset($settings->foo));
        unset($settings->foo);
        $this->assertNull($settings->foo);
        unset($settings->foo); // check no error

        // increment
        $settings->count = 0;
        $settings->count++;
        $this->assertEquals(1, $settings->count);
        $settings->count--;
        $this->assertEquals(0, $settings->count);
    }

    public function testAsArray()
    {
        $settings = new ValueStore();
        // set / get
        $settings['foo'] = 'bar';
        $this->assertEquals('bar', $settings['foo']);
       
        // isset/unset
        $this->assertTrue(isset($settings['foo']));
        unset($settings['foo']);
        $this->assertFalse(isset($settings['foo']));

        // increment
        $settings['count'] = 0;
        $settings['count']++;
        $this->assertEquals(1, $settings['count']);
        $settings['count']--;
        $this->assertEquals(0, $settings['count']);
    }

    public function testAsFunctions()
    {
        $settings = new ValueStore();

        // set / get
        $settings->set('foo', 'bar');
        $this->assertEquals('bar', $settings->get('foo'));

        // has / unset
        $this->assertTrue($settings->has('foo'));
        $this->assertTrue($settings->unset('foo'));
        $this->assertFalse($settings->has('foo'));

        // increment
        $this->assertEquals(1, $settings->increment('count'));
        $this->assertEquals(0, $settings->decrement('count'));
    }

    public function testClear()
    {
        $settings = new ValueStore();
        $settings->foo = 'bar';
        $this->assertTrue($settings->has('foo'));
        $settings->clear();
        $this->assertFalse($settings->has('foo'));
    }

    public function testIncrement()
    {
        $settings = new ValueStore();
        $this->assertEquals(1, $settings->increment('count'));
        $this->assertEquals(3, $settings->increment('count', 2));
    }

    public function testDecrement()
    {
        $settings = new ValueStore();
        $this->assertEquals(-1, $settings->decrement('count'));
        $this->assertEquals(-3, $settings->decrement('count', 2));
    }

    public function testJson()
    {
        $file = sys_get_temp_dir() . '/' . uniqid() . '.json';

        $settings = new ValueStore($file);
    
        $settings->name = 'json';
        $settings->description = 'json-test';
        $settings->data = [
            'id' => 'f5324126-4a63-432a-b287-323969cae2e7'
        ];
        $this->assertTrue($settings->save());
        $this->assertFileHash('d181303b1728ca71701959b4222dbfc7', $file);

        $settings = new ValueStore($file);
        $this->assertEquals('json', $settings->name);
        $this->assertEquals('f5324126-4a63-432a-b287-323969cae2e7', $settings->data['id']);

        $settings->clear();
        $this->assertTrue($settings->save()); // test save empty values
    }

    public function testXml()
    {
        $file = sys_get_temp_dir() . '/' . uniqid() . '.xml';

        $settings = new ValueStore($file);
    
        $settings->name = 'xml';
        $settings->description = 'xml demo';
        $settings->data = [
            'id' => 'f5324126-4a63-432a-b287-323969cae2e7'
        ];
      
        $this->assertTrue($settings->save());
        $this->assertFileHash('4684008f9c0a5a253f8197693f8a013c', $file);

        $settings = new ValueStore($file);
        $this->assertEquals('xml', $settings->name);
        $this->assertEquals('f5324126-4a63-432a-b287-323969cae2e7', $settings->data['id']);

        $settings->clear();
        $this->assertTrue($settings->save()); // test save empty values
    }

    public function testPhp()
    {
        $file = sys_get_temp_dir() . '/' . uniqid() . '.php';

        $settings = new ValueStore($file);
    
        $settings->name = 'php';
        $settings->description = 'php demo';
        $settings->data = [
            'id' => 'f5324126-4a63-432a-b287-323969cae2e7'
        ];
        $this->assertTrue($settings->save());
        $this->assertFileHash('9cd7a6fae64850eae345005f6e16893e', $file);

        $settings = new ValueStore($file);
        $this->assertEquals('php', $settings->name);
        $this->assertEquals('f5324126-4a63-432a-b287-323969cae2e7', $settings->data['id']);
      
        $settings->clear();
        $this->assertTrue($settings->save()); // test save empty values
    }

    public function testYaml()
    {
        $file = sys_get_temp_dir() . '/' . uniqid() . '.yml';

        $settings = new ValueStore($file);
    
        $settings->name = 'yaml';
        $settings->description = 'yaml demo';
        $settings->data = [
            'id' => 'f5324126-4a63-432a-b287-323969cae2e7'
        ];
        $this->assertTrue($settings->save());
        $this->assertFileHash('582684eb646187903303e583916c8713', $file);

        $settings = new ValueStore($file);

        $this->assertEquals('yaml', $settings->name);
        $this->assertEquals('f5324126-4a63-432a-b287-323969cae2e7', $settings->data['id']);
      
        $settings->clear();
        $this->assertTrue($settings->save()); // test save empty values
    }

    public function assertFileHash(string $expected, string $file)
    {
        #echo file_get_contents($file);
        $this->assertEquals($expected, hash_file('md5', $file), 'File hash does not match');
    }

    public function testToArray()
    {
        $settings = new ValueStore();
        $settings->name = 'foo';
        $settings->created = date('Y-m-d H:i:s');
        $settings->modifed = date('Y-m-d H:i:s');

        $this->assertIsArray($settings->toArray());
        $this->assertIsArray($settings->__debugInfo());
    }

    public function testEmptyJson()
    {
        $file = sys_get_temp_dir() . '/' . uniqid();

        file_put_contents($file .'.json', '');
        $this->assertEmpty((new ValueStore($file .'.php'))->toArray());
    }

    public function testEmptyXml()
    {
        $file = sys_get_temp_dir() . '/' . uniqid();

        file_put_contents($file .'.xml', '');
        $this->assertEmpty((new ValueStore($file .'.xml'))->toArray());
    }

    public function testEmptyYaml()
    {
        $file = sys_get_temp_dir() . '/' . uniqid();

        file_put_contents($file .'.yml', '');
        $this->assertEmpty((new ValueStore($file .'.yml'))->toArray());
    }

    public function testEmptyPHP()
    {
        $file = sys_get_temp_dir() . '/' . uniqid();

        file_put_contents($file .'.php', '');
        $this->expectException(ValueStoreException::class);
        (new ValueStore($file .'.php'))->toArray();
    }

    public function testCountable()
    {
        $settings = new ValueStore();
        $settings->name = 'foo';
        $settings->created = date('Y-m-d H:i:s');
        $settings->modifed = date('Y-m-d H:i:s');
        $this->assertEquals(3, count($settings));
        $this->assertEquals(3, $settings->count());
    }

    public function testIterator()
    {
        $settings = new ValueStore(sys_get_temp_dir() . '/' . uniqid() . '.json');
        $settings->one = 1;
        $settings->two = 2;
        $settings->three = 3;
        
        $found = [];
        foreach ($settings as $key => $value) {
            $found[$key] = $value;
        }
        $this->assertEquals(['one' => 1,'two' => 2,'three' => 3], $found);
    }

    public function testInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $settings = new ValueStore('settings', ['type' => 'apcu']);
    }

    public function testSaveNoFile()
    {
        $this->assertFalse((new ValueStore())->save());
    }

    public function testToString()
    {
        $settings = new ValueStore();
        $settings->foo = 'bar';
        $expected = '{"foo":"bar"}';
        $this->assertEquals($expected, (string) $settings);
    }

    public function testJsonSerialiable()
    {
        $settings = new ValueStore();
        $settings->foo = 'bar';
        $expected = '{"foo":"bar"}';
        $this->assertEquals($expected, json_encode($settings));
    }

    public function testIncreaseException()
    {
        $this->expectException(ValueStoreException::class);
        $store = new ValueStore();
        $store->foo = 'bar';
        $store->increment('foo');
    }
    public function testDecreaseException()
    {
        $this->expectException(ValueStoreException::class);
        $store = new ValueStore();
        $store->foo = 'bar';
        $store->decrement('foo');
    }

    public function testStoreNonScalar()
    {
        $this->expectException(ValueStoreException::class);
        $store = new ValueStore();
        $store->foo = new stdClass();
    }

    public function testStoreNonScalarDeep()
    {
        $this->expectException(ValueStoreException::class);
        $store = new ValueStore();
        $store->foo = [
            'bar' => new stdClass()
        ];
    }
}
