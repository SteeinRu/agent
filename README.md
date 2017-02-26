# Module Agent

PHP module that determines the browser, platform, language (...) built on the basis of Mobiledetect.


## Based on possible

```php
$agent = new \SteeinAgent\Agent();
```

```php
$agent->setUserAgent('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2');
$agent->setHttpHeaders($headers);
```
All of the original **Mobile Detect** methods are still available, check out some original examples at https://github.com/serbanghita/Mobile-Detect/wiki/Code-examples


### Is
Check for a certain property in the user agent.

```php
$agent->is('Windows');
$agent->is('Firefox');
$agent->is('iPhone');
$agent->is('OS X');
```

### Magic is-method

```php
$agent->isAndroidOS();
$agent->isNexus();
$agent->isSafari();
```

### Mobile detection

Check for mobile device:

```php
$agent->isMobile();
$agent->isTablet();
```


### Additional functions

## Accept languages

Get the browser's accept languages. Example:

```php
$languages = $agent->languages();
```

### Device name
Get the device name, if mobile. (iPhone, Nexus, AsusTablet, ...)

```php
$device = $agent->device();
```

### Operating system name
Get the operating system. (Ubuntu, Windows, OS X, ...)

```php
$platform = $agent->platform();
```

### Browser/platform version

MobileDetect recently added a **version** method that can get the version number for components. To get the browser or platform version you can use:

```php
$browser = $agent->browser();
$version = $agent->version($browser);

$platform = $agent->platform();
$version = $agent->version($platform);
```
