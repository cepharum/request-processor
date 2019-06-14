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

use PDO;
use Throwable;


// disable use of "current" configuration
Config::detectCurrent( '' );


list( $id, $key ) = explode( '-', $_GET['validate'], 2 );
if ( !$id || !$key ) {
	throw new HttpException( 'invalid validation request', 400 );
}


$db    = Database::get();
$table = Database::tableName( 'requests' );

$db->beginTransaction();

try {
	$userInput = [];

	$entry = Database::row( /** @lang mysql */ "SELECT formid, validationkey, querykey, ts_validated, ts_adminmail, yaml FROM $table WHERE id=?", [ $id ] );
	if ( !$entry ) {
		throw new HttpException( 'no such entry', 404 );
	}

	if ( $entry['validationkey'] != $key ) {
		throw new HttpException( 'unauthorized request for validating selected entry', 400 );
	}

	$userInput = spyc_load( $entry['yaml'] );

	if ( !$entry['ts_validated'] ) {
		Database::exec( /** @lang mysql */ "UPDATE $table SET ts_validated=? WHERE id=?", [ time(), $id ] );
	}

	if ( !$entry['ts_adminmail'] ) {
		Mail::sendNotification( $id );
	}

	$db->commit();

	Page::showView( 'success', [
		'title'   => Interpolate::braces( L10n::localize( Config::current( 'validation.title' ) ), $userInput ),
		'message' => Interpolate::braces( L10n::localize( Config::current( 'validation.onSuccess' ) ), $userInput ),
	] );
}
catch ( Throwable $exception ) {
	$db->rollBack();

	Page::showView( 'failure', [
		'title'   => Interpolate::braces( L10n::localize( Config::current( 'validation.title' ) ), $userInput ),
		'message' => Interpolate::braces( L10n::localize( Config::current( 'validation.onFailure' ) ), $userInput ),
		'error'   => $exception,
	] );
}
