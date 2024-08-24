<?php
/**
 * Loader for namespace.
 *
 * @since 1.0.0
 * @package SureTrigger
 */

spl_autoload_register(
	function ( $class ) {
		// The namespace prefix.
		$prefix = 'SureTriggers\\';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			// No, move to the next registered autoloader.
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );

		// Replace the namespace prefix with the base directory, replace namespace.
		// separators with directory separators in the relative class name, append.
		// with .php.
		$file = __DIR__ . '/src/' . str_replace( '\\', '/', $relative_class ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

