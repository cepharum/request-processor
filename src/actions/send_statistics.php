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


if ( $_GET['send_statistics'] ) {
	$token = $_GET['send_statistics'];

	if ( $token !== Setup::get( 'admin.password' ) ) {
		throw new HttpException( 'access denied', 403 );
	}


	Config::detectCurrent( '' );

	$formId = Url::getRequestId( true );
	if ( $formId ) {
		Mail::sendStatistics( $formId, !Data::asBoolean( @$_GET['send_all'], 'no' ) );

		Page::showView( 'success', [
			'title'   => L10n::translate( 'statistics.title' ),
			'message' => L10n::translate( 'statistics.success' ),
		] );
	} else {
		$db    = Database::get();
		$table = Database::tableName( 'requests' );

		$list = $db->query( /** @lang mysql */ "SELECT DISTINCT formid FROM $table" )
			->fetchAll( PDO::FETCH_ASSOC );

		$label    = L10n::translate( 'statistics.label.latest' );
		$labelAll = L10n::translate( 'statistics.label.all' );

		$list = array_map( function ( $record ) use ( $token, $label, $labelAll ) {
			$formId = $record['formid'];

			$url = Url::getForForm( $formId, [ 'send_statistics' => $token ] );

			return "* $formId: [$label]($url) / [$labelAll]($url&send_all=yes)";
		}, $list );

		Page::showView( 'success', [
			'title'   => L10n::translate( 'statistics.title' ),
			'message' => implode( "\n", $list ),
		] );
	}
}

