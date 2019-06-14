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

use Spyc;

/**
 * Exposes request configurations.
 *
 * In opposition to @see Setup this class exposes configuration of a particular
 * type of request which is selected according to some query parameter in every
 * request.
 *
 * @package Request
 */
class Config extends Data {
	/**
	 * @var Config[] cache of previously loaded configurations
	 */
	protected static $_cache = [];

	/**
	 * Fetches configuration selected by its ID.
	 *
	 * @param string $id ID of configuration to fetch
	 * @return Config loaded configuration
	 * @throws HttpException on invalid configuration ID
	 * @throws \Throwable
	 */
	public static function load( $id = null ) {
		if ( is_null( $id ) ) {
			$id = $_GET[Url::getRequestParameterName()];
		}

		if ( !preg_match( '#^[^./]+$#', $id ) ) {
			throw new HttpException( 'invalid configuration ID', 400 );
		}

		if ( !array_key_exists( $id, static::$_cache ) ) {
			$configurationFile = BASEDIR . "/config/$id.yaml";

			if ( !file_exists( $configurationFile ) ) {
				if ( $id === 'defaults' ) {
					$configurationFile = BASEDIR . "/config/$id.dist.yaml";

					if ( !file_exists( $configurationFile ) ) {
						throw new HttpException( 'no such configuration: ' . $id, 404 );
					}
				}
			}

			$yamlCode = @file_get_contents( $configurationFile );
			if ( !$yamlCode ) {
				throw new HttpException( 'accessing configuration failed' );
			}

			$record = ( new Spyc() )->load( $yamlCode );

			if ( $id !== 'defaults' ) {
				$merged = [];

				static::arrayMerge(
					$merged,
					config::load( 'defaults' )->record,
					$record,
					[ 'meta' => [
						'id'   => $id,
						'host' => $_SERVER['SERVER_NAME'],
						'url'  => Url::getForForm( $id ),
					] ]
				);

				$record = static::interpolate( $merged, [
					'requestname' => Url::getRequestParameterName(),
					'requestid'   => $id,
					'formid'      => Form::startSession(),
					'scripturl'   => Url::get(),
				] );
			}

			static::$_cache[$id] = new static( $record );
		}

		return static::$_cache[$id];
	}

	/**
	 * Deeply interpolates all strings in provided data structure.
	 *
	 * @param mixed $record data structure to be interpolated
	 * @param array $data set of named information to use in preference on
	 *     interpolating
	 * @return mixed provided data structure with all strings interpolated
	 * @throws HttpException
	 */
	protected static function interpolate( &$record, $data ) {
		foreach ( $record as $key => $value ) {
			if ( is_array( $value ) ) {
				static::interpolate( $record[$key], $data );
			} else if ( is_string( $value ) ) {
				$record[$key] = Interpolate::dollars( $value, $data, true );
			}
		}

		return $record;
	}

	/**
	 * Queries configuration of current request.
	 *
	 * @param string $query selector of particularly desired information
	 * @param mixed $default value to return if query hits null value
	 * @param boolean $caseInsensitive set true to ignore case of segments on querying data
	 * @return Config|mixed loaded configuration when invoked w/o query,
	 *     queried information otherwise
	 * @throws HttpException if there is no valid config ID in current request
	 */
	public static function current( $query = null, $default = null, $caseInsensitive = false ) {
		static::detectCurrent();

		if ( CURRENT_CONFIG === 'defaults' ) {
			throw new HttpException( 'invalid request for defaults', 400 );
		}

		$config = static::load( CURRENT_CONFIG === '' ? 'defaults' : CURRENT_CONFIG );

		return $query ? $config->query( $query, $default, $caseInsensitive ) : $config;
	}

	/**
	 * Detects ID of current request's associated configuration.
	 *
	 * @param string|null $id configuration ID to use explicitly, is obeyed on first call, only
	 * @throws HttpException
	 */
	public static function detectCurrent( $id = null ) {
		if ( !defined( 'CURRENT_CONFIG' ) ) {
			if ( is_null( $id ) ) {
				$id = Url::getRequestId();
			}

			define( 'CURRENT_CONFIG', $id );
		}

		if ( CURRENT_CONFIG && !preg_match( '#^[a-z0-9-_]+$#', CURRENT_CONFIG ) ) {
			throw new HttpException( 'invalid or missing configuration ID', 400 );
		}
	}

	/**
	 * Detects if current handling running request is bound to some _current_
	 * configuration selected by any means (e.g. due to using query parameter).
	 *
	 * @return bool true if there is some actually current configuration.
	 * @throws HttpException
	 */
	public static function hasCurrent() {
		static::detectCurrent();

		return defined( 'CURRENT_CONFIG' ) && CURRENT_CONFIG && preg_match( '#^[a-z0-9-_]+$#', CURRENT_CONFIG );
	}
}
