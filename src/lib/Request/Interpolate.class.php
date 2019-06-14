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
 * Implements very simple support for interpolating strings.
 *
 * @package Request
 */
class Interpolate {
	const PATTERN_BRACES  = '/{\{(.+?)}}/';
	const PATTERN_DOLLARS = '/\$\$(.+?)\$\$/';

	/**
	 * Caches instance used on static call for interpolating.
	 *
	 * @var Interpolate
	 */
	protected static $cache = null;

	/**
	 * Exposes configuration to use on interpolating.
	 *
	 * @var Config
	 */
	protected $config;


	/**
	 * Binds interpolation processor with provided configuration.
	 *
	 * @param Config|null $config configuration to use, omit for current
	 *     configuration
	 * @throws HttpException on missing current configuration
	 */
	public function __construct( Config $config = null ) {
		try {
			$this->config = $config ?: Config::current();
		} catch ( HttpException $exception ) {
			if ( $config ) {
				throw new $exception;
			}

			$config = Config::load( 'defaults' );
		}
	}

	/**
	 * Searches provided text for named markers wrapped in double pairs of
	 * curly braces and replaces them with elements from provided array,
	 * current configuration or current setup matching by name or query.
	 *
	 * @param string $text some text to be processed
	 * @param array $data named items used to replace markers matching by name
	 * @param boolean $caseInsensitive set true to look up any marker's name
	 *     case-insensitively
	 * @return string interpolated string
	 * @throws HttpException
	 * @example
	 *
	 * - $text is `'some text with a {{ pattern.name }}'`.
	 * - $data is array with element named `pattern.name` whose value is
	 *     `'value'`.
	 * - The interpolation results in string `'some text with a value'`.
	 *
	 */
	public static function braces( $text, $data = [], $caseInsensitive = false ) {
		if ( is_null( static::$cache ) ) {
			// temporarily add dummy due to probable issues with re-entrant
			// calls on fetching current configuration below
			static::$cache = new static( new Config() );

			try {
				$config = Config::current();
			} catch ( HttpException $exception ) {
				$config = Config::load( 'defaults' );
			}

			static::$cache = new static( $config );
		}

		return static::$cache->process( self::PATTERN_BRACES, $text, $data, $caseInsensitive );
	}

	/**
	 * Searches provided text for named markers wrapped in double pairs of
	 * dollar signs and replaces them with elements from provided array,
	 * current
	 * configuration or current setup matching by name or query.
	 *
	 * @param string $text some text to be processed
	 * @param array $data named items used to replace markers matching by name
	 * @param boolean $caseInsensitive set true to look up any marker's name
	 *     case-insensitively
	 * @return string interpolated string
	 * @throws HttpException
	 * @example
	 *
	 * - $text is `'some text with a $$pattern.name$$'`.
	 * - $data is array with element named `pattern.name` whose value is
	 *     `'value'`.
	 * - The interpolation results in string `'some text with a value'`.
	 *
	 */
	public static function dollars( $text, $data = [], $caseInsensitive = false ) {
		if ( is_null( static::$cache ) ) {
			// temporarily add dummy due to probable issues with re-entrant
			// calls on fetching current configuration below
			static::$cache = new static( new Config() );

			try {
				$config = Config::current();
			} catch ( HttpException $exception ) {
				$config = Config::load( 'defaults' );
			}

			static::$cache = new static( $config );
		}

		return static::$cache->process( self::PATTERN_DOLLARS, $text, $data, $caseInsensitive );
	}

	/**
	 * Searches provided text for named markers wrapped in double pairs of
	 * curly braces and replaces them with elements from provided array,
	 * bound configuration or current setup matching by name or query.
	 *
	 * @param string $text some text to be processed
	 * @param array $data named items used to replace markers matching by name
	 * @param boolean $caseInsensitive set true to look up any marker's name
	 *     case-insensitively
	 * @return string interpolated string
	 * @example
	 *
	 * - $text is `'some text with a {{ pattern.name }}'`.
	 * - $data is array with element named `pattern.name` whose value is
	 *     `'value'`.
	 * - The interpolation results in string `'some text with a value'`.
	 *
	 */
	public function interpolateBraces( $text, $data = [], $caseInsensitive = false ) {
		return $this->process( self::PATTERN_BRACES, $text, $data, $caseInsensitive );
	}

