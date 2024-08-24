<?php
/**
 * Newsletter core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Newsletter;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\Newsletter
 */
class Newsletter extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Newsletter';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Newsletter', 'suretriggers' );
		$this->description = __( 'Newsletter is a powerful yet simple email creation tool that helps you get in touch with your subscribers and engage them with your own content.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/newsletter.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		if ( defined( 'NEWSLETTER_VERSION' ) ) {
			return true;
		} else {
			return false;
		}
	}
}

IntegrationsController::register( Newsletter::class );
