<?php
/**
 * http-message, a Psr\Http\Message implementation
 *
 * This file is part of http-message.
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult <ical@kigkonsult.se>
 * @copyright 2019-2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software http-message.
 *            The above copyright, link and this licence notice shall be
 *            included in all copies or substantial portions of the http-message.
 *
 *            http-message is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            http-message is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with http-message. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
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
     * @var string
     */
    private static $EMPTY = '';

    /**
     * @var string  -  default HTTP protocol version
     */
    protected static $defaultPV = '1.1';

    /**
     * @var string  -  HTTP protocol version
     */
    protected $protocolVersion = null;

    /**
     * @var array  -  associative array of the message's header
     */
    protected $headers = [];

    /**
     * @var array  -  associative array of the message's headerNames
     */
    protected $headerNames = [];

    /**
     * @var StreamInterface  -  message body
     */
    protected $body = null;

    /**
     * @param null|string|resource|StreamInterface $body
     * @param null|string[]        $headers
     * @param null|string          $protocolVersion
     * @throws InvalidArgumentException on any invalid argument.
     */
    public function __construct(
        $body            = null,
        $headers         = null,
        $protocolVersion = null
    )
    {
        $this->setBody( $body ?? self::$EMPTY );
        foreach( (array) $headers as $name => $value ) {
            $this->setHeader( $name, $value );
        }
        $this->protocolVersion = ( $protocolVersion ?? self::$defaultPV );
    }

    /**
     * {@inheritdoc}
     */
    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function withProtocolVersion( $version ) : self
{
        $new = clone $this;
        $new->protocolVersion = $version;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders() : array
    {
        $headers = [];
        foreach( $this->headerNames as $name => $name2 ) {
            $headers[$name] = $this->headers[$name2];
        }
        return $headers;
    }

    /**
     * Return marshalled header name
     *
     * @param string $name
     * @return string
     */
    protected static function marschallHeader( string $name ) : string
    {
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
    public function hasHeader( $name ) : bool
    {
        $name = self::marschallHeader( $name );
        return isset( $this->headers[$name] );
    }

    /**
     * Set headerName and headerValue
     *
     * @param string          $name
     * @param string|string[] $value
     * @return static
     */
    protected function setHeader( string $name, $value ) : self
    {
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
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader( $name ) : array
    {
        $name = self::marschallHeader( $name );
        return $this->headers[$name] ?? [];
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaderLine( $name ) : string
    {
        static $GLUE  = ',';
        $name  = self::marschallHeader( $name );
        return ( isset( $this->headers[$name] ))
            ? implode( $GLUE, $this->headers[$name] )
            : self::$EMPTY;
    }

    /**
     * {@inheritdoc}
     */
    public function withHeader( $name, $value ) : self
    {
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
    public function withAddedHeader( $name, $value ) : self
    {
        $new = clone $this;
        $new->setHeader( $name, $value );
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutHeader( $name ) : self
    {
        $new = clone $this;
        self::removeHeader( $new, $name );
        return $new;
    }

    /**
     * Remove header name
     *
     * @param Message $message
     * @param string  $name
     */
    protected static function removeHeader( Message $message, string $name )
    {
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
    public function getBody() : StreamInterface
    {
        return $this->body;
    }

    /**
     * Set (StreamInterface) body from StreamInterface|resource|string
     *
     * @param StreamInterface|resource|string $body
     * @return static
     * @throws InvalidArgumentException on invalid body.
     */
    protected function setBody( $body ) : self
    {
        $FMTERR  = 'Invalid body, not string, resource or StreamInterface';
        switch( true ) {
            case ( $body instanceof StreamInterface ) :
                $this->body = $body;
                break;
            case ( is_resource( $body )) :
                $this->body = Stream::factoryFromResource( $body );
                break;
            case ( empty( $body ) || is_string( $body )) :
                $this->body = Stream::factoryFromString( $body ?? self::$EMPTY );
                break;
            default :
                throw new InvalidArgumentException( $FMTERR );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withbody( StreamInterface $body ) : self
    {
        $new       = clone $this;
        $new->body = $body;
        return $new;
    }
}
