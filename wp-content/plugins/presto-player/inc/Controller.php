<?php

namespace PrestoPlayer;

class Controller {

	/**
	 * The settings page handler.
	 *
	 * @var Component[]
	 */
	private $components = array();

	/**
	 * The core plugin API.
	 *
	 * @var Core
	 */
	private $core;

	/**
	 * Creates an instance of the plugin controller.
	 *
	 * @since 2.3.0 Component parameters replaced with factory-cofigured array.
	 *
	 * @param Core        $core       The core API.
	 * @param Component[] $components An array of plugin components.
	 */
	public function __construct( Core $core, array $components ) {
		$this->core       = $core;
		$this->components = $components;
	}

	/**
	 * Starts the plugin for real.
	 *
	 * @return void
	 */
	public function run() {
		// Set plugin singleton.
		Core::set_instance( $this->core );

		foreach ( $this->components as $component ) {
			$component->register();
		}
	}
}
