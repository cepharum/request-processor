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
use PDOException;
use PDOStatement;

/**
 * Implements convenient wrapper for PDO to access database.
 *
 * @package Request
 */
class Database {
	/**
	 * @var PDO|null
	 */
	protected static $connection = null;

	/**
	 * Connects with database or provides previously established connection.
	 *
	 * @return PDO
	 * @throws HttpException
	 */
	public static function get() {
		if ( !static::$connection ) {
			$setup = Setup::get( 'database' );

			// qualify configuration options
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			];

			if ( is_array( @$setup['options'] ) ) {
				if ( @$setup['options']['init'] ) {
					$options[PDO::MYSQL_ATTR_INIT_COMMAND] = $setup['options']['init'];
				}
			}

			// connect with database
			$db = static::$connection = new PDO( $setup['dsn'], $setup['username'], $setup['password'], $options );
			if ( !$db ) {
				throw new HttpException( 'failed connecting with database', 500 );
			}

			// always try initializing database
			$sql = file_get_contents( BASEDIR . '/setup/db/init.sql' );
			$sql = Interpolate::dollars( $sql, [
				'prefix' => static::tableName( '' ),
			] );

			if ( $db->exec( $sql ) === false ) {
				throw new HttpException( 'failed initializing database', 500 );
			}
		}

		return static::$connection;
	}

	/**
	 * Qualifies provided name of a table to obey any configured prefix.
	 *
	 * @param string $name name of table
	 * @return string qualified name of table
	 * @throws HttpException
	 */
	public static function tableName( $name ) {
		return preg_replace( '/[^a-z0-9_.-]/i', '', Setup::get( 'database.prefix', '' ) . $name );
	}

	/**
	 * Conveniently combines preparation and execution of an SQL statement
	 * returning resulting statement descriptor e.g. for fetching result row
	 * by row.
	 *
	 * @param string $sql SQL statement to be queried
	 * @param array $parameters optional set of input parameters to be bound
	 * @return PDOStatement resulting statement
	 * @throws HttpException
	 * @throws PDOException
	 */
	public static function query( $sql, $parameters = [] ) {
		$db   = static::get();
		$stmt = $db->prepare( $sql );
		$stmt->execute( $parameters );

		return $stmt;
	}

	/**
	 * Conveniently combines preparation and execution of single statement
	 * returning all resulting rows.
	 *
	 * @param string $sql SQL statement to be queried
	 * @param array $parameters optional set of input parameters to be bound
	 * @param int $limit maximum number of matching rows to return
	 * @return array rows resulting from provided query
	 * @throws PDOException
	 * @throws HttpException
	 */
	public static function all( $sql, $parameters = [], $limit = PHP_INT_MAX ) {
		$stmt = static::query( $sql, $parameters );

		$result = [];
		while ( count( $result ) < $limit && ( ( $entry = $stmt->fetch( PDO::FETCH_ASSOC ) ) === false ) ) {
			$result[] = $entry;
		}

		$stmt->closeCursor();

		return $result;
	}

	/**
	 * Conveniently combines preparation and execution of single statement
	 * returning first resulting row, only.
	 *
	 * @param string $sql SQL statement to be queried
	 * @param array $parameters optional set of input parameters to be bound
	 * @return array|null first row resulting from provided query
	 * @throws PDOException
	 * @throws HttpException
	 */
	public static function row( $sql, $parameters = [] ) {
		$stmt = static::query( $sql, $parameters );
		$entry = $stmt->fetch( PDO::FETCH_ASSOC );
		$stmt->closeCursor();

		return $entry;
	}

	/**
	 * Conveniently combines preparation and execution of single statement w/o
	 * returning any result.
	 *
	 * @param string $sql SQL statement to be queried
	 * @param array $parameters optional set of input parameters to be bound
	 * @throws PDOException
	 * @throws HttpException
	 */
	public static function exec( $sql, $parameters = [] ) {
		$stmt = static::query( $sql, $parameters );
		$stmt->closeCursor();
	}
}
