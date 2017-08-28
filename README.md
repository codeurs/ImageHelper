# ImageHelper
A wrapper around the popular Intervention Image library for common image manipulations extended with caching.

## Getting started
- composer require codeurs/imagehelper

## Code Examples
```php
use Codeurs\ImageHelper;

// Set the cache folder location
ImageHelper::setCacheFolder('cache/images');

// Resize an image and return the cached file location
$src = ImageHelper::fromPath('assets/image.png')
  ->resize(320, 240)
  ->src();
```
