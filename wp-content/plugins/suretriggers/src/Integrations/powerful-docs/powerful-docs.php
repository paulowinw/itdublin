<?php
/**
 * PowerfulDocs core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\PowerfulDocs;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\PowerfulDocs
 */
class PowerfulDocs extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'PowerfulDocs';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Powerful Docs', 'suretriggers' );
		$this->description = __( 'Easily build documentation website with AJAX based live search functionality and keep track of search term. This plugin provides shortcodes to display category list & live search input box.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/powerfuldocs.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'PFD_Loader' );
	}
}

IntegrationsController::register( PowerfulDocs::class );
