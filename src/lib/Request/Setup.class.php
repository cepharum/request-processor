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
 * Exposes configuration of application as a whole.
 *
 * In opposition to @see Config this class is exposing configuration to be
 * applied without regards to some currently selected type of request.
 *
 * @package Request
 */
class Setup extends Data {
	/**
	 * @var Setup cache of previously loaded configurations
	 */
	protected static $_cache = null;

	/**
	 * Fetches setup configuration.
	 *
	 * @return Setup loaded setup configuration
	 * @throws HttpException on invalid configuration ID
	 */
	public static function load() {
		if ( !static::$_cache ) {
			$configurationFile = BASEDIR . "/setup/server.yaml";

			if ( !file_exists( $configurationFile ) ) {
				throw new HttpException( 'missing setup configuration', 500 );
			}

			static::$_cache = new static( ( new \Spyc() )->loadFile( $configurationFile ) );
		}

		return static::$_cache;
	}

	/**
	 * Combines loading and querying setup in a single invocation for caller's
	 * convenience.
	 *
	 * @param string $query selector of desired information
	 * @param mixed $default value to return if query hits null value
	 * @param boolean $caseInsensitive set true to use lowercase variants of either marker's name for looking up related data
	 * @return mixed found value or provided default
	 * @throws HttpException
	 */
	public static function get( $query = null, $default = null, $caseInsensitive = false ) {
		return static::load()->query( $query, $default, $caseInsensitive );
	}
}
