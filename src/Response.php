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

use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

use function gettype;
use function is_scalar;
use function sprintf;

/**
 * Implement Psr\Http\Message\ResponseInterface with
 *   new property: rawBody (mixed)
 *   methods
 *     getRawBody()
 *     isRawBodyEmpty()
 *     withRawBody()
 *     isBodyEmpty()
 *
 *     assertStatusCode()
 *     isStatusCodeValid()
 *     getResponseBody()
 *     isBodyLessResponse()
 */
/**
 * HTTP response encapsulation.
 *
 * Responses are considered immutable; all methods that might change state are
 * implemented such that they retain the internal state of the current
 * message and return a new instance that contains the changed state.
 *
 *
 */
class Response extends Message implements ResponseInterface
{
    /**
     * @const string
     */
    const MIN_STATUS_CODE_VALUE = 100;
    const MAX_STATUS_CODE_VALUE = 599;

    /**
     * Map of standard HTTP status code/reason phrases
     *
     * @var array
     */
    private $phrases = [
        // INFORMATIONAL CODES
        self::STATUS_CONTINUE            => 'Continue',                  // 100;
        self::STATUS_SWITCHING_PROTOCOLS => 'Switching Protocols',       // 101
        self::STATUS_PROCESSING          => 'Processing',                // 102
        self::STATUS_EARLY_HINTS         => 'Early Hints',               // 203
        // SUCCESS CODES
        self::STATUS_OK                  => 'OK',                        // 200
        self::STATUS_CREATED             => 'Created',                   // 201
        self::STATUS_ACCEPTED            => 'Accepted',                  // 202
        self::STATUS_NON_AUTHORITATIVE_INFORMATION                       // 203
                                         => 'Non-Authoritative Information',
        self::STATUS_NO_CONTENT          => 'No Content',                // 204
        self::STATUS_RESET_CONTENT       => 'Reset Content',             // 205
        self::STATUS_PARTIAL_CONTENT     => 'Partial Content',           // 206
        self::STATUS_MULTI_STATUS        => 'Multi-Status',              // 207
        self::STATUS_ALREADY_REPORTED    => 'Already Reported',          // 208
        self::STATUS_IM_USED             => 'IM Used',                   // 226
        // REDIRECTION CODES
        self::STATUS_MULTIPLE_CHOICES    => 'Multiple Choices',          // 300
        self::STATUS_MOVED_PERMANENTLY   => 'Moved Permanently',         // 301
        self::STATUS_FOUND               => 'Found',                     // 302
        self::STATUS_SEE_OTHER           => 'See Other',                 // 303
        self::STATUS_NOT_MODIFIED        => 'Not Modified',              // 304
        self::STATUS_USE_PROXY           => 'Use Proxy',                 // 305
        self::STATUS_RESERVED            => 'Switch Proxy',              // 306, Deprecated to '(Unused)'
        self::STATUS_TEMPORARY_REDIRECT  => 'Temporary Redirect',        // 307
        self::STATUS_PERMANENT_REDIRECT  => 'Permanent Redirect',        // 308
        // CLIENT ERROR
        self::STATUS_BAD_REQUEST         => 'Bad Request',               // 400
        self::STATUS_UNAUTHORIZED        => 'Unauthorized',              // 401
        self::STATUS_PAYMENT_REQUIRED    => 'Payment Required',          // 402
        self::STATUS_FORBIDDEN           => 'Forbidden',                 // 403
        self::STATUS_NOT_FOUND           => 'Not Found',                 // 404
        self::STATUS_METHOD_NOT_ALLOWED  => 'Method Not Allowed',        // 405
        self::STATUS_NOT_ACCEPTABLE      => 'Not Acceptable',            // 406
        self::STATUS_PROXY_AUTHENTICATION_REQUIRED                       // 407
                                         => 'Proxy Authentication Required',
        self::STATUS_REQUEST_TIMEOUT     => 'Request Timeout',           // 408
        self::STATUS_CONFLICT            => 'Conflict',                  // 409
        self::STATUS_GONE                => 'Gone',                      // 410
        self::STATUS_LENGTH_REQUIRED     => 'Length Required',           // 411
        self::STATUS_PRECONDITION_FAILED => 'Precondition Failed',       // 412
        self::STATUS_PAYLOAD_TOO_LARGE   => 'Payload Too Large',         // 413
        self::STATUS_URI_TOO_LONG        => 'URI Too Long',              // 414
        self::STATUS_UNSUPPORTED_MEDIA_TYPE                              // 415
                                         => 'Unsupported Media Type',
        self::STATUS_RANGE_NOT_SATISFIABLE                               // 416
                                         => 'Range Not Satisfiable',
        self::STATUS_EXPECTATION_FAILED  => 'Expectation Failed',        // 417
        self::STATUS_IM_A_TEAPOT         => 'I\'m a teapot',             // 418
        self::STATUS_MISDIRECTED_REQUEST => 'Misdirected Request',       // 421
        self::STATUS_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',     // 422
        self::STATUS_LOCKED              => 'Locked',                    // 423
        self::STATUS_FAILED_DEPENDENCY   => 'Failed Dependency',         // 424
        self::STATUS_TOO_EARLY           => 'Too early',                 // 425
        self::STATUS_UPGRADE_REQUIRED    => 'Upgrade Required',          // 426
        self::STATUS_PRECONDITION_REQUIRED
                                         => 'Precondition Required',     // 428
        self::STATUS_TOO_MANY_REQUESTS   => 'Too Many Requests',         // 429
        self::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE                     // 431
                                         => 'Request Header Fields Too Large',
                                                                         // 444
        444                              => 'Connection Closed Without Response',
        self::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS                       // 451
                                         => 'Unavailable For Legal Reasons',
        // SERVER ERROR
        499                              => 'Client Closed Request',     // 499
        self::STATUS_INTERNAL_SERVER_ERROR => 'Internal Server Error',   // 500
        self::STATUS_NOT_IMPLEMENTED     => 'Not Implemented',           // 501
        self::STATUS_BAD_GATEWAY         => 'Bad Gateway',               // 502
        self::STATUS_SERVICE_UNAVAILABLE => 'Service Unavailable',       // 503
        self::STATUS_GATEWAY_TIMEOUT     => 'Gateway Timeout',           // 504
        self::STATUS_VERSION_NOT_SUPPORTED                               // 505
                                         => 'HTTP Version Not Supported',
        self::STATUS_VARIANT_ALSO_NEGOTIATES                             // 506
                                         => 'Variant Also Negotiates',
        self::STATUS_INSUFFICIENT_STORAGE                                // 507
                                         => 'Insufficient Storage',
        self::STATUS_LOOP_DETECTED       => 'Loop Detected',             // 508
        self::STATUS_NOT_EXTENDED        => 'Not Extended',              // 510
        self::STATUS_NETWORK_AUTHENTICATION_REQUIRED                     // 511
                                         => 'Network Authentication Required',
                                                                         // 599
        599                              => 'Network Connect Timeout Error',
    ];

