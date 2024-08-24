<?php
/**
 * SendEmail.
 * php version 5.6
 *
 * @category SendEmail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * SendEmail
 *
 * @category SendEmail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SendEmail extends AutomateAction {

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
	public $action = 'voxel_send_email';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Send Email', 'suretriggers' ),
			'action'   => 'voxel_send_email',
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
		$message   = $selected_options['message'];
		$subject   = $selected_options['subject'];
		$recipient = $selected_options['wp_user_email'];

		if ( ! class_exists( 'Voxel\Queues\Async_Email' ) ) {
			return false;
		}

		if ( is_email( $recipient ) ) {
			$args  = [
				'emails' => [
					[
						'recipient' => $recipient,
						'subject'   => $subject,
						'message'   => $message,
						'headers'   => [
							'Content-type: text/html;',
						],
					],
				],
			];
			$email = \Voxel\Queues\Async_Email::instance()->data( $args )->dispatch();
			if ( ! $email ) {
				throw new Exception( 'Email not sent' );
			} else {
				return [
					'success' => true,
					'message' => esc_attr__( 'Email sent successfully', 'suretriggers' ),
				];
			}
		} else {
			throw new Exception( 'Please enter valid email address.' );
		}
	}

}

SendEmail::get_instance();
