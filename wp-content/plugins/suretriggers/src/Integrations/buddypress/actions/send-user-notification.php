<?php
/**
 * SendUserNotification.
 * php version 5.6
 *
 * @category SendUserNotification
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * SendUserNotification
 *
 * @category SendUserNotification
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendUserNotification extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'send_user_notification';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send the user a notification', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$sender               = $selected_options['wp_user_email'];
		$receiver             = $selected_options['to_user_email'];
		$notification_content = $selected_options['notification_content'];
		$notification_link    = $selected_options['notification_link'];

		if ( empty( $sender ) || ! is_email( $sender ) ) {
			throw new Exception( 'Invalid sender email.' );
		}

		if ( empty( $receiver ) || ! is_email( $receiver ) ) {
			throw new Exception( 'Invalid receiver email.' );
		}

		$sender_user   = get_user_by( 'email', $sender );
		$receiver_user = get_user_by( 'email', $receiver );
		
		// Attempt to send notification.
		if ( function_exists( 'bp_notifications_add_notification' ) ) {
			if ( function_exists( 'bp_core_current_time' ) ) {
				if ( $sender_user && $receiver_user ) {
					$sender_id       = $sender_user->ID;
					$receiver_id     = $receiver_user->ID;
					$notification_id = bp_notifications_add_notification(
						[
							'user_id'           => $receiver_id,
							'secondary_item_id' => $sender_id,
							'component_name'    => 'suretriggers',
							'component_action'  => 'suretriggers_bp_notification',
							'date_notified'     => bp_core_current_time(),
							'is_new'            => 1,
							'allow_duplicate'   => true,
						]
					);

					if ( is_wp_error( $notification_id ) ) {
						throw new Exception( $notification_id->get_error_message() );
					} else {

						// Add the link.
						if ( ! empty( $notification_link ) ) {
							$notification_content = '<a href="' . esc_url( $notification_link ) . '" title="' . esc_attr( wp_strip_all_tags( $notification_content ) ) . '">' . ( $notification_content ) . '</a>';
						}

						// Adding meta for notification display on front-end.
						if ( function_exists( 'bp_notifications_update_meta' ) ) {
							bp_notifications_update_meta( $notification_id, 'st_notification_content', $notification_content );
							bp_notifications_update_meta( $notification_id, 'st_notification_link', $notification_link );
						}
						$context['sender'] = WordPress::get_user_context( $user_id );
						if ( function_exists( 'bp_notifications_get_notification' ) && function_exists( 'bp_notifications_get_meta' ) ) {
							$notification            = bp_notifications_get_notification( $notification_id );
							$notification_meta       = bp_notifications_get_meta( $notification_id );
							$context['notification'] = array_merge( get_object_vars( $notification ), $notification_meta );
						}
						return $context;
					}
				}
			}
		} else {
			throw new Exception( 'BuddyPress notification module is not active.' );
		}
	}
}

SendUserNotification::get_instance();
