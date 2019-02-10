## Web API client for [_EVE-Online_](https://www.eveonline.com) - [_ESI_ API](https://esi.evetech.net)
This Web API client library is used by [_Pathfinder_](https://github.com/exodus4d/pathfinder) and handles all _ESI_ API requests.<br />
Additional APIs can easily be added and can be used side by side with their own configuration. Included clients:

- _CCP ESI_ API client: [ESI.php](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Client/ESI.php) 
- _CCP SSO_ API client: [SSO.php](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Client/SSO.php) 
- _GitHub_ basic API client: [Github.php](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Client/Github.php) 
  
This Web client is build on [_Guzzle_](http://guzzlephp.org) and makes much use of the build in 
[_Middleware_](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware) concept in _Guzzle_.

### Installation:
Use [_Composer_](https://getcomposer.org/) for installation. In `composer.json` `require` section add:
```json
{
  "require": {
    "php-64bit": ">=7.1",
    "exodus4d/pathfinder_esi": "dev-master#v1.2.5"
  }
}
```
> **_Pathfinder_:** This web API client lib is automatically installed through [_Composer_](https://getcomposer.org/) along with all other required dependencies for the _Pathfinder_ project. (→ see [composer.json](https://github.com/exodus4d/pathfinder/blob/master/composer.json)).
>
> A newer version of _Pathfinder_ **may** require a newer version of this repository as well. So running `composer install` **after** a _Pathfinder_ update will upgrade/install a newer _ESI_ client.
Check  _Pathfinder_ [release](https://github.com/exodus4d/pathfinder/releases) notes for further information.

### Use Client:
#### 1. Init client:

```php
// New web client instance for GitHub API [→ Github() implements ApiInterface()]
$client = new \Exodus4D\ESI\Client\Github('https://api.github.com');

// configure client [→ check ApiInterface() for methods]
$client->setTimeout(3);                     // Timeout of the request (seconds)
$client->setUserAgent('My Example App');    // User-Agent Header (string)
$client->setDecodeContent('gzip, deflate'); // Accept-Encoding Header
$client->setDebugLevel(3);                  // Debug level [0-3]
$client->setNewLog(function() : \Closure {  // Callback for new LogInterface
   return function(string $action, string $level = 'warning') : logging\LogInterface {
       $log = new logging\ApiLog($action, $level);
       $log->addHandler('stream', 'json', './logs/requests.log');
       return $log;
   };
});

// Loggable $requests (e.g. HTTP 5xx resp.) will not get logged if return false;
$client->setIsLoggable(function() : \Closure {
    return function(RequestInterface $request) use ($f3) : bool {
        return true;
    };
});

$client->setLogStats(true);                 // Add some cURL status information (e.g. transferTime) to logged responses

$client->setLogCache(true);                 // Add (local) cache info (e.g. response data cached) to logged requests
// $client->setLogAllStatus(true);          // Log all requests regardless of response HTTP status code
$client->setLogFile('requests');            // Log file name for request/response errors
$client->setRetryLogFile('retry_requests'); // Log file for requests errors due to max request retry exceeds

$client->setCacheDebug(true);               // Add debug HTTP Header with local cache status information (HIT/MISS)
$client->setCachePool(function() : \Closure {
    return function() : ?CacheItemPoolInterface {
        $client = new \Redis();             // Cache backend used accross the web client
        $client->connect('localhost', 6379);
          
        // → more PSR-6 compatible adapters at www.php-cache.com (e.g. Filesystem, Array,..)
        $poolRedis = new RedisCachePool($client);
        $cachePool = new NamespacedCachePool($poolRedis, 'myCachePoolName');
        return $cachePool;                  // This can be any PSR-6 compatible instance of CacheItemPoolInterface()
    };
});
```

#### 2. Send requests
```php
// get all releases from GitHub for a repo
$releases = $client->getProjectReleases('exodus4d/pathfinder');
// .. more requests here ...
```

## Concept
### _Guzzle_ [_Middlewares_](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#middleware) :
_Middlewares_ classes are _small_ functions that _hook_ into the "request → response" chain in _Guzzle_.
- A _Middleware_ can _manipulate_ the `request` and `response` objects
- Each _Middleware_ is dedicated to handles its own task. 
- There are _Middlewares_ for "logging", "caching",... pre-configured. 
- Each _Middleware_ has its own set of config options that can be set through the `$client->`.
- All configured _Middlewares_ are pushed into a [_HandlerStack()_](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#handlerstack) that gets _resolved_ for **each** request.
- The **order** in the `HandlerStack()` is essential!

### _Guzzle_ [_HandlerStack_](http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#handlerstack) :
This flowchart shows all _Middlewares_ used by [ESI.php](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Client/ESI.php) API client. 
Each request to _ESI_ API invokes all _Middlewares_ in the following **order**:
##### Before request
[GuzzleJsonMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleJsonMiddleware.php) → 
[GuzzleLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleLogMiddleware.php) → 
[GuzzleCacheMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCacheMiddleware.php) → 
[GuzzleCcpLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpLogMiddleware.php) → 
[GuzzleRetryMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleRetryMiddleware.php) → 
[GuzzleCcpErrorLimitMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpErrorLimitMiddleware.php)
##### After response (→ reverse order!)
[GuzzleCcpErrorLimitMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpErrorLimitMiddleware.php) → 
[GuzzleRetryMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleRetryMiddleware.php) → 
[GuzzleCcpLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpLogMiddleware.php) → 
[GuzzleCacheMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCacheMiddleware.php) → 
[GuzzleLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleLogMiddleware.php) →
[GuzzleJsonMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleJsonMiddleware.php)

### Default _Middlewares_:
#### JSON
Requests with expected _JSON_ encoded `response` data have [GuzzleJsonMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleJsonMiddleware.php)
in _HandlerStack_. <br />
This adds `Accept: application/json` Header to `request` and `response` body gets _wrapped_ into [JsonStream](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Stream/JsonStream.php).

`$client->setAcceptType('json');`

#### Caching
A client instance _should_ be set up with a [_PSR-6_](https://www.php-fig.org/psr/psr-6) compatible cache pool where _persistent_ data can be stored.
Valid `response` data can be cached by its `Cache-Expire` HTTP Header. 
[GuzzleCacheMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCacheMiddleware.php) also handle `Etag` Headers.
Other _Middlewares_ can also access the cache pool for their needs. 
E.g. [GuzzleLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleLogMiddleware.php) can _throttle_ error logging by using the cache pool for error counts,..

→ See: `$client->setCachePool();`
> **Hint:** Check out [www.php-cache.com](http://www.php-cache.com) for _PSR-6_ compatible cache pools.

#### Logging
Errors (or other _events_) during (~before) a request can be logged (e.g. connect errors, or 4xx/5xx `responses`).<br />
The _primary_ _Middleware_ for logging is [GuzzleLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleLogMiddleware.php)<br />
Other _Middlewares_ also have access to the _global_ new log callback and implement their own logs.

`$client->setNewLog();`

#### Retry
Requests result in an _expected_ error (timeouts, _cURL_ connect errors,.. ) will be retried [default: 2 times → configurable!]. 
Check out [GuzzleRetryMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleRetryMiddleware.php) for more information.

### _CCP ESI_ exclusive _Middlewares_:
Each web client has its own stack of _Middlewares_. These _Middlewares_ are exclusive for `requests` to _CCP´s ESI_ API:

#### [GuzzleCcpLogMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpLogMiddleware.php)
Requests to endpoints that return a `warning` HTTP Header for `deprecated` /or `legacy` marked endpoints get logged into separate log files.

#### [GuzzleCcpErrorLimitMiddleware](https://github.com/exodus4d/pathfinder_esi/blob/master/app/Lib/Middleware/GuzzleCcpErrorLimitMiddleware.php)
Failed _ESI_ requests (4xx/5xx status code) implement the concept of "Error Rate Limiting" (→ blog: [ESI error rate limiting](https://developers.eveonline.com/blog/article/esi-error-limits-go-live)).
In case a request failed multiple times in a period, this _Middleware_ keeps track of logging this **and** _pre-block_ requests (e.g. for a user) an endpoint before _CCP_ actual does.

### Content Encoding
The default configuration for "[decode-content](http://docs.guzzlephp.org/en/stable/request-options.html#decode-content)" is `true` → decode "_gzip_" or "_deflate_" responses.<br />
Most APIs will only send compressed response data if `Accept-Encoding` HTTP Header found in request. A `string` value will add this Header and response data gets decoded.

`$client->setDecodeContent('gzip, deflate');`

## Bug report
Issues can be tracked here: https://github.com/exodus4d/pathfinder/issues

## Development
If you are a developer you might have **both** repositories ([exodus4d/pathfinder](https://github.com/exodus4d/pathfinder), [exodus4d/pathfinder_esi](https://github.com/exodus4d/pathfinder_esi) ) checked out locally.

In this case you probably want to _test_ changes in your **local** [exodus4d/pathfinder_esi](https://github.com/exodus4d/pathfinder_esi) repo using your **local** [exodus4d/pathfinder](https://github.com/exodus4d/pathfinder) installation.

1. Clone/Checkout **both** repositories local next to each other
2. Make your changes in your pathfinder_esi repo and **commit** changes (no push!)
3. Switch to your pathfinder repo
4. Run _Composer_ with [`composer-dev.json`](https://github.com/exodus4d/pathfinder/blob/master/composer-dev.json), which installs pathfinder_esi from your **local** repository.
    - Unix: `$set COMPOSER=composer-dev.json && composer update`
    - Windows (PowerShell): `$env:COMPOSER="composer-dev.json"; composer update --no-suggest`

