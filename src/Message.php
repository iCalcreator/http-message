<?php
/**
 * http-message, a Psr\Http\Message implementation
 *
 * Copyright (c) 2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * Link      https://kigkonsult.se
 * Package   http-message
 * Version   1.0
 * License   Subject matter of licence is the software http-message.
 *           The above copyright, link, package and version notices and
 *           this licence notice shall be included in all copies or
 *           substantial portions of the http-message.
 *
 *           http-message is free software: you can redistribute it and/or modify
 *           it under the terms of the GNU Lesser General Public License as published
 *           by the Free Software Foundation, either version 3 of the License,
 *           or (at your option) any later version.
 *
 *           http-message is distributed in the hope that it will be useful,
 *           but WITHOUT ANY WARRANTY; without even the implied warranty of
 *           MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *           GNU Lesser General Public License for more details.
 *
 *           You should have received a copy of the GNU Lesser General Public License
 *           along with http-message. If not, see <https://www.gnu.org/licenses/>.
 *
 * This file is part of http-message.
 */

namespace Kigkonsult\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

use function array_keys;
use function explode;
use function implode;
use function sprintf;
use function strtolower;
use function ucfirst;

/**
 * HTTP messages consist of requests from a client to a server and responses
 * from a server to a client. This interface defines the methods common to
 * each.
 *
 * Messages are considered immutable; all methods that might change state MUST
 * be implemented such that they retain the internal state of the current
 * message and return an instance that contains the changed state.
 *
 * @link http://www.ietf.org/rfc/rfc7230.txt
 * @link http://www.ietf.org/rfc/rfc7231.txt
 */
class Message implements MessageInterface
{

    /**
     * @var string  -  default HTTP protocol version
     * @access protected
     */
    protected static $defaultPV = '1.1';

    /**
     * @var string  -  HTTP protocol version
     * @access protected
     */
    protected $protocolVersion = null;

    /**
     * @var array  -  associative array of the message's header
     * @access protected
     */
    protected $headers = [];

    /**
     * @var array  -  associative array of the message's headerNames
     * @access protected
     */
    protected $headerNames = [];

    /**
     * @var StreamInterface  -  message body
     * @access protected
     */
    protected $body = null;

    /**
     * @param null|string|StreamInterface $body
     * @param null|string[]        $headers
     * @param null|string          $protocolVersion
     * @throws InvalidArgumentException on any invalid element.
     */
    public function __construct(
        $body            = null,
        $headers         = null,
        $protocolVersion = null
    ) {
        $this->setBody( $body );
        foreach( (array) $headers as $name => $value ) {
            $this->setHeader( $name, $value );
        }
        $this->protocolVersion = ( empty( $protocolVersion )) ? self::$defaultPV : $protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion() {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion( $version ) {
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders() {
        $headers = [];
        foreach( $this->headerNames as $name => $name2 ) {
            $headers[$name] = $this->headers[$name2];
        }
        return $headers;
    }

    /**
     * Return marshalled header name
     *
     * @param $name
     * @return string
     * @access protected
     * @static
     */
    protected static function marschallHeader( $name ) {
        $GLUE = '-';
        $headerParts = explode( $GLUE, $name );
        foreach( $headerParts as & $part ) {
            $part = ucfirst( strtolower( $part ));
        }
        return implode( $GLUE, $headerParts );
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader( $name ) {
        $name = self::marschallHeader( $name );
        return isset( $this->headers[$name] );
    }

    /**
     * Set headerName and headerValue
     *
     * @param string          $name
     * @param string|string[] $value
     */
    protected function setHeader( $name, $value ) {
        $name2 = self::marschallHeader( $name );
        if( ! isset( $this->headerNames[$name] )) {
            $this->headerNames[$name] = $name2;
        }
        if( ! isset( $this->headers[$name2] )) {
            $this->headers[$name2] = [];
        }
        foreach( (array) $value as $value ) {
            $this->headers[$name2][] = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader( $name ) {
        $name = self::marschallHeader( $name );
        return isset( $this->headers[$name] )
            ? $this->headers[$name]
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine( $name ) {
        $GLUE  = ',';
        $EMPTY = '';
        $name  = self::marschallHeader( $name );
        return ( isset( $this->headers[$name] ))
            ? implode( $GLUE, $this->headers[$name] )
            : $EMPTY;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader( $name, $value ) {
        $FMTERR = 'Header %s do not exist';
        $name2  = self::marschallHeader( $name );
        if( ! isset( $this->headers[$name2] )) {
            throw new InvalidArgumentException( sprintf( $FMTERR, $name ));
        }
        $new = clone $this;
        self::removeHeader( $new, $name );
        $new->setHeader( $name, $value );
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withAddedHeader( $name, $value ) {
        $new = clone $this;
        $new->setHeader( $name, $value );
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader( $name ) {
        $new = clone $this;
        self::removeHeader( $new, $name );
        return $new;
    }

    /**
     * Remove header name
     *
     * @param Message $message
     * @param string  $name
     * @access protected
     * @static
     */
    protected static function removeHeader( Message $message, $name ) {
        $name2 = self::marschallHeader( $name );
        if( isset( $message->headers[$name2] )) {
            unset( $message->headers[$name2] );
        }
        foreach( array_keys( $message->headerNames, $name2 ) as $name3 ) {
            unset( $message->headerNames[$name3] );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Set body
     *
     * @param string|StreamInterface $body
     * @access protected
     * @throws InvalidArgumentException on invalid body.
     */
    protected function setBody( $body ) {
        $FMTERR  = 'Invalid body, not string, resource or StreamInterface';
        switch( true ) {
            case ( $body instanceof StreamInterface ) :
                $this->body = $body;
                break;
            case ( is_resource( $body )) :
                $this->body = Stream::factoryFromResource( $body );
                break;
            case ( empty( $body ) || is_string( $body )) :
                $this->body = Stream::factoryFromString( $body );
                break;
            default :
                throw new InvalidArgumentException( $FMTERR );
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function withbody( StreamInterface $body ) {
        $new       = clone $this;
        $new->body = $body;
        return $new;
    }

}
