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

use InvalidArgumentException;
use Locale;
use RuntimeException;


/**
 * Implements very simple support for translations.
 *
 * @package Request
 */
class L10n {
	protected static $_locale = null;
	protected static $_map = null;

	/**
	 * Selects locale used on upcoming translations preferring locale requested
	 * by client by default.
	 *
	 * @param string $locale locale to select explicitly, omit for processing Accept-Language provided in HTTP request
	 * @return string discovered and selected locale
	 */
	public static function selectLocale( $locale = null ) {
		if ( !defined( "BASEDIR" ) ) {
			throw new RuntimeException( 'missing BASEDIR' );
		}

		if ( !$locale ) {
			if ( static::$_locale ) {
				$locale = static::$_locale;
			} else {
				$locale = Locale::acceptFromHttp( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
			}
		}

		static::$_locale = $locale;

		if ( !preg_match( '/^([a-z]{2,3})(?:[-_]|$)/i', trim( $locale ), $match ) ) {
			throw new InvalidArgumentException( 'invalid locale identifier: ' . $locale );
		}

		$desiredFile = BASEDIR . '/l10n/' . $locale . '.php';
		$defaultFile = BASEDIR . '/l10n/en.php';

		static::reset();

		if ( $defaultFile !== $desiredFile && @file_exists( $defaultFile ) ) {
			include( $defaultFile );
		}

		if ( @file_exists( $desiredFile ) ) {
			include( $desiredFile );
		}

		return $locale;
	}

	/**
	 * Drops all previously registered translations.
	 */
	public static function reset() {
		static::$_map = [];
	}

	/**
	 * Registers another set of translations.
	 *
	 * @param array $translations map of lookup keys in translation string or
	 *        into array mapping tests on count into related translation
	 */
	public static function register( $translations ) {
		foreach ( $translations as $key => $localization ) {
			static::$_map[$key] = $localization;
		}
	}

	/**
	 * Fetches translation selected by given lookup key and matching provided
	 * number of items.
	 *
	 * @param string $key lookup key selecting translation
	 * @param int $count number of items to be mentioned in translated message
	 * @return string resulting translation or provided key there is no matching translation
	 */
	public static function translate( $key, $count = 1 ) {
		if ( static::$_map === null ) {
			static::selectLocale();
		}

		if ( array_key_exists( $key, static::$_map ) ) {
			$localization = static::$_map[$key];

			if ( !is_array( $localization ) ) {
				return $localization;
			}

			$fallback = $key;

			foreach ( $localization as $test => $text ) {
				if ( preg_match( '/^(?:(\*)|(<>|[<>=]=?)\s*(-?\d+))$/i', trim( $test ), $match ) ) {
					if( $match[1] ) {
						$fallback = $text;
					} else {
						switch ( $match[2] ) {
							case '<>' : $result = $count != $match[3]; break;
							case '=' :
							case '==' : $result = $count == $match[3]; break;
							case '<' : $result = $count < $match[3]; break;
							case '<=' : $result = $count <= $match[3]; break;
							case '>' : $result = $count > $match[3]; break;
							case '>=' : $result = $count >= $match[3]; break;
							default: $result = false;
						}

						if ( $result ) {
							return $text;
						}
					}
				}
			}

			return $fallback;
		}

		return $key;
	}

	/**
	 * Extracts localized information for current locale from internationalized
	 * input.
	 *
	 * @param array|string $input some optionally internationalized information
	 * @return string|array localized information extracted from internationalized
	 *         input, or input as-is if not internationalized
	 */
	public static function localize( $input ) {
		if ( is_array( $input ) ) {
			if ( array_key_exists( 'any', $input ) || array_key_exists( 'en', $input ) ) {
				if ( preg_match( '/^\s*([a-z]+)(?:[-_;]|$)/i', static::selectLocale(), $match ) ) {
					$locale = $match[1];

					if ( array_key_exists( $locale, $input ) ) {
						return $input[$locale];
					}
				}

				return array_key_exists( 'any', $input ) ? $input['any'] : $input['en'];
			}
		}

		return $input;
	}

	/**
	 * Conveniently combines code for looking up a translation and to
	 * interpolate the resulting translation.
	 *
	 * @param string $key lookup key selecting translation
	 * @param int $count number of items to be mentioned in translated message
	 * @param array $data data to be used in preference on interpolating
	 * @return string translated and interpolated string
	 */
	public static function interpolate( $key, $count = 1, $data = [] ) {
		return Interpolate::dollars( static::translate( $key, $count ), $data );
	}
}
