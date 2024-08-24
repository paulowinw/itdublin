<?php
/**
 * LicenseKeyStatusChangedToInactive.
 * php version 5.6
 *
 * @category LicenseKeyStatusChangedToInactive
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\EDD\Triggers;

use EDD_Payment;
use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\EDD\EDD;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'LicenseKeyStatusChangedToInactive' ) ) :

	/**
	 * NewLicenseKey
	 *
	 * @category NewLicenseKey
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class LicenseKeyStatusChangedToInactive {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'EDD';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'edd_license_key_status_changed_to_inactive';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'License Key Status Changed To Active', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'edd_sl_post_set_status',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int    $license_id License ID.
		 * @param string $status Status.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $license_id, $status ) {
			if ( 'inactive' !== $status ) {
				return;
			}
			$context = EDD::edd_get_license_data( $license_id );    
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	LicenseKeyStatusChangedToInactive::get_instance();

endif;
