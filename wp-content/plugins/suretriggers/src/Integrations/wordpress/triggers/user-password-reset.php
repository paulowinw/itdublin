<?php
/**
 * UserPasswordReset.
 * php version 5.6
 *
 * @category UserPasswordReset
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserPasswordReset' ) ) :


	/**
	 * UserPasswordReset
	 *
	 * @category UserPasswordReset
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserPasswordReset {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WordPress';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'after_password_reset';

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
				'label'         => __( 'User resets their password', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [
					'after_password_reset',
					'woocommerce_customer_reset_password',
				],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 *
		 * @param object $user user object.
		 * @param string $new_password new password.
		 *
		 * @return void
		 */
		public function trigger_listener( $user, $new_password = null ) {

			$context                 = WordPress::get_user_context( $user->ID );
			$context['new_password'] = $new_password;
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);

		}

	}


	UserPasswordReset::get_instance();

endif;



