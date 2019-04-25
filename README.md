
The Kigkonsult\Http\Message package provide [PSR] server-side
    
> _Message_ class

> _Stream_ class

> _Response_ class


###### Message class
* implements [Psr\Http\Message\MessageInterface]
* constructor args:  body, headers, protocolVersion

###### Stream class
* implements [Psr\Http\Message\StreamInterface]
* constructor args: content, streamWrapper/resource, mode
* with a (static) factory methods
  *  `factoryFromString( content [, streamWrapper [, mode ]] )`
  *  `factoryFromResource( resource )`

###### Response class
* extends Message
* implements
  *  [Psr\Http\Message\ResponseInterface]
  *  [Fig\Http\Message\StatusCodeInterface]
* constructor args:  body, status, headers

The Response class has an added property: 
* `rawBody`
  *  response body (type mixed) before serialization/encoding

in parallel with 
* `(Psr\Http\Message\)ServerRequest::parsedBody`
* `(Psr\Http\Message\)ServerRequest::body`
 
with corresponding methods:
* `getRawBody()`
  *  Return rawBody
* `isRawBodyEmpty()`
  *  Return bool true on empty rawBody
* `withRawBody()`
  *  Return new instance with rawBody

and two rawBody/body methods
* `getResponseBody()` 
  *  Return rawBody if not empty else body
* `isBodyEmpty()`
  *  Return bool true if (serialized/encoded) body is empty
 
Two statusCode methods are added:

* `assertStatusCode()` 
  *  (static) validates statusCode, throws InvalidArgumentException on error
* `isBodyLessResponse()` 
  *  Return bool true on statusCode 1xx, 204 or 304

 
###### Installation
 
[Composer], from the Command Line:
 
```
composer require kigkonsult/http-master:dev-master
```
 
Composer, in your `composer.json`:
 
``` json
{
    "require": {
        "kigkonsult/http-master": "dev-master"
    }
}
```
 
Composer, acquire access
``` php
<?php
use Kigkonsult\Http\Message\Stream;
use Kigkonsult\Http\Message\Message;
use Kigkonsult\Http\Message\Response;
...
include 'vendor/autoload.php';
```
 
 
Otherwise , download and acquire..
 
``` php
<?php
use Kigkonsult\Http\Message\Stream;
use Kigkonsult\Http\Message\Message;
use Kigkonsult\Http\Message\Response;
...
include 'pathToSource/http-master/autoload.php';
```

###### License

This project is licensed under the LGPLv3 License
 
 
[Psr]:https://github.com/php-fig/http-message
[Psr\Http\Message\MessageInterface]:https://github.com/php-fig/http-message
[Psr\Http\Message\StreamInterface]:https://github.com/php-fig/http-message
[Psr\Http\Message\ResponseInterface]:https://github.com/php-fig/http-message
[Fig\Http\Message\StatusCodeInterface]:https://github.com/php-fig/http-message-util
[Composer]:https://getcomposer.org/