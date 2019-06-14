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

/**
 * Lists existing requests sorted by time of reception in descending order.
 *
 */

if ( $_GET['dblist'] ) {
	// prevent exception on missing current configuration
	Config::detectCurrent( '' );


	if ( $_GET['dblist'] !== Setup::get( 'admin.password' ) ) {
		throw new HttpException( 'access denied', 403 );
	}

	$db     = Database::get();
	$table  = Database::tableName( 'requests' );
	$result = $db->query( /** @lang mysql */ "SELECT * FROM $table ORDER BY ts_received DESC", \PDO::FETCH_ASSOC );

	if ( $result ) {
		Page::write( '<div class="regular-content dump"><h1>List Of Entries</h1><p class="counter"># of entries: $$count$$</p>', [
			'count' => $result->rowCount(),
		] );

		$rows = [];
		while ( $row = $result->fetch( \PDO::FETCH_ASSOC ) ) {
			$cells = implode( "\n", array_filter( array_map( function ( $key, $value ) {
				if ( $key === 'id' ) {
					return false;
				}

				$key   = htmlentities( $key );
				$value = htmlentities( $value );

				return "<dt>$key</dt><dd class=\"$key\">$value</dd>";
			}, array_keys( $row ), array_values( $row ) ) ) );

			$rows[] = <<<EOT
<h2>id {$row['id']}</h2>
<dl>
	$cells
</dl>
EOT;
		}

		Page::write( '$$rows$$</div>', [
			'rows' => implode( "\n", $rows ),
		] );
	} else {
		throw new \PDOException( 'fetching database failed' );
	}

	Page::render();
}
