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

use Psr\Http\Message\ResponseInterface as Master;
use Fig\Http\Message\StatusCodeInterface;
use RuntimeException;

/**
 * Extends Psr\Http\Message\ResponseInterface
 * with
 *   (new property: rawBody)
 *   methods
 *     getRawBody()
 *     isRawBodyEmpty()
 *     withRawBody()
 *     isBodyEmpty()
 *     getResponseBody()
 *     isBodyLessResponse()
 */
interface ResponseInterface extends Master, StatusCodeInterface
{
    /**
     * Return rawBody, i.e. the unserialized and uncompressed message body
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @return mixed
     */
    public function getRawBody();

    /**
     * Return bool true if rawBody is empty
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @return bool
     */
    public function isRawBodyEmpty() : bool;

    /**
     * Return an instance with rawBody
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @param mixed $rawBody
     * @return static
     */
    public function withRawBody( $rawBody = null ) : ResponseInterface;

    /**
     * Return bool true if body or rawBody is empty
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @return bool
     */
    public function isBodyEmpty() : bool;

    /**
     * Return response body from rawBody or body(stream)
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @return mixed
     * @throws RuntimeException on Stream error
     */
    public function getResponseBody();

    /**
     * Return bool true if instance is a no-body-response
     *
     * "Any response message which "MUST NOT" include a message-body
     * (such as the 1xx, 204, and 304 responses and any response to a HEAD request)
     * is always terminated by the first empty line after the header fields,
     * regardless of the entity-header fields present in the message."
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec4.html#sec4.4
     *
     * A non Psr\Http\Message\ResponseInterface method
     *
     * @return bool
     */
    public function isBodyLessResponse() : bool;
}