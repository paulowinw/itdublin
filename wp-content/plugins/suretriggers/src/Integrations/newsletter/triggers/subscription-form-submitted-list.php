<?php
/**
 * SubscriptionFormSubmittedList.
 * php version 5.6
 *
 * @category SubscriptionFormSubmittedList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Newsletter\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'SubscriptionFormSubmittedList' ) ) :

	/**
	 * SubscriptionFormSubmittedList
	 *
	 * @category SubscriptionFormSubmittedList
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class SubscriptionFormSubmittedList {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Newsletter';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'subscription_form_submitted_list';

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
				'label'         => __( 'Subscription Form Submitted with Specific List', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'newsletter_user_post_subscribe',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 20,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $user User.
		 * @return void
		 */
		public function trigger_listener( $user ) {

			global $wpdb;

			if ( property_exists( $user, 'id' ) ) {
				$user_id = $user->id;

				$log = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . "newsletter_user_logs WHERE user_id = %d AND source = 'subscribe'", $user_id ) );

				if ( null === $log ) {
					return;
				}

				if ( ! isset( $log->data ) && null !== $log->data ) {
					return;
				}
				if ( null === $log->data ) {
					return;
				}

				$lists_arr = get_option( 'newsletter_lists' );
				$lists     = json_decode( $log->data, true );
				foreach ( (array) $lists as $list_id => $status ) {
					if ( '1' !== $status ) {
						continue;
					}
					if ( property_exists( $user, 'email' ) ) {
						$context['email'] = $user->email;
					}
					$context['list_id'] = $list_id;
					if ( is_array( $lists_arr ) ) {
						if ( isset( $lists_arr[ $list_id ] ) ) {
							$list_name            = $lists_arr[ $list_id ];
							$context['list_name'] = $list_name;
						}
					}
				}
			}

			if ( ! empty( $context ) ) {
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
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
	SubscriptionFormSubmittedList::get_instance();

endif;
