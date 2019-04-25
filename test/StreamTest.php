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
use InvalidArgumentException;
use Exception;

/**
 * class StreamTest
 *
 * @author      Kjell-Inge Gustafsson <ical@kigkonsult.se>
 */
class StreamTest extends TestCase
{
    private static $loremIpsum = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer at ligula quis odio pellentesque aliquam eget a lorem. Nulla et justo in quam ullamcorper faucibus. Fusce iaculis sagittis ligula quis scelerisque. Proin vitae enim cursus, ullamcorper sapien non, consectetur justo. Maecenas ligula velit, semper vitae posuere nec, imperdiet at metus. Nulla egestas, turpis lacinia aliquet iaculis, tellus velit imperdiet leo, dignissim commodo quam lacus eget libero. Integer nec sodales sapien. Nunc ullamcorper malesuada sem in condimentum. Etiam facilisis, nisl non eleifend dapibus, ipsum nisi efficitur sapien, eu placerat nisl risus id sapien. Duis vulputate ut tellus sagittis feugiat. Aenean tincidunt sodales est, ac condimentum augue aliquet finibus. Maecenas ultricies, tortor non volutpat imperdiet, sapien turpis sodales sapien, sed mollis diam elit eu arcu. Quisque placerat egestas tempus. Morbi libero sapien, molestie quis ligula nec, eleifend mattis turpis.In lacinia, tortor ut vehicula viverra, arcu nisl ornare metus, at porta quam est eu dolor. Donec pretium mauris nibh, vel egestas lorem bibendum non. Cras tincidunt consequat nisi. Maecenas volutpat turpis non urna laoreet, sed molestie metus hendrerit. Nam a ultricies justo, ut ullamcorper ipsum. Praesent sed consequat quam. Suspendisse id lacinia quam.
Phasellus in neque sed nisl hendrerit facilisis. Nulla eu elit gravida, condimentum nulla id, fringilla enim. Nulla maximus dui id vehicula sagittis. Vestibulum tincidunt porta risus, eu mattis lectus laoreet eget. Sed tristique sit amet dui venenatis condimentum. Donec lobortis diam sed lectus blandit posuere. Quisque odio arcu, condimentum nec tempor ut, faucibus sit amet sem. Nullam ut porta arcu, accumsan tempus orci. Mauris id eros sit amet urna fringilla pulvinar. Aenean vitae ligula id ante ultrices consequat.
Vestibulum eget cursus enim. Curabitur nec elementum ex. Vestibulum non tortor purus. Quisque a mi sit amet erat consequat ultrices at in lectus. Proin urna sapien, ornare nec tempor non, dignissim bibendum ante. Etiam sem dolor, dapibus eget interdum vitae, dapibus a tellus. Duis volutpat at erat quis volutpat. Vestibulum id sollicitudin magna, quis mattis turpis. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam pellentesque mollis aliquet. Ut tincidunt velit dui, quis accumsan dui auctor et. Vivamus eu arcu eu risus fermentum pharetra. In luctus dui nulla, at finibus nibh ornare at. Sed porttitor consequat accumsan. Sed vel diam dolor. Cras vitae malesuada sapien.
Quisque vel odio id tortor congue malesuada. Praesent a ex turpis. Donec pharetra elit ut bibendum semper. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Mauris feugiat elit nec sapien imperdiet, in condimentum nibh sodales. Sed nec accumsan felis. Nulla leo turpis, egestas sed tincidunt eu, bibendum in libero.';

    private static $fileName = [];

    public static function tearDownAfterClass() {
        foreach( self::$fileName as $file ) {
            unlink( $file );
        }
        self::$fileName = null;
    }

    /**
     * @param string $data
     * @return resource
     */
    private function getResourceHandle( $data ) {
        $fileName         = tempnam( sys_get_temp_dir(), "test" );
        self::$fileName[] = $fileName;
        $handle           = fopen( $fileName, "wb+" );
        fwrite( $handle, $data );
        return $handle;
    }

    /**
     * streamTest1+2 provider
     */
    public function streamTestProvider() {

        $dataArr       = [];

        $dataArr[] = [
            11,
            null,
            null,
            null,
            null,
        ];

        $dataArr[] = [
            12,
            self::$loremIpsum,
            null,
            null,
            self::$loremIpsum,
        ];

        $dataArr[] = [
            21,
            null,
            'php://memory',
            'wb+',
            null,
        ];

        $dataArr[] = [
            22,
            null,
            'php://memory',
            null,
            null,
        ];

        $dataArr[] = [
            23,
            self::$loremIpsum,
            'php://memory',
            'wb+',
            self::$loremIpsum,
        ];

        $dataArr[] = [
            24,
            self::$loremIpsum,
            'php://memory',
            null,
            self::$loremIpsum,
        ];

        $dataArr[] = [
            31,
            null,
            $this->getResourceHandle( null ),
            null,
            null,
        ];

        $dataArr[] = [
            32,
            null,
            $this->getResourceHandle( self::$loremIpsum ),
            null,
            self::$loremIpsum,
        ];

        return $dataArr;
    }

