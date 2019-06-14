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


if ( !preg_match( '/^(?:(yaml|csv):)?([^-]+)-(.+)$/', $_GET['query'], $match ) ) {
	throw new HttpException( 'invalid query', 400 );
}


Config::detectCurrent( '' );

$format = $match[1];
$id     = $match[2];
$key    = $match[3];

if ( $id && $key ) {
	switch ( $format ) {
		case 'yaml' :
			downloadRequest( $id, $key, 'yaml' );
			exit;

		case 'csv' :
		default :
			downloadRequest( $id, $key, 'csv' );
			exit;
	}
} else {
	throw new HttpException( 'invalid selectors in query', 400 );
}


/**
 * This function is used by the site-admin to download the YAML-data
 * of a request.
 *
 * The URL with the ID and the correct key to query the request's data
 * is send to the site-admin by mail, e.g.
 *        https://<host>/?query=1-130772a1cdfa232c0b1149a3b9023603
 *
 * STEP 3: The YAML-data of the given request
 *         will be downloaded to the client.
 *
 * The function doesn't return!
 *
 * The YAML-data is send to the client. In case of an error
 * a web page with the error-message will be showed.
 *
 * @param int $id
 *        Database-ID of the record with the input form's data
 * @param string $key
 *        For security reason the string which is saved
 *        as 'querykey' has to be given, too.
 * @throws Throwable
 */
function downloadRequest( $id, $key, $format ) {
	$db    = Database::get();
	$table = Database::tableName( 'requests' );

	$db->beginTransaction();

	try {
		$stmt = $db->prepare( /** @lang mysql */ "SELECT formid, querykey, yaml FROM $table WHERE id=? AND querykey=?" );
		$stmt->execute( [ $id, $key ] );
		$entry = $stmt->fetch( PDO::FETCH_ASSOC );

		if ( !$entry ) {
			throw new HttpException( 'no such record', 404 );
		}

		$stmt = $db->prepare( /** @lang mysql */ "UPDATE $table SET ts_grabbed=? WHERE id=?" );
		$stmt->execute( [ time(), $id ] );

		switch ( $format ) {
			case 'yaml' :
				$yaml = preg_replace( '/\r?\n/', "\r\n", $entry['yaml'] );

				Page::render( 'raw', [
					'name' => 'input-' . $entry['formid'] . '-id-' . $id . '.yaml.txt',
					'type' => 'text/plain',
					'data' => $yaml,
				] );
				break;

			case 'csv' :
			default :
				$record = spyc_load( $entry['yaml'] );
				$csv    = Data::getCSV( [ $record ] );

				Page::render( 'raw', [
					'name' => 'input-' . $entry['formid'] . '-id-' . $id . '.csv',
					'type' => 'text/csv',
					'data' => $csv,
				] );
		}

		$db->commit();
	}
	catch ( Throwable $exception ) {
		$db->rollBack();

		throw $exception;
	}
}
