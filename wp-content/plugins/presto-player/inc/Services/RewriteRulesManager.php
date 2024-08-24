<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Plugin;

/**
 * The RewriteRulesManager service.
 */
class RewriteRulesManager {


	/**
	 * The option name for the stored version.
	 *
	 * @var string
	 */
	protected $option_name = 'presto_flush_rewrite_rules';

	/**
	 * Bootstraps the service.
	 *
	 * @return void
	 */
	public function bootstrap() {
		add_action( 'admin_init', array( $this, 'flushRewriteRulesOnVersionChange' ) );
	}

	/**
	 * Retrieves the stored version from the WordPress options.
	 *
	 * @return string The stored version.
	 */
	public function getStoredVersion() {
		return get_option( $this->option_name, '0.0.0' );
	}

	/**
	 * Retrieves the current plugin version.
	 *
	 * @return string The current plugin version.
	 */
	public function getCurrentPluginVersion() {
		return Plugin::version();
	}

	/**
	 * Flushes the rewrite rules if the plugin version has changed.
	 *
	 * @return bool|void Returns false if the versions are the same, otherwise void.
	 */
	public function flushRewriteRulesOnVersionChange() {
		$current_version = $this->getCurrentPluginVersion();
		$stored_version  = $this->getStoredVersion();

		if ( ! $this->isVersionDifferent( $current_version, $stored_version ) ) {
			return false;
		}

		return $this->flushRewriteRulesAndUpdateVersion( $current_version );
	}

	/**
	 * Checks if the current plugin version is different from the stored version.
	 *
	 * @param string $current_version The current plugin version.
	 * @param string $stored_version The stored plugin version.
	 *
	 * @return bool True if the versions are different, false otherwise.
	 */
	public function isVersionDifferent( $current_version, $stored_version ) {
		return version_compare( $current_version, $stored_version, '!=' );
	}

	/**
	 * Flushes the rewrite rules and updates the stored version.
	 *
	 * @param string $new_version The new plugin version.
	 *
	 * @return void
	 */
	public function flushRewriteRulesAndUpdateVersion( $new_version ) {
		flush_rewrite_rules();
		return update_option( $this->option_name, $new_version, false );
	}
}
