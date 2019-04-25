<?php
/**
 * http-message, a Psr\Http\Message implementation
 *
 * copyright (c) 2019 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
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

use PHPUnit\Framework\TestCase;

/**
 * class MessageTest
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 */
class MessageTest extends TestCase
{
    private static $loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer at ligula quis odio pellentesque aliquam eget a lorem. Nulla et justo in quam ullamcorper faucibus. Fusce iaculis sagittis ligula quis scelerisque. Proin vitae enim cursus, ullamcorper sapien non, consectetur justo. Maecenas ligula velit, semper vitae posuere nec, imperdiet at metus. Nulla egestas, turpis lacinia aliquet iaculis, tellus velit imperdiet leo, dignissim commodo quam lacus eget libero. Integer nec sodales sapien. Nunc ullamcorper malesuada sem in condimentum. Etiam facilisis, nisl non eleifend dapibus, ipsum nisi efficitur sapien, eu placerat nisl risus id sapien. Duis vulputate ut tellus sagittis feugiat. Aenean tincidunt sodales est, ac condimentum augue aliquet finibus. Maecenas ultricies, tortor non volutpat imperdiet, sapien turpis sodales sapien, sed mollis diam elit eu arcu. Quisque placerat egestas tempus. Morbi libero sapien, molestie quis ligula nec, eleifend mattis turpis.In lacinia, tortor ut vehicula viverra, arcu nisl ornare metus, at porta quam est eu dolor. Donec pretium mauris nibh, vel egestas lorem bibendum non. Cras tincidunt consequat nisi. Maecenas volutpat turpis non urna laoreet, sed molestie metus hendrerit. Nam a ultricies justo, ut ullamcorper ipsum. Praesent sed consequat quam. Suspendisse id lacinia quam.
Phasellus in neque sed nisl hendrerit facilisis. Nulla eu elit gravida, condimentum nulla id, fringilla enim. Nulla maximus dui id vehicula sagittis. Vestibulum tincidunt porta risus, eu mattis lectus laoreet eget. Sed tristique sit amet dui venenatis condimentum. Donec lobortis diam sed lectus blandit posuere. Quisque odio arcu, condimentum nec tempor ut, faucibus sit amet sem. Nullam ut porta arcu, accumsan tempus orci. Mauris id eros sit amet urna fringilla pulvinar. Aenean vitae ligula id ante ultrices consequat.
Vestibulum eget cursus enim. Curabitur nec elementum ex. Vestibulum non tortor purus. Quisque a mi sit amet erat consequat ultrices at in lectus. Proin urna sapien, ornare nec tempor non, dignissim bibendum ante. Etiam sem dolor, dapibus eget interdum vitae, dapibus a tellus. Duis volutpat at erat quis volutpat. Vestibulum id sollicitudin magna, quis mattis turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam pellentesque mollis aliquet. Ut tincidunt velit dui, quis accumsan dui auctor et. Vivamus eu arcu eu risus fermentum pharetra. In luctus dui nulla, at finibus nibh ornare at. Sed porttitor consequat accumsan. Sed vel diam dolor. Cras vitae malesuada sapien.
Quisque vel odio id tortor congue malesuada. Praesent a ex turpis. Donec pharetra elit ut bibendum semper. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris feugiat elit nec sapien imperdiet, in condimentum nibh sodales. Sed nec accumsan felis. Nulla leo turpis, egestas sed tincidunt eu, bibendum in libero.';

    /**
     * messageBodyTest1 provider
     */
    public function messageBodyTest1Provider() {

        $dataArr       = [];

        $dataArr[] = [
            111,
            null,
            null,
            null,
        ];

        $dataArr[] = [
            112,
            '',
            '',
            null,
        ];

        $dataArr[] = [
            113,
            self::$loremIpsum,
            self::$loremIpsum,
            null,
        ];

        $file    = tempnam( sys_get_temp_dir(), "test" );
        $handle  = fopen( $file, "wb+" );
        $dataArr[] = [
            121,
            $handle,
            null,
            $file,
        ];

        $file    = tempnam( sys_get_temp_dir(), "test" );
        $handle  = fopen( $file, "wb+" );
        fwrite( $handle, '' );
        $dataArr[] = [
            122,
            $handle,
            '',
            $file,
        ];

        $file    = tempnam( sys_get_temp_dir(), "test" );
        $handle  = fopen( $file, "wb+" );
        fwrite( $handle, self::$loremIpsum );
        $dataArr[] = [
            123,
            $handle,
            self::$loremIpsum,
            $file,
        ];

        return $dataArr;
    }

