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
    /**
     * Kigkonsult\Http\Message autoloader
     */
spl_autoload_register(
    function( $class ) {
        static $PREFIX = 'Kigkonsult\\Http\\Message\\';
        static $BS     = '\\';
        static $FMT    = '%1$s%2$s%3$s.php';
        if ( 0 != strncmp( $PREFIX, $class, 24 )) {
            return;
        }
        $class = substr( $class, 24 );
        if ( false !== strpos( $class, $BS )) {
            $class = str_replace( $BS, DIRECTORY_SEPARATOR, $class );
        }
        $file = sprintf( $FMT, __DIR__, DIRECTORY_SEPARATOR, $class );
        if ( file_exists( $file )) {
            include $file;
        }
    }
);
