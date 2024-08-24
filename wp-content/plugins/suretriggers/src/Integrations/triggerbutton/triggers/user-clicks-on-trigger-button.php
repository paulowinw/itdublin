<?php
/**
 * UserClicksOnTriggerButton.
 * php version 5.6
 *
 * @category UserClicksOnTriggerButton
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TriggerButton\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserClicksOnTriggerButton' ) ) :

	/**
	 * UserClicksOnTriggerButton
	 *
	 * @category UserClicksOnTriggerButton
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserClicksOnTriggerButton {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'TriggerButton';

		/**
		 * Action name.
		 *
		 * @var string
		 */
		public $trigger = 'st_trigger_button';

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
		 * Register a action.
		 *
		 * @param array $triggers actions.
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'A User Clicks on Trigger Button', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'st_trigger_button_action',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 5,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param string $st_trigger_id Trigger ID.
		 * @param int    $user_id User id.
		 * @param int    $cookie_duration Cookie Duration.
		 * @param bool   $setcookie Set Cookie.
		 * @param array  $parent_post_data Button Parent Post Data.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $st_trigger_id, $user_id, $cookie_duration, $setcookie, $parent_post_data ) {
			$context           = WordPress::get_user_context( $user_id );
			$all_meta_for_user = get_user_meta( $user_id );

			$context['user_meta']               = $all_meta_for_user;
			$context['st_trigger_button_input'] = "[st_trigger_button id='" . $st_trigger_id . "' button_label='Click here']";
			$context['st_trigger_id']           = $st_trigger_id;

			$before_button_click_data     = '';
			$before_button_click_response = apply_filters( 'st_trigger_button_before_click_hook', $before_button_click_data );

			$after_button_click_data     = '';
			$after_button_click_response = apply_filters( 'st_trigger_button_after_click_hook', $after_button_click_data );

			$context['before_click_response'] = $before_button_click_response;
			$context['after_click_response']  = $after_button_click_response;

			if ( ! empty( $parent_post_data ) ) {
				$context = array_merge( $context, $parent_post_data );
			}
			
			$automation = AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => ap_get_current_user_id(),
					'context'    => $context,
				]
			);

			if ( $automation && 'true' == $setcookie ) {
				do_action( 'st_trigger_button_set_cookie', $st_trigger_id, $user_id, $cookie_duration );
			}
		}
	}

	UserClicksOnTriggerButton::get_instance();

endif;