    /**
     * test Message body
     *
     * @test
     * @dataProvider messageBodyTest1Provider
     *
     * @param int    $case
     * @param string $protocol
     * @param string $expected
     * @param string $file
     */
    public function messageBodyTest1(
        $case,
        $body,
        $expected,
        $file
    ) {
        static $FMTERR = 'Error %d in case#%d';

        $message = new Message( $body );
        $content = $message->getBody();
        $content->rewind();
        $this->assertEquals(
            $expected,
            $content->getContents(),
            sprintf( $FMTERR, 1,  $case )
        );

        if( ! empty( $file )) {
            unlink( $file );
        }
    }

    /**
     * messageHeaderTest2 provider
     */
    public function messageHeaderTest2Provider() {

        $dataArr       = [];

        $dataArr[] = [
            211,
            'hEaDeRnAMe',
            'headerValue',
        ];

        $dataArr[] = [
            212,
            'hEaDeRnAMe',
            [ 'headerValue1', 'headerValue2' ]
        ];

        return $dataArr;
    }

    /**
     * test Message headers
     *
     * @test
     * @dataProvider messageHeaderTest2Provider
     *
     * @param int    $case
     * @param string $headerName
     * @param string $headerValue
     */
    public function messageTest2(
        $case,
        $headerName,
        $headerValue
    ) {
        static $FMTERR   = 'Error %d in case#%d';
        static $APPENDIX = 'appendix';

        $message = new Message( null, [ $headerName => $headerValue ] );

        $this->assertTrue(
            $message->hasHeader( $headerName ),
            sprintf( $FMTERR, 1,  $case )
        );

        $this->assertEquals(
            (array) $headerValue,
            $message->getHeader( $headerName ),
            sprintf( $FMTERR, 2,  $case )
        );

        $this->assertEquals(
            [ $headerName => (array) $headerValue ],
            $message->getHeaders(),
            sprintf( $FMTERR, 3,  $case )
        );

        $this->assertEquals(
            implode( ',', (array) $headerValue ),
            $message->getHeaderLine( $headerName ),
            sprintf( $FMTERR, 4,  $case )
        );

        $message = $message->withHeader( $headerName, $APPENDIX );
        $this->assertEquals(
            $APPENDIX,
            $message->getHeaderLine( $headerName ),
            sprintf( $FMTERR, 5,  $case )
        );

        $message = $message->withHeader( $headerName, $headerValue )
                           ->withAddedHeader( $headerName, $APPENDIX );
        $expected   = (array) $headerValue;
        $expected[] = $APPENDIX;
        $this->assertEquals(
            implode( ',', $expected ),
            $message->getHeaderLine( $headerName ),
            sprintf( $FMTERR, 6,  $case )
        );

        $message = $message->withoutHeader( $headerName );
        $this->assertEquals(
            [],
            $message->getHeader( $headerName ),
            sprintf( $FMTERR, 7,  $case )
        );

    }

    /**
     * messageProtocolTest3 provider
     */
    public function messageProtocolTest3Provider() {

        $dataArr       = [];

        $dataArr[] = [
            301,
            null,
            '1.1',
        ];

        $dataArr[] = [
            302,
            '1.2',
            '1.2',
        ];

        return $dataArr;
    }

    /**
     * test Message protocol
     *
     * @test
     * @dataProvider messageProtocolTest3Provider
     *
     * @param int    $case
     * @param string $protocol
     * @param string $expected
     */
    public function messageProtocolTest3(
        $case,
        $protocol,
        $expected
    ) {
        static $FMTERR = 'Error %d in case#%d';

        $message = new Message( null, null, $protocol );
        $this->assertEquals(
            $expected,
            $message->getProtocolVersion(),
            sprintf( $FMTERR, 1,  $case )
        );

        $otherStatus = '1.0';
        $message = $message->withProtocolVersion( $otherStatus );
        $this->assertEquals(
            $otherStatus,
            $message->getProtocolVersion(),
            sprintf( $FMTERR, 2,  $case )
        );

        if( ! empty( $protocol )) {
            $message = $message->withProtocolVersion( $protocol );
            $this->assertEquals(
                $expected,
                $message->getProtocolVersion(),
                sprintf( $FMTERR, 3,  $case )
            );
        }
    }

}
