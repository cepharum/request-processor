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
 * Drops all used tables in database.
 *
 */

if ( $_GET['dbdrop'] ) {
	// prevent exception on missing current configuration
	Config::detectCurrent( '' );


	if ( $_GET['dbdrop'] !== Setup::get( 'admin.password' ) ) {
		throw new HttpException( 'access denied', 403 );
	}

	if ( !Setup::get( 'database.volatile' ) ) {
		throw new HttpException( 'database is not marked volatile, you must set database.volatile in server.yaml', 403 );
	}


	$db    = Database::get();
	$table = Database::tableName( 'requests' );
	if ( $db->exec( /** @lang mysql */ "DROP TABLE $table" ) === false ) {
		throw new \PDOException( 'dropping requests failed' );
	}

	$title   = L10n::translate( 'Dropping Database' );
	$message = L10n::translate( 'Database has been dropped successfully.' );

	Page::write( <<<EOT
<div class="regular-content admin-drop">
	<h1>$title</h1>
	<p>$message</p>
</div>
EOT
	);

	Page::render();
}
