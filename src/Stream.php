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

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;
use RuntimeException;

use function fclose;
use function fopen;
use function fread;
use function fseek;
use function ftell;
use function fwrite;
use function is_resource;
use function is_string;
use function stream_get_contents;
use function stream_get_meta_data;
use function strlen;

/**
 * Describes a data stream.
 *
 * Typically, an instance will wrap a PHP stream; this interface provides
 * a wrapper around the most common operations, including serialization of
 * the entire stream to a string.
 */
class Stream implements StreamInterface
{
    /**
     * @var string
     * @access private
     */
    private static $streamWrapper = 'php://memory';

    /**
     * @var string
     * @access private
     */
    private static $mode = 'wb+';

    /**
     * @var resource
     * @access private
     */
    private $stream;

    /**
     * @param null|string           $content
     * @param null|string|resource  $streamWrapper
     * @param null|string           $mode
     * @throws InvalidArgumentException on any invalid element.
     * @throws RuntimeException on write error
     */
    public function __construct( $content = null, $streamWrapper = null, $mode = null ) {
        static $FMTERR4 = 'Invalid body, resource or streamWrapper';
        switch( true ) {
            case ( is_resource( $streamWrapper )) :
                $this->stream = $streamWrapper;
                break;
            case ( empty( $content )) :
                // fall through
            case ( is_string( $content )) :
                $this->stream = self::createStream( $streamWrapper, $mode );
                if( ! empty( $content )) {
                    $this->write( $content );
                }
            break;
            default :
                throw new InvalidArgumentException( $FMTERR4 );
                break;
        }
    }

    /**
     * Return a new stream, from a string (opt with wrapper+mode) or resource
     *
     * @param null|string           $content
     * @param null|string|resource  $streamWrapper
     * @param null|string           $mode
     * @return Stream
     * @throws InvalidArgumentException on stream create error
     * @throws RuntimeException on stream write error
     * @static
     */
    public static function factory( $content = null, $streamWrapper = null, $mode = null ) {
        return new Stream( $content, $streamWrapper, $mode );
    }

    /**
     * Return a new stream, from a string (opt with wrapper+mode)
     *
     * @param null|string   $content
     * @param null|string   $streamWrapper
     * @param null|string   $mode
     * @return Stream
     * @throws InvalidArgumentException on stream create error
     * @throws RuntimeException on stream write error
     * @static
     */
    public static function factoryFromString( $content = null, $streamWrapper = null, $mode = null ) {
        return new Stream( $content, $streamWrapper, $mode );
    }

    /**
     * Return a new stream, from resource
     *
     * @param resource $resource
     * @return Stream
     * @static
     */
    public static function factoryFromResource( $resource ) {
        return new Stream( null, $resource );
    }

    /**
     * Create stream
     *
     * @param null|string  $streamWrapper
     * @param null|string  $mode
     * @return resource
     * @throws InvalidArgumentException on stream error
     * @access private
     * @static
     */
    private static function createStream( $streamWrapper, $mode ) {
        static $FMTERR1 = 'Can not open streamWrapper';
        static $FMTERR2 = 'can not create resource from streamWrapper';
        static $FMTERR3 = 'Resource type is not stream';
        static $STREAM  = 'stream';
        static $errorHandler = [ __CLASS__, 'PhpErrors2Exception' ];
        if( empty( $streamWrapper ) ) {
            $streamWrapper = self::$streamWrapper;
        }
        if( empty( $mode ) ) {
            $mode = self::$mode;
        }
        set_error_handler( $errorHandler );
        try {
            $stream = fopen( $streamWrapper, $mode );
        }
        catch( RuntimeException $e ) {
            throw $e;
        }
        finally {
            restore_error_handler();
        }
        switch( true ) {
            case ( false === $stream ) :
                throw new InvalidArgumentException( $FMTERR1 );
                break;
            case( ! is_resource( $stream )) :
                throw new InvalidArgumentException( $FMTERR2 );
                break;
            case( $STREAM !== get_resource_type( $stream )) :
                throw new InvalidArgumentException( $FMTERR3 );
                break;
        }
        return $stream;
    }

