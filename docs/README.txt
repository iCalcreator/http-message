
Kigkonsult\Http\Message package

provide PSR server-side
    
- Message class

- Stream class

- Response class


MESSAGE class
- implements Psr\Http\Message\MessageInterface
- constructor args:  body, headers, protocolVersion
    body            null|string|StreamInterface
    headers         null|string[]
    protocolVersion null|string

STREAM class
- implements Psr\Http\Message\StreamInterface
- constructor args: content, streamWrapper/resource, mode
    content        null|string
    streamWrapper  null|string|resource  (defaults to 'php://memory')
    mode           null|string           (defaults to 'wb+')

- with a (static) factory methods
--  factoryFromString( [ content [, streamWrapper [, mode ]]] )
      content        null|string
      streamWrapper  null|string   (defaults to 'php://memory')
      mode           null|string   (defaults to 'wb+')
--  factoryFromResource( resource )
    resource  resource

RESPONSE class
- extends Message
- implements
    Psr\Http\Message\ResponseInterface
    Fig\Http\Message\StatusCodeInterface
- constructor args:  body, status, headers
    body     null|string|resource|StreamInterface
    status   null|int     (defaults to STATUS_OK, 200)
    headers  null|array

The Response class has an added property: 
- rawBody
--  response body (type mixed) before serialization/encoding

in parallel with `ServerRequest::parsedBody` and `ServerRequest::body`
 
with corresponding methods:
-  getRawBody()
--   Return mixed rawBody

- isRawBodyEmpty()
--  Return bool true on empty rawBody

- withRawBody()
--  Return new instance with rawBody

and two rawBody/body methods
- getResponseBody()
--  Return mixed
      rawBody if not empty
      body if not empty
      null

- isBodyEmpty()
--  Return bool true if (serialized/encoded) body is empty
 
Two statusCode methods are added:

- assertStatusCode()
--  static, Validates statusCode, throws InvalidArgumentException on error

- isBodyLessResponse()
--  Return bool true on statusCode 1xx, 204 or 304


INSTALL
 
Composer (https://getcomposer.org/), from the Command Line:
 
composer require kigkonsult/http-master:dev-master


Composer, in your `composer.json`:
 
{
    "require": {
        "kigkonsult/http-master": "dev-master"
    }
}

Composer, acquire access
~~~~~~
<?php
use Kigkonsult\Http\Message\Stream;
use Kigkonsult\Http\Message\Message;
use Kigkonsult\Http\Message\Response;
...
include 'vendor/autoload.php';

~~~~~~

Otherwise , download and acquire..
~~~~~~
<?php
use Kigkonsult\Http\Message\Stream;
use Kigkonsult\Http\Message\Message;
use Kigkonsult\Http\Message\Response;
...
include 'pathToSource/http-master/autoload.php';

~~~~~~


Copyright (c) 2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
Link      https://kigkonsult.se
Package   http-master
Version   1.0
License   Subject matter of licence is the software http-message.
          The above copyright, link, package and version notices and
          this licence notice shall be included in all copies or
          substantial portions of the http-message.

          http-message is free software: you can redistribute it and/or modify
          it under the terms of the GNU Lesser General Public License as published
          by the Free Software Foundation, either version 3 of the License,
          or (at your option) any later version.

          http-message is distributed in the hope that it will be useful,
          but WITHOUT ANY WARRANTY; without even the implied warranty of
          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
          GNU Lesser General Public License for more details.

          You should have received a copy of the GNU Lesser General Public License
          along with http-message. If not, see <https://www.gnu.org/licenses/>.