    /**
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var mixed  the unserialized and uncompressed message body
     */
    private $rawBody;

    /**
     * @param null|string|resource|StreamInterface $body
     * @param null|int                    $status  Status code for the response, if any.
     * @param null|array                  $headers for the response, if any.
     * @throws InvalidArgumentException on any invalid element.
     */
    public function __construct(
        $body    = null,
        $status  = null,
        $headers = []
    )
    {
        parent::__construct( $body, (array) $headers );
        $this->setStatusCode( $status ?? self::STATUS_OK );
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase() : string
    {
        if( empty( $this->reasonPhrase ) &&
            isset( $this->phrases[$this->statusCode] )) {
            $this->reasonPhrase = $this->phrases[$this->statusCode];
        }
        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus( $code, $reasonPhrase = '' ) : ResponseInterface
    {
        $new = clone $this;
        $new->setStatusCode( $code );
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    /**
     * Set a valid status code.
     *
     * @param int $code
     * @throws InvalidArgumentException on an invalid status code.
     * @return static
     */
    private function setStatusCode( int $code ) : self
    {
        self::assertStatusCode( $code );
        $this->statusCode = $code;
        return $this;
    }

    /**
     * Assert status code.
     *
     * @param int $code
     * @throws InvalidArgumentException on an invalid status code.
     */
    public static function assertStatusCode( int $code )
    {
        static $FMT = 'Invalid status code "%s" (%d - %d)';
        if( ! self::isStatusCodeValid( $code )) {
            throw new InvalidArgumentException(
                sprintf(
                    $FMT,
                    ( is_scalar($code) ? $code : gettype( $code )),
                    static::MIN_STATUS_CODE_VALUE,
                    static::MAX_STATUS_CODE_VALUE
                )
            );
        }
    }

    /**
     * Return true i statusCode is int and 1xx-599
     *
     * @param mixed $code
     * @return bool
     */
    public static function isStatusCodeValid( $code ) : bool
    {
        return (( $code == intval( $code ))  &&
            ( $code >= static::MIN_STATUS_CODE_VALUE ) &&
            ( $code <= static::MAX_STATUS_CODE_VALUE ));
    }

    /**
     * {@inheritdoc}
     */
    public function getRawBody()
    {
        return $this->rawBody;
    }

    /**
     * {@inheritdoc}
     */
    public function isRawBodyEmpty() : bool
    {
        // return ( empty( $this->rawBody ));
        return ( empty( $this->rawBody ) && ( '0' != $this->rawBody ));
    }

    /**
     * {@inheritdoc}
     */

    public function withRawBody( $rawBody = null ) : ResponseInterface
    {
        $new          = clone $this;
        $new->rawBody = $rawBody;
        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function isBodyEmpty() : bool
    {
        return empty( $this->getBody()->getSize());
        /*
        $size = $this->getBody()->getSize();
        echo __FUNCTION__ . ' size=' . $size . PHP_EOL; // test ###
        return empty( $size );
        */
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseBody()
    {
        if( ! $this->isRawBodyEmpty()) {
            return $this->getRawBody();
        }
        if( ! $this->isBodyEmpty()) {
            $stream = $this->getBody();
            $stream->rewind();
            return $stream->getContents();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isBodyLessResponse() : bool
    {
        $statusCode = $this->getStatusCode();
        switch( true ) {
            case (( self::STATUS_CONTINUE <= $statusCode ) && ( self::STATUS_OK > $statusCode )) :
                return true; // 1xx
            case ( self::STATUS_NO_CONTENT == $statusCode ) :
                return true; // 204
            case ( self::STATUS_NOT_MODIFIED == $statusCode ) :
                return true; // 304
            default :
                break;
        }
        return false;
    }
}