	/**
	 * Searches provided text for named markers wrapped in double pairs of
	 * dollar signs and replaces them with elements from provided array,
	 * bound configuration or current setup matching by name or query.
	 *
	 * @param string $text some text to be processed
	 * @param array $data named items used to replace markers matching by name
	 * @param boolean $caseInsensitive set true to look up any marker's name
	 *     case-insensitively
	 * @return string interpolated string
	 * @example
	 *
	 * - $text is `'some text with a $$pattern.name$$'`.
	 * - $data is array with element named `pattern.name` whose value is
	 *     `'value'`.
	 * - The interpolation results in string `'some text with a value'`.
	 *
	 */
	public function interpolateDollars( $text, $data = [], $caseInsensitive = false ) {
		return $this->process( self::PATTERN_DOLLARS, $text, $data, $caseInsensitive );
	}

	/**
	 * Searches provided text for named markers using some regular expression
	 * and replaces every match with element from provided array, from current
	 * configuration or from current setup matching by name or query.
	 *
	 * @param string $pattern regular expression to use for matching
	 * @param string $text some text to be processed
	 * @param array $data named items used to replace markers matching by name
	 * @param boolean $caseInsensitive set true to look up any marker's name
	 *     case-insensitively
	 * @return string interpolated string
	 */
	protected function process( $pattern, $text, $data = [], $caseInsensitive = false ) {
		return preg_replace_callback( $pattern, function ( $match ) use ( $data, $caseInsensitive ) {
			$name = trim( $match[1] );

			// detect side-band instructions found in marker
			if ( preg_match( '/^(?:!(!?)(\$|\{})?)?([^|]+)\|\|(?:!(!?)(\$|\{})?\s*)?(.*)$/', $name, $match ) ) {
				$codes     = $match[1] !== '';
				$repeat    = trim( $match[2] );
				$name      = trim( $match[3] );
				$altCodes  = $match[4] !== '';
				$altRepeat = trim( $match[5] );
				$default   = $match[6];
			} else {
				$repeat  = $codes = $altRepeat = $altCodes = false;
				$default = '';
			}


			// sequentially try several sources to fetch value for replacing
			// found marker with
			$text = null;

			for ( $i = 0; is_null( $text ) && $i < 4; $i++ ) {
				switch ( $i ) {
					case 0 :
						// look up selected name in provided set of data
						if ( $caseInsensitive ) {
							foreach ( $data as $key => $value ) {
								if ( !strcasecmp( $key, $name ) ) {
									$text = $value;
									break;
								}
							}
						} else if ( array_key_exists( $name, $data ) ) {
							$text = $data[$name];
						}
						break;

					case 1 :
						// try replacing with matching value retrieved from current configuration
						try {
							$config = $this->config->query( $name, null, $caseInsensitive );
							if ( $config !== null ) {
								$text = strval( $config );
							}
						} catch ( HttpException $exception ) {}
						break;

					case 2 :
						// try replacing with matching value retrieved from setup
						try {
							$setup = Setup::get( $name, null, $caseInsensitive );
							if ( $setup !== null ) {
								$text = strval( $setup );
							}
						} catch ( HttpException $exception ) {}
						break;

					case 3 :
						// use any implicitly defined fallback
						$text   = $default;
						$codes  = $altCodes;
						$repeat = $altRepeat;
						break;
				}
			}

			if ( is_null( $text ) ) {
				// no source yielded any value for replacing marker with
				return '';
			}


			// must be interpolated itself?
			if ( $repeat !== '' ) {
				$pattern = $repeat === '$' ? self::PATTERN_DOLLARS : self::PATTERN_BRACES;
				$text    = $this->process( $pattern, $text, $data, $caseInsensitive );
			}

			// shall we replace C-style quotes with related characters?
			return $codes ? stripcslashes( $text ) : $text;
		}, $text );
	}
}
