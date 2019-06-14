<?php
/**
 * (c) 2019 cepharum GmbH, Berlin, http://cepharum.de
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2019 cepharum GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE
 * SOFTWARE.
 *
 * @author: cepharum
 */

namespace Request;

Config::detectCurrent( '' );

$folder = BASEDIR . '/config';


$items = [];


$dir = opendir( $folder );
while ( ( $entry = readdir( $dir ) ) !== false ) {
	if ( $entry[0] === '.' || !preg_match( '/^(.+)\.yaml$/', $entry, $match ) ) {
		continue;
	}

	try {
		$config = Config::load( $match[1] );
	}
	catch ( HttpException $exception ) {
		continue;
	}

	$title = $config->query( 'title' );
	if ( $title ) {
		$title = L10n::localize( $title );
		$item = '* [' . $title . '](' . Url::getForForm( $match[1] ) . ')';

		$teaser = $config->query( 'teaser' );
		if ( $teaser ) {
			$teaser = L10n::localize( $teaser );
			$item .= "\n\n  " . implode( "\n  ", array_map( function ( $line ) {
					return ltrim( $line );
				}, preg_split( '/\r?\n/', trim( $teaser ) ) ) );
		}

		$items[] = $item;
	}
}
closedir( $dir );


Page::showView( 'success', [
	'class'   => 'overview',
	'title'   => L10n::translate( 'overview.title' ),
	'message' => implode( "\n\n", $items ),
] );
