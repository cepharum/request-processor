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
 * Implements several convenience methods and helpers for handling deeply
 * structured records of data.
 *
 * @package Request
 */
class Data {
	/**
	 * @var array wrapped set of data
	 */
	protected $record;

	/**
	 * @param array $record set of data to be wrapped in instance
	 */
	public function __construct( $record = null ) {
		$this->record = is_array( $record ) ? $record : [];
	}

	/**
	 * Descends into currently wrapped data structure using provided sequence
	 * of
	 * segments.
	 *
	 * Invoke the method without any argument to load the whole set of wrapped
	 * data.
	 *
	 * @param string $selector period-separated sequence of segment names with
	 *        each selecting another level of hierarchy to descend into
	 * @param mixed $default value to return if querying data structure hits
	 *        null value
	 * @param boolean $caseInsensitive set true to use lowercase variants of
	 *     either marker's name for looking up related data
	 * @return mixed resulting value or level of hierarchy
	 */
	public function query( $selector = null, $default = null, $caseInsensitive = false ) {
		$iter = $this->record;

		if ( $selector ) {
			foreach ( explode( '.', $selector ) as $segment ) {
				if ( is_array( $iter ) ) {
					if ( $caseInsensitive ) {
						foreach ( $iter as $key => $sub ) {
							if ( !strcasecmp( $key, $segment ) ) {
								$iter = $sub;
								continue 2;
							}
						}
					} else {
						if ( array_key_exists( $segment, $iter ) ) {
							$iter = $iter[$segment];
							continue;
						}
					}
				}

				$iter = null;
				break;
			}
		}

		return is_null( $iter ) ? $default : $iter;
	}


	/**
	 * Reduces deeply structured array into shallow array with original
	 * hierarchy of original elements reflected by key names of resulting
	 * array.
	 *
	 * @param array $result array receiving flattened elements of original
	 *     array
	 * @param array $array array to be reduced
	 * @param string $prefix prefix of keys to use in current run, omit on
	 *     external invocation
	 * @return array array provided in $result
	 */
	public static function reduceArray( &$result, $array, $prefix = '' ) {
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				static::reduceArray( $result, $value, $prefix . $key . '_' );
			} else {
				$result[$prefix . $key] = $value;
			}
		}

		return $result;
	}

	/**
	 * Renders comma-separated version of single record of data.
	 *
	 * @param array $record record of data to be encoded
	 * @param array $keys keys of record's items to be rendered
	 * @return string comma-separated version of provided single record of data
	 */
	public static function getCSVRow( $record, $keys = null ) {
		$reduced = [];

		static::reduceArray( $reduced, $record );

		if ( !$keys ) {
			$keys = array_keys( $reduced );
		}

		return implode( ';', array_map( function ( $key ) use ( $reduced ) {
			return '"' . strtr( $reduced[$key], [
					'"'  => '""',
					"\r" => "",
					"\n" => " ",
				] ) . '"';
		}, $keys ) );
	}

	/**
	 * Encodes multi-record set of data as CSV document.
	 *
	 * @note This method is pretty expensive for iterating over provided data
	 *       three times.
	 *
	 * @param array $records records of data to be encoded
	 * @return string comma-separated version of provided data records
	 */
	public static function getCSV( $records ) {
		// flatten all provided rows of data
		$records = array_map( function ( $row ) {
			$reduced = [];
			static::reduceArray( $reduced, $row );
			return $reduced;
		}, $records );

		// extract union set of keys used in all reduced rows of data
		$keys = [];
		foreach ( $records as $row ) {
			foreach ( $row as $key => $ignore ) {
				$keys[$key] = true;
			}
		}

		$keys = array_keys( $keys );

		// generate multi-row CSV with used keys in first row
		return chr( 239 ) . chr( 187 ) . chr( 191 ) .
		       static::getCSVRow( $keys ) . "\r\n" .
		       implode( "\r\n", array_map( function ( $row ) use ( $keys ) {
			       return static::getCSVRow( $row, $keys );
		       }, $records ) );
	}

	/**
	 * Merges two arrays recursively replacing existing properties in target
	 * array with properties of source matching by name.
	 *
	 * @param array $destination array receiving all data from provided sources
	 *     successively
	 * @param array... $firstSource first of several possible sources to be
	 *        merged into given destination
	 * @return array merged data
	 */
	public static function arrayMerge( &$destination, $firstSource ) {
		$sources = func_get_args();
		array_shift( $sources );

		if ( !is_array( $destination ) ) {
			throw new \InvalidArgumentException( 'invalid collector for merging arrays' );
		}

		foreach ( $sources as $source ) {
			if ( is_array( $source ) ) {
				foreach ( $source as $key => $value ) {
					if ( array_key_exists( $key, $destination ) && is_array( $destination[$key] ) && is_array( $value ) ) {
						static::arrayMerge( $destination[$key], $value );
					} else if ( is_numeric( $key ) ) {
						$destination[] = $value;
					} else {
						$destination[$key] = $value;
					}
				}
			}
		}

		return $destination;
	}

	/**
	 * Tries to interpret provided value as human-readable representation of a
	 * boolean information.
	 *
	 * @param string $value value to be interpreted as boolean
	 * @return bool|string interpreted value or provided value on failed interpretation
	 */
	public static function asBoolean( $value ) {
		if ( preg_match( '/^(?:y(?:es)?|true|on|1)$/i', trim( $value ) ) ) {
			return true;
		}

		if ( preg_match( '/^(?:no?|false|off|0)?$/i', $value ) ) {
			return false;
		}

		return $value;
	}
}
