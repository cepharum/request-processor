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


if ( $_GET['send_validation'] ) {
	// disable check for having current configuration
	Config::detectCurrent( '' );


	if ( $_GET['send_validation'] !== Setup::get( 'admin.password' ) ) {
		throw new HttpException( 'access denied', 403 );
	}

	$mailAddress = $_GET['mail'];
	if ( $mailAddress ) {
		$stats  = Mail::sendValidation( $mailAddress, !Data::asBoolean( @$_GET['send_all'], 'no' ) );
		$result = [];

		if ( !$stats || !is_array( $stats ) ) {
			$result[] = L10n::translate( 'validate.resend.unknown' );
		} else {
			$num = [
				'success'  => count( $stats['success'] ),
				'deferred' => count( $stats['deferred'] ),
				'failure'  => count( $stats['failure'] ),
			];

			if ( $num['success'] == 0 && $num['deferred'] == 0 && $num['failure'] == 0 ) {
				$result[] = L10n::interpolate( 'validate.resend.none', 1, $num );
			}

			if ( $num['success'] > 0 ) {
				$result[] = L10n::interpolate( 'validate.resend.success', $num['success'], $num );
			}

			if ( $num['deferred'] > 0 ) {
				$result[] = L10n::interpolate( 'validate.resend.deferred', $num['deferred'], $num );
			}

			if ( $num['failure'] > 0 ) {
				$result[] = L10n::interpolate( 'validate.resend.failure', $num['failure'], $num );
			}
		}

		$result = implode( "\n\n", $result );

		Page::showView( 'success', [
			'title'   => L10n::translate( 'validate.resend.title' ),
			'message' => $result,
		] );
	} else {
		Page::showView( 'form/input', [
			'title'  => L10n::translate( 'validate.resend.title' ),
			'label'  => L10n::translate( 'validate.resend.form-label' ),
			'name'   => 'mail',
			'url'    => Url::get(),
			'hidden' => [
				'send_validation' => $_GET['send_validation'],
			],
		] );
	}
}
