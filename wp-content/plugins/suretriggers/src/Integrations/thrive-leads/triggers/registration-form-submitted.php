<?php
/**
 * RegistrationFormSubmitted.
 * php version 5.6
 *
 * @category RegistrationFormSubmitted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\ThriveLeads\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'RegistrationFormSubmitted' ) ) :

	/**
	 * RegistrationFormSubmitted
	 *
	 * @category RegistrationFormSubmitted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class RegistrationFormSubmitted {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'ThriveLeads';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'tl_registration_form_submitted';

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
				'label'         => __( 'Registration Form Submitted', 'suretriggers' ),
				'action'        => 'tl_registration_form_submitted',
				'common_action' => 'thrive_register_form_through_wordpress_user',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int   $user_id User ID.
		 * @param array $data Form Data.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id, $data ) {
			if ( ! empty( $data ) ) {
				$data['user_id'] = $user_id;
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $data,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	RegistrationFormSubmitted::get_instance();

endif;
