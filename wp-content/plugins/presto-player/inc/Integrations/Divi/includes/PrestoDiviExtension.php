<?php

class PrestoDiviExtension extends DiviExtension {


	/**
	 * The gettext domain for the extension's translations.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $gettext_domain = 'presto-player';

	/**
	 * The extension's WP Plugin name.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $name = 'presto-player';

	/**
	 * The extension's version
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * PrestoDiviExtensions constructor.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __construct( $name = 'presto-player', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );

		parent::__construct( $name, $args );
	}

	/**
	 * Enqueues minified, production javascript bundles.
	 *
	 * @since 3.1
	 */
	protected function _enqueue_bundles() {
	}

	protected function _enqueue_backend_styles() {
	}

	public function wp_hook_enqueue_scripts() {
	}
}

new PrestoDiviExtension();
