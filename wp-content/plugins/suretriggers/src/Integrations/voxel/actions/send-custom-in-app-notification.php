<?php
/**
 * SendCustomInAppNotification.
 * php version 5.6
 *
 * @category SendCustomInAppNotification
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * SendCustomInAppNotification
 *
 * @category SendCustomInAppNotification
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendCustomInAppNotification extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_send_custom_in_app_notification';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send Custom In App Notification', 'suretriggers' ),
			'action'   => 'voxel_send_custom_in_app_notification',
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
	 * 
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		// Get the user ID.
		$user_email = $selected_options['user_email'];

		// If user ID is email, then get the user by email.
		if ( is_email( $user_email ) ) {
			$user       = get_user_by( 'email', $user_email );
			$user_email = $user ? $user->ID : 1;
		}

		// Get the notification message.
		$message = $selected_options['message'];

		// Get post id to link.
		$post_id = isset( $selected_options['post_id'] ) ? (int) $selected_options['post_id'] : 0;

		if ( ! class_exists( 'Voxel\Post' ) || ! class_exists( 'Voxel\User' ) || ! class_exists( 'Voxel\Notification' ) 
		|| ! function_exists( 'Voxel\get' ) || ! function_exists( 'Voxel\set' ) ) {
			return false;
		}

		// Get the recipient.
		$recipient = \Voxel\User::get( $user_email );

		// Get the post to link.
		$post = \Voxel\Post::force_get( $post_id );

		$post_type    = $post->post_type;
		$notification = \Voxel\Notification::create(
			[
				'user_id' => $recipient->get_id(),
				'subject' => 'SureTriggers: Notification created',
				'type'    => 'post-types/' . $post_type->wp_post_type->name . '/post:updated',
				'details' => [
					'post_id'     => $post_id,
					'destination' => 'post_author',
				],
			]
		);

		// Update the notification count for the user.
		$recipient->update_notification_count();

		// Get the voxel events.
		$events = (array) \Voxel\get( 'events', [] );

		$defaults = [
			'post_author' => [
				'label'     => 'Notify user',
				'recipient' => function( $event ) {
					return $event->author;
				},
				'inapp'     => [
					'enabled'       => false,
					'subject'       => $message,
					'details'       => function( $event ) {
						return [
							'post_id' => $event->post->get_id(),
						];
					},
					'apply_details' => function( $event, $details ) {
						$event->prepare( $details['post_id'] );
					},
					'links_to'      => function( $event ) {
						return $event->post->get_link(); },
					'image_id'      => function( $event ) {
						return $event->post->get_logo_id(); },
				],
				'email'     => [
					'enabled' => false,
					'subject' => 'Your post has been updated successfully.',
					'message' => [
						'html' => [
							'subject' => 'Your post has been updated successfully.',
							'body'    => 'Your post <strong>@post(:title)</strong> has been updated successfully.
							<a href="@post(:url)">Open</a>
							HTML',
						],
					],
				],
			],
		];

		// Add the event to the events array.
		$events[ 'post-types/' . $post_type->wp_post_type->name . '/post:updated' ] = [
			'notifications' => $defaults,
		];

		// Set the events.
		\Voxel\set( 'events', $events, false );

		return [
			'success'      => true,
			'message'      => esc_attr__( 'Custom In-app notification sent successfully', 'suretriggers' ),
			'user_id'      => $user_email,
			'notification' => $message,
		];
	}

}

SendCustomInAppNotification::get_instance();
