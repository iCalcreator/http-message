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

use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use Exception;

/**
 * class ResponseTest
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 */
class ResponseTest extends TestCase
{

    /**
     * @var string
     */
    private $fileName = null;

    protected function tearDown()
    {
        if( ! empty( $this->fileName ) && is_file( $this->fileName )) {
            unlink( $this->fileName );
        }
    }

    /**
     * @param null|mixed $data
     * @return resource
     */
    private function getResourceHandle( $data )
    {
        $this->fileName = tempnam( sys_get_temp_dir(), "test" );
        $handle         = fopen( $this->fileName, "wb+" );
        fwrite( $handle, (string) $data ?? '' );
        return $handle;
    }

    /**
     * Testing response status
     *
     * @test
     * @ // expectedException InvalidArgumentException
     */
    public function testSetStatusCode()
    {
        $data     = '1!2"3#4¤5%6&7/8(9)0=+?*.:,;';
        $this->getResourceHandle( $data );
        try {
            $stream = new Stream( null, fopen( $this->fileName, 'wb+' ));
            $reponse = new Response( $stream, 1234, [] );
            $this->expectException( InvalidArgumentException::class );
        }
        catch( InvalidArgumentException $e ) {}
        catch( Exception $e ) {}
        $this->assertTrue( true );
    }

    /**
     * test empty rawBody
     *
     * @test
     */
    public function testrawBody1()
    {
        $response = new Response();
        $response = $response->withRawBody();
        $this->assertEmpty( $response->getRawBody());
        $this->assertTrue( $response->isRawBodyEmpty());
        $this->assertEquals( null, $response->getResponseBody());
    }

    /**
     * testBody2 provider
     */
    public function body2Provider() : array
    {

        $dataArr       = [];

        $dataArr[] = [
            11, null, null, null    // expects rawBody
        ];

        $dataArr[] = [
            21, 1, null, 1          // expects rawBody
        ];

        $dataArr[] = [
            22, null, 1, 1          // expects body
        ];

        $dataArr[] = [
            23, 1, 2, 1             // expects rawBody
        ];

        $dataArr[] = [
            31, 0, null, 0          // expects rawBody
        ];

        $dataArr[] = [
            32, null, 0, 0          // expects body
        ];

        $dataArr[] = [
            33, 0, 1, 0             // expects rawBody
        ];

        $dataArr[] = [
            41, false, null, false  // expects rawBody
        ];

        $dataArr[] = [
            42, null, false, false  // expects body
        ];

        $dataArr[] = [
            43, false, 1, false     // expects rawBody
        ];

        $dataArr[] = [
            61, true, null, true    // expects rawBody
        ];

        $dataArr[] = [
            62, null, true, true    // expects body
        ];

        $dataArr[] = [
            63, true, 0, true       // expects rawBody
        ];

        $dataArr[] = [
            71, 1, '', 1            // expects rawBody
        ];

        $dataArr[] = [
            72, '', 1, 1            // expects body
        ];

        return $dataArr;
    }

    /**
     * test empty body
     *
     * @test
     * @dataProvider body2Provider
     *
     * @param int    $case
     * @param string $rawBody
     * @param string $body
     * @param string $expected
     */
    public function testBody2(
        int $case,
        $rawBody,
        $body,
        $expected
    )
    {
        $response = new Response();
        $response = $response->withRawBody( $rawBody );

        $response = $response->withBody(
            Stream::factoryFromResource( $this->getResourceHandle( $body ))
        );

        $this->assertEquals(
            $expected,
            $response->getResponseBody(),
            'error in case #' . $case .
            ' rawBody: '  . var_export( $rawBody,  true ) .
            ' body: '     . var_export( $body,     true ) .
            ' expected: ' . var_export( $expected, true )
        );

        if( empty( $rawBody ) && ( '0' != $rawBody )) {
            $this->assertTrue( $response->isRawBodyEmpty());
        }

        if( empty( $body ) && ( '0' != $body )) {
            $this->assertTrue( $response->isBodyEmpty());
        }
    }

    /**
     * test response
     *
     * @test
     */
    public function testrawBody3()
    {
        $data     = '1!2"3#4¤5%6&7/8(9)0=+?*.:,;';
        $response = new Response();
        $response = $response->withRawBody( $data )
                             ->withStatus( Response::STATUS_INTERNAL_SERVER_ERROR );

        $this->assertEquals( Response::STATUS_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals( 'Internal Server Error', $response->getReasonPhrase());

        $this->assertEquals( $data, $response->getRawBody());
        $this->assertEquals( $data, $response->getResponseBody());

    }

    /**
     * testisBodyLessResponse provider
     */
    public function isBodyLessResponseProvider() : array
    {
        $dataArr = [];

        $dataArr[] = [
                new Response(),
                false,
        ];

        $dataArr[] = [
                new Response( 'php://memory', Response::STATUS_CONTINUE ),
                true,
        ];

        $dataArr[] = [
                new Response( 'php://memory', Response::STATUS_NO_CONTENT ),
                true,
        ];

        $dataArr[] = [
                new Response( 'php://memory', Response::STATUS_NOT_MODIFIED ),
                true,
        ];

        $dataArr[] = [
                new Response( 'php://memory', Response::STATUS_INTERNAL_SERVER_ERROR ),
                false,
        ];

        return $dataArr;
    }

    /**
     * test Response statusCodes
     *
     * @test
     * @dataProvider isBodyLessResponseProvider
     * @param ResponseInterface $response
     * @param bool              $expected
     */
    public function testisBodyLessResponse(
        ResponseInterface $response,
        bool $expected
    )
    {
        $this->assertEquals( $expected, $response->isBodyLessResponse());
    }
}
