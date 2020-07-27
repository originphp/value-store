# ValueStore

![license](https://img.shields.io/badge/license-MIT-brightGreen.svg)
[![build](https://travis-ci.org/originphp/value-store.svg?branch=master)](https://travis-ci.org/originphp/value-store)
[![coverage](https://coveralls.io/repos/github/originphp/value-store/badge.svg?branch=master)](https://coveralls.io/github/originphp/value-store?branch=master)

Provides a consistent interface for working various types of key-value stores, including JSON, XML, Yaml and PHP files.

## Installation

To install this package

```linux
$ composer require originphp/value-store
```

## Usage

By default datastores are stored in `json` unless you provide a different file extension, such as `xml`,`yml` or `php`. The type is auto-detected from the file extension, but can be overridden in the constructor.

### Dependencies

To use `Xml` you will need to install the following composer package

```bash
$ composer require originphp/xml
```

To use `Yaml` you will need to install the following composer package

```bash
$ composer require originphp/yaml
```

### Data

You work with this like an object or array, and if the file exists it will load the existing data. To save data call the `save` method.

```php
use Origin\ValueStore\ValueStore;

$settings = new ValueStore(storage_path('settings.json'));

$settings->email = 'demo@example.com'
$settings->incomingServer = [
    'host' => 'mail.example.com',
    'port' => 993,
    'encryption' => 'ssl'
];
$settings->active = true;

$settings->save();
```

You can also use `isset`, `unset` and `count`

```php
unset($settings->key);
$hasKey = isset($settings->key);
$keys = count($settings);
```

You can iterate through the settings

```php
foreach($settings as $key => $value){
    ...
}
```

To increment or decrement values in the store

```php
$settings->increment('count');
$settings->decrement('count');
```

You can also pass a second argument with the amount you want to increase or decrease by.

```php
$settings->increment('count', 4);
$settings->decrement('count', 3);
```

You can also set/get/check values using functions, this can be handy when working with variables.

```php
$settings->set('foo','bar');
$settings->set(['foo'=>'bar','key'=>'value']); // Set multiple values.

$foo = $settings->get('foo');

$keyExists = $settings->has('foo');
$setting->unset('foo');
$count = $settings->count();
```

You can also access as an array

```php
$value = $settings['foo'];
$settings['foo'] = 'bar'
unset($settings['foo']);
$has = isset($settings['foo']);
```

To clear all items in the store (remember to call save if needed).

```php
$settings->clear(); // clears all values in value-store
```

You can convert your value-store to any type on the fly

```php
$settings->toArray();
$settings->toJson();
$settings->toPhp();
$settings->toXml();
$settings->toYaml();
```
