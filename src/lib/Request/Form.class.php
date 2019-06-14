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


if ( !array_key_exists( 'forms', $_SESSION ) || !is_array( $_SESSION['forms'] ) ) {
	$_SESSION['forms'] = [];
}


/**
 * Manages form sessions mostly to prevent XSRF attacks.
 *
 * @package Request
 */
class Form {
	/**
	 * Starts new form session.
	 *
	 * @return string ID of new form session
	 * @throws Throwable
	 */
	public static function startSession() {
		// drop outdated form sessions
		$maxSessionsAge = Setup::get( 'limits.age-form-sessions', 6 * 60 * 60 );
		foreach ( $_SESSION['forms'] as $key => $time ) {
			if ( time() - $time > $maxSessionsAge ) {
				unset( $_SESSION['forms'][$key] );
			}
		}

		// limit number of running sessions as well to lower risk of pollutions
		$maxSessions = Setup::get( 'limits.num-form-sessions', 10000 );
		if ( count( $_SESSION['forms'] ) > $maxSessions ) {
			uasort( $_SESSION['forms'], function( $l, $r ) { return $l - $r; } );

			array_splice( $_SESSION['forms'], 0, -$maxSessions );
		}

		// create new session
		$id = bin2hex( random_bytes( 16 ) );

		$_SESSION['forms'][$id] = time();


		return $id;
	}

	/**
	 * Detects if given ID addresses existing session.
	 *
	 * @param string $id form session ID to test
	 * @return bool true if form session exists
	 */
	public static function sessionExists( $id ) {
		return array_key_exists( $id, $_SESSION['forms'] );
	}

	/**
	 * Detects if given ID addresses session that has matured by means of having
	 * been created a sufficient time ago.
	 *
	 * @param string $id form session ID to test
	 * @return bool true if form session exists and has matured
	 */
	public static function sessionHasMatured( $id ) {
		return array_key_exists( $id, $_SESSION['forms'] ) && time() - $_SESSION['forms'][$id] > Setup::get( 'limits.form-sessions-maturity', 10 );
	}

	/**
	 * Drops matured form session selected by its unique ID.
	 *
	 * @param string $id ID of form session to drop
	 */
	public static function dropSession( $id ) {
		if ( self::sessionHasMatured( $id ) ) {
			unset( $_SESSION['forms'][$id] );
		}
	}
}
