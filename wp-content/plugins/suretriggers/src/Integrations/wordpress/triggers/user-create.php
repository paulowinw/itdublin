<?php
/**
 * UserCreate.
 * php version 5.6
 *
 * @category UserCreate
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

if ( ! class_exists( 'UserCreate' ) ) :


	/**
	 * UserCreate
	 *
	 * @category UserCreate
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserCreate {


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
		public $trigger = 'user_register';

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
				'label'         => __( 'User is created', 'suretriggers' ),
				'action'        => 'user_register',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 *
		 * @param int $user_id created user id.
		 *
		 * @return void
		 */
		public function trigger_listener( $user_id ) {

			$context = WordPress::get_user_context( $user_id );
			$user    = get_userdata( $user_id );

			if ( $user ) {
				$display_name = $user->display_name;
				$display_name = explode( ' ', $display_name );
				if ( ! empty( $display_name ) ) {
					if ( '' != $display_name[0] ) {
						$context['user_firstname'] = $display_name[0];
					}
					if ( array_key_exists( 1, $display_name ) && '' != $display_name[1] ) {
						$context['user_lastname'] = $display_name[1];
					}
				}
			}
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);

		}

	}


	UserCreate::get_instance();

endif;




