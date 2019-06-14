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


/**
 * Implements very simple engine for template-based rendering of pages.
 *
 * @package Request
 */
class Page {
	private static $sent = false;

	protected static $data = [];

	/**
	 * Detects if page has been rendered and sent to client before.
	 *
	 * @return bool true if page has been rendered and sent before
	 */
	public static function isSent() {
		return self::$sent;
	}

	/**
	 * Fetches path name of template file for named view.
	 *
	 * @param string $templateName name of view requested template is used for
	 * @return string path name of file (might be missing, though)
	 */
	public static function getTemplateFile( $templateName = 'default' ) {
		if ( !preg_match( '/^[a-z_-]+$/i', $templateName ) ) {
			$templateName = 'default';
		}

		$folder = BASEDIR . '/templates/pages/';

		$filename = $folder . $templateName . '.phtml';
		if ( !@file_exists( $filename ) && $templateName === 'default' ) {
			$templateName = 'default.dist';
		}

		return $folder . $templateName . '.phtml';
	}

	/**
	 * Fetches path name of file implementing special code of particular view.
	 *
	 * @param string $viewName name of view to use
	 * @return string path name of file implementing requested view (might be missing, though)
	 */
	public static function getViewFile( $viewName = 'default' ) {
		if ( preg_match( '/^[a-z_\/-]+$/i', $viewName ) ) {
			return BASEDIR . "/templates/views/$viewName.php";
		}

		throw new InvalidArgumentException( 'invalid view name rejected' );
	}

	/**
	 * Appends (more) content to be inserted into rendered page.
	 *
	 * @param string $content code of content to be inserted
	 * @param array $data named variables for replacing marker in given code
	 * @param string $area name of area content is to be inserted in
	 */
	public static function write( $content, $data = null, $area = 'main' ) {
		$content = is_array( $data ) && count( $data ) ? Interpolate::dollars( $content, $data, true ) : $content;

		if ( array_key_exists( $area, static::$data ) ) {
			static::$data[$area] .= $content;
		} else {
			static::$data[$area] = strval( $content );
		}
	}

	/**
	 * Puts arbitrary data in scope of current page.
	 *
	 * @param string $name name of value to put
	 * @param mixed $value value to put
	 */
	public static function put( $name, $value ) {
		static::$data[$name] = $value;
	}

	/**
	 * Drops data selected by name.
	 *
	 * @param string $name name of data to drop
	 */
	public static function drop( $name ) {
		unset( static::$data[$name] );
	}

	/**
	 * Reads previously collected data from internally managed volatile storage.
	 *
	 * @param string $name name of data to read
	 * @returns string|null found data
	 */
	public static function read( $name ) {
		return array_key_exists( $name, static::$data ) ? static::$data[$name] : null;
	}

	/**
	 * Renders output using page template selected by name and filled with data
	 * provided here as well as collected before using Page::write().
	 *
	 * @param string $pageName name of page template to use
	 * @param array $data data to be used on processing template
	 * @param int $statusCode HTTP status code to send on rendering page
	 */
	public static function render( $pageName = 'default', $data = [], $statusCode = null ) {
		if ( !static::isSent() ) {
			if ( $statusCode ) {
				http_response_code( $statusCode );
			}

			if ( is_array( $data ) ) {
				$merged = [];
				$data = Data::arrayMerge( $merged, static::$data, $data );
			} else {
				$data = static::$data;
			}

			$pageFile = static::getTemplateFile( $pageName );
			if ( !@file_exists( $pageFile ) ) {
				$pageFile = static::getTemplateFile( 'default' );
			}

			$snapshot = static::$data;
			foreach ( $data as $key => $value ) {
				static::$data[$key] = $value;
			}

			include( $pageFile );

			static::$data = $snapshot;

			self::$sent = true;
		}
	}

	/**
	 * Generates view selected by name.
	 *
	 * This method is including one out of of several views to generate some
	 * content using class Page. Either view's implementation is assumed to
	 * render the page using Page::render() eventually.
	 *
	 * @param string $viewName name of view to show
	 * @param array $data data extracted and passed to view's implementation
	 * @param int $statusCode HTTP status code to send on rendering page
	 */
	public static function showView( $viewName, $data = [], $statusCode = null ) {
		if ( !static::isSent() ) {
			if ( $statusCode ) {
				http_response_code( $statusCode );
			}

			$snapshot = static::$data;
			foreach ( $data as $key => $value ) {
				static::$data[$key] = $value;
			}

			include( static::getViewFile( $viewName ) );

			static::$data = $snapshot;
		}
	}
}
