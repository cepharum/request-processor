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
 * Manages URLs related to current application.
 *
 * @package Request
 */
class Url {
	/**
	 * Compiles URL addressing current application with optional set of query
	 * parameters.
	 *
	 * @param array $query optional set of query parameters to include with URL
	 * @return string compiled URL
	 */
	public static function get( $query = [] ) {
		$query = count( $query ) ? '?' . http_build_query( $query ) : '';

		return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . $query;
	}

	/**
	 * Returns name of query parameter selecting current type of request.
	 *
	 * @return string
	 * @throws HttpException
	 */
	public static function getRequestParameterName() {
		return Setup::get( 'parameters.request', 'request' );
	}

	/**
	 * Retrieves ID selecting kind of request.
	 *
	 * @param boolean $relax set true to return null on missing request ID instead of throwing exception
	 * @return string ID selecting kind of request
	 * @throws HttpException
	 */
	public static function getRequestId( $relax = false ) {
		$requestId = @$_GET[static::getRequestParameterName()];
		if ( !$requestId && !$relax ) {
			throw new HttpException( 'missing request selection', 404 );
		}

		return $requestId;
	}

	/**
	 * Fetches URL addressing particular form selected by its ID.
	 *
	 * @param string $formId ID of form to be addressed
	 * @param array $query additional query parameters to include with resulting URL
	 * @return string resulting URL
	 * @throws HttpException on trouble accessing setup
	 */
	public static function getForForm( $formId, $query = [] ) {
		if ( !is_array( $query ) ) {
			$query = [];
		}

		$query[static::getRequestParameterName()] = $formId;

		return static::get( $query );
	}
}
