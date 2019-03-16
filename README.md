# Filesystem (directory, file, cache, ...)

![License](https://img.shields.io/packagist/l/corex/filesystem.svg)
![Build Status](https://travis-ci.org/corex/filesystem.svg?branch=master)
![codecov](https://codecov.io/gh/corex/filesystem/branch/master/graph/badge.svg)


### Cache
Simple cache implementation.

A few examples.
```php
// Generate key based on string + array.
$key = Cache::key('test', ['param1' => 'Something']);

// Set path for cache stores.
Cache::path('/path/cache/stores');

// Set lifetime for cache in seconds.
Cache::lifetime(600);

// Set lifetime for cache in minutes.
Cache::lifetime('60m');

// Set lifetime for cache in hours.
Cache::lifetime('1h');

// Get from cache from 'custom-store'.
$data = Cache::get('test', 'default.value', 'custom-store');

// Put data in cache to 'custom-store'.
Cache::put('test', 'data', 'custom-store');

// Flush cache 'custom-store'.
Cache::flush('custom-store');
```


### Directory
Various directory helpers.

A few examples.
```php
// Test if directory exists.
$exist = Directory::exist('/my/path');

// Check if directory is writeable.
$isWriteable = Directory::isWritable('/my/path');

// Make directory.
Directory::make('/my/path');

// Get entries of a directory.
$entries = Directory::entries('/my/path', '*', true, true, true);
```


### File
Various file helpers (i.e. stub, json, etc.)

A few examples.
```php
// Check if file exists.
$exist = File::exist($filename);

// Get from file.
$content = File::get($filename);

// Load lines.
$lines = File::getLines($filename);

// Save content.
File::put($filename, $content);

// Save lines.
File::putLines($filename, $lines);

// Get stub.
$stub = File::getStub($filename, [
    'firstname' => 'Roger',
    'lastname' => 'Moore'
]);

// Get template.
$template = File::getTemplate($filename, [
    'firstname' => 'Roger',
    'lastname' => 'Moore'
]);

// Get json.
$array = File::getJson($filename);

// Put json.
File::putJson($filename, $array);

// Get temp filename.
$filename = File::getTempFilename();

// Delete file.
File::delete($filename);
```
