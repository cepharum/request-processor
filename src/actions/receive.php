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

use Throwable;


// make sure to fetch a currently selected request
Config::current();


// make sure there is a mature session started for provided form's input
$formId = $_GET['receive'];
if ( !Form::sessionExists( $formId ) ) {
	throw new HttpException( 'invalid form ID (value)', 400 );
}

if ( !Form::sessionHasMatured( $formId ) ) {
	throw new HttpException( 'invalid form ID (maturity)', 400 );
}


// read request body
$rawInput = file_get_contents( 'php://input' );

// parse request body for contained JSON and basically validate its data
$data = json_decode( $rawInput, true );
if ( trim( $rawInput ) == '' || !is_array( $data ) || !count( $data ) || json_last_error() != JSON_ERROR_NONE ) {
	throw new HttpException( 'invalid or missing form input data', 400 );
}


// extracting customer's mail address from provided input data for validation
$mail = array_key_exists( 'validation_mail', $data ) ? $data['validation_mail'] : null;


// create record in database
$db    = Database::get();
$table = Database::tableName( 'requests' );

$db->beginTransaction();

try {
	$stmt = $db->prepare( /** @lang mysql */ "INSERT INTO $table (formid,json,yaml,mailaddress,validationkey,querykey,ts_received) VALUES (?,?,?,?,?,?,?)" );
	$stmt->execute( [
		Url::getRequestId(),
		$rawInput,
		spyc_dump( $data ),
		$mail,
		bin2hex( random_bytes( 16 ) ),
		bin2hex( random_bytes( 16 ) ),
		time()
	] );

	if ( $mail ) {
		$sent = Mail::sendValidation( $mail, true );
		if ( !count( $sent['success'] ) ) {
			throw new HttpException( 'sending mail for validating request failed', 500 );
		}
	} else {
		Mail::sendNotification( $db->lastInsertId() );
	}

	$db->commit();

	Form::dropSession( $formId );
}
catch ( Throwable $exception ) {
	$db->rollBack();

	throw $exception;
}


Page::render( 'json', [
	'success'    => true,
	'validation' => $mail ? 'required' : 'skipped',
] );
