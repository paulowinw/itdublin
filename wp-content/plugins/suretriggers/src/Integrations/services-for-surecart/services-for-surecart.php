<?php
/**
 * ServicesForSureCart core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\ServicesForSureCart;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\ServicesForSureCart
 */
class ServicesForSureCart extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'ServicesForSureCart';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Services For SureCart', 'suretriggers' );
		$this->description = __( 'Services For SureCart plugin empowers you to sell services and custom deliverables with SureCart. Enjoy features like status and activity tracking, built-in messaging, and final delivery and approvals, all beautifully integrated directly into your website and customer dashboard.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/servicesforsurecart.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'SureCart' ) && defined( 'SURELYWP_SERVICES' );
	}

}

IntegrationsController::register( ServicesForSureCart::class );