    /**
     * test Stream instance
     *
     * @test
     * @dataProvider streamTestProvider
     *
     * @param int    $case
     * @param string $content
     * @param string $streamWrapper
     * @param string $mode
     * @param string $expected
     */
    public function streamTest1(
        $case,
        $content,
        $streamWrapper,
        $mode,
        $expected
    ) {
        $this->streamTestX(
            100 + $case,
            new Stream( $content, $streamWrapper, $mode ),
            $expected
        );
    }

    /**
     * test Stream factory
     *
     * @test
     * @dataProvider streamTestProvider
     *
     * @param int    $case
     * @param string $content
     * @param string $streamWrapper
     * @param string $mode
     * @param string $expected
     */
    public function streamTest2(
        $case,
        $content,
        $streamWrapper,
        $mode,
        $expected
    ) {
        $this->streamTestX(
            200 + $case,
            Stream::factory( $content, $streamWrapper, $mode ),
            $expected
        );
    }

    /**
     * test Stream factoryFromString / factoryFromResource
     *
     * @test
     * @dataProvider streamTestProvider
     *
     * @param int    $case
     * @param string $content
     * @param string $streamWrapper
     * @param string $mode
     * @param string $expected
     */
    public function streamTest3(
        $case,
        $content,
        $streamWrapper,
        $mode,
        $expected
    ) {
        if( is_resource( $streamWrapper )) {
            $this->streamTestX(
                300 + $case,
                Stream::factoryFromResource( $streamWrapper ),
                $expected
            );
        }
        else {
            $this->streamTestX(
                400 + $case,
                Stream::factoryFromString( $content, $streamWrapper, $mode ),
                $expected
            );
        }
    }

    /**
     * @param int    $case
     * @param Stream $stream
     * @param string $expected
     */
    public function streamTestX(
        $case,
        $stream,
        $expected
    ) {
        static $FMTERR = 'Error %d in case #%d';

        $this->assertTrue(
            $stream->isReadable(),
            sprintf( $FMTERR, 1, $case )
        );
        $this->assertTrue(
            $stream->isWritable(),
            sprintf( $FMTERR, 2, $case )
        );

        $this->assertTrue(
            $stream->isSeekable(),
            sprintf( $FMTERR, 3, $case )
        );

        $stream->rewind();
        $this->assertEmpty(
            $stream->tell(),
            sprintf( $FMTERR, 4, $case )
        );

        $this->assertEquals(
            empty( $expected ),
            Stream::isStreamEmpty( $stream ),
            sprintf( $FMTERR, 5, $case )
        );

        $contentLength = strlen( $expected );
        $this->assertEquals(
            $contentLength,
            $stream->getSize(),
            sprintf( $FMTERR, 6, $case )
        );

        if( ! empty( $contentLength )) {
            $stream->seek( 0 );
            $this->assertEquals(
                $expected,
                $stream->read( $contentLength ),
                sprintf( $FMTERR, 7, $case )
            );
        }

        $stream->rewind();
        $this->assertEquals(
            $expected,
            $stream->getContents(),
            sprintf( $FMTERR, 7, $case )
        );
        $this->assertTrue(
            $stream->eof(),
            sprintf( $FMTERR, 8, $case )
        );

        $stream->detach();
    }

    /**
     * streamTest4 provider
     */
    public function streamTest4Provider() {

        $dataArr       = [];

        $dataArr[] = [
            41,
            'content',
            'php://memory',
            'r'
        ];

        return $dataArr;
    }

    /**
     * test Stream resource exceptions
     *
     * @test
     * @dataProvider streamTest4Provider
     *
     * @param int    $case
     * @param string $content
     * @param string wrapper
     * @param string $mode
     */
    public function streamTest4(
        $case,
        $content,
        $wrapper,
        $mode
    ) {
        static $FMTERR = 'Error in case #%d';
        $error = false;
        try {
            $stream = new Stream( $content, $wrapper, $mode );
        }
        catch( Exception $e ) {
            $error = true;
        }
        $this->assertTrue(
            $error,
            sprintf( $FMTERR, $case )
        );
    }

}
