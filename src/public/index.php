<?php

namespace Request;

use PDOException;
use Throwable;

include( '../lib/bootstrap.php' );

try {
	$actions = BASEDIR . '/actions/';

	if ( !count( $_GET ) ) {
		include( $actions . 'overview.php' );
	} else {
		foreach ( $_GET as $action => $value ) {
			if ( preg_match( '/^[a-z_]+$/i', $action ) ) {
				$actionFile = $actions . $action . '.php';

				if ( @file_exists( $actionFile ) ) {
					include( $actionFile );

					if ( Page::isSent() ) {
						break;
					}
				}
			}
		}

		if ( !Page::isSent() ) {
			Page::showView( 'form' );
		}
	}
}
catch ( HttpException $error ) {
	Page::render( 'error', [
		'error' => $error,
	], $error->getCode() ?: 500 );
}
catch ( PDOException $exception ) {
	Page::render( 'error', [
		'error' => $exception,
	], 500 );
}
catch ( Throwable $error ) {
	Page::render( 'crash', [
		'error' => $error,
	], 500 );
}
