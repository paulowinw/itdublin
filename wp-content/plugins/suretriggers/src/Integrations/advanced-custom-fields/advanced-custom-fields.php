<?php
/**
 * AdvancedCustomFields core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\AdvancedCustomFields;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\AdvancedCustomFields
 */
class AdvancedCustomFields extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'AdvancedCustomFields';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'AdvancedCustomFields', 'suretriggers' );
		$this->description = __( 'Advanced Custom Fields (ACF) helps you easily customize WordPress with powerful, professional and intuitive fields.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/AdvancedCustomFields.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'ACF' );
	}

}

IntegrationsController::register( AdvancedCustomFields::class );
