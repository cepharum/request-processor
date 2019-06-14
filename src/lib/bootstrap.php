<?php

namespace Request;

define( 'BASEDIR', dirname( __DIR__ ) );

session_start();

error_reporting( E_ERROR | E_WARNING | E_PARSE );
ini_set( 'display_errors', 1 );

include_once( __DIR__ . '/../vendor/autoload.php' );

spl_autoload_register( function( $class ) {
	$pathname = __DIR__ . '/' . strtr( $class, [ '\\' => '/' ] ) . '.class.php';
	if ( file_exists( $pathname ) ) {
		include( $pathname );
	}
} );