    /**
     * Return bool true if stream is empty
     *
     * @param StreamInterface $stream
     * @return bool
     * @static
     */
    public static function isStreamEmpty( StreamInterface $stream ) {
        return empty( $stream->getSize());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() {
        static $EMPTY = '';
        if( ! $this->isReadable()) {
            return $EMPTY;
        }
        $this->rewind();
        try {
            $data = $this->getContents();
        }
        catch( RuntimeException $e ) {
            return $EMPTY;
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function close() {
        fclose( $this->stream );
    }

    /**
     * {@inheritdoc}
     */
    public function detach() {
        $this->close();
        return null;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize() {
        return strlen( $this->__toString());
    }

    /**
     * {@inheritdoc}
     */
    public function tell() {
        static $FMTERR = 'Resource ftell error';
        $pos = ftell( $this->stream );
        if( false === $pos ) {
            throw new RuntimeException( $FMTERR );
        }
        return $pos;
    }

    /**
     * {@inheritdoc}
     */
    public function eof() {
        static $EOF = 'eof';
        return $this->getMetadata( $EOF );
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable() {
        static $SEEKABLE = 'seekable';
        return $this->getMetadata( $SEEKABLE );
    }

    /**
     * {@inheritdoc}
     */
    public function seek( $offset, $whence = SEEK_SET ) {
        static $FMTERR1 = 'Resource is not seekable';
        static $FMTERR2 = 'Resource seek error';
        if( ! $this->isSeekable()) {
            throw new RuntimeException( $FMTERR1 );
        }
        if( -1 == fseek( $this->stream, $offset, $whence )) {
            throw new RuntimeException( $FMTERR2 );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind() {
        $this->seek( 0 );
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable() {
        static $MODE   = 'mode';
        static $WRITEC = 'waxc+';
        return ( false !== strpbrk( $this->getMetadata( $MODE ), $WRITEC ));
    }

    /**
     * {@inheritdoc}
     */
    public function write( $string ) {
        static $FMTERR1 = 'Resource is not writable';
        static $FMTERR2 = 'Resource fwrite error';
        if( ! $this->isWritable()) {
            throw new RuntimeException( $FMTERR1 );
        }
        $cnt = fwrite( $this->stream, $string );
        if( false === $cnt ) {
            throw new RuntimeException( $FMTERR2 );
        }
        return $cnt;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable() {
        static $MODE  = 'mode';
        static $READC = 'r+';
        return ( false !== strpbrk( $this->getMetadata( $MODE ), $READC ));
    }

    /**
     * {@inheritdoc}
     */
    public function read( $length ) {
        static $FMTERR1 = 'Resource is not readable';
        static $FMTERR2 = 'Resource fread error';
        if( ! $this->isReadable()) {
            throw new RuntimeException( $FMTERR1 );
        }
        $data = fread( $this->stream, $length );
        if( false === $data ) {
            throw new RuntimeException( $FMTERR2 );
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents() {
        static $FMTERR1 = 'Resource is not readable';
        static $FMTERR2 = 'Resource read content error';
        if( ! $this->isReadable()) {
            throw new RuntimeException( $FMTERR1 );
        }
        $data = stream_get_contents( $this->stream );
        if( false === $data ) {
            throw new RuntimeException( $FMTERR2 );
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata( $key = null ) {
        $metaData = stream_get_meta_data( $this->stream );
        if( empty( $key )) {
            return $metaData;
        }
        return ( isset( $metaData[$key] )) ? $metaData[$key] : null;
    }

    /**
     * Throw PHP error as RuntimeException
     *
     * @param int    $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int    $errLine
     * @throws RuntimeException
     * @access private
     * @static
     */
    private static function PhpErrors2Exception( $errNo, $errStr, $errFile, $errLine ) {
        static $FMT = '(%d) %s in %s(%d)';
        throw new RuntimeException( sprintf( $FMT, $errNo, $errStr, $errFile, $errLine ));
    }
}
