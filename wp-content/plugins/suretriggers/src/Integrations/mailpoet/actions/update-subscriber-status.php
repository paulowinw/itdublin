<?php
/**
 * UpdateSubscriberStatus.
 * php version 5.6
 *
 * @category UpdateSubscriberStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MailPoet\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * UpdateSubscriberStatus
 *
 * @category UpdateSubscriberStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateSubscriberStatus extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MailPoet';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'update_subscriber_status';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Update Subscriber Status', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id          user_id.
	 * @param int   $automation_id    automation_id.
	 * @param array $fields           fields.
	 * @param array $selected_options selectedOptions.
	 *
	 * @return array
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		// Shorthand.
		$email             = $selected_options['subscriber_email'];
		$subscriber_status = $selected_options['subscriber_status'];
		$subscriber        = [];

		// Bail if not list selected.
		if ( '' === $email ) {
			return;
		}

		if ( isset( $email ) && ! empty( $email ) ) {
			$subscriber['email'] = sanitize_email( $email );
		}

		if ( isset( $subscriber_status ) && ! empty( $subscriber_status ) ) {
			$subscriber['status'] = $subscriber_status;
		}

		// Get the MailPoet API.
		$mailpoet = \MailPoet\API\API::MP( 'v1' );

		try {
			// Check if email is already a subscriber.
			$existing_subscriber = $mailpoet->getSubscriber( $subscriber['email'] );
			$table_name          = $wpdb->prefix . 'mailpoet_subscribers';

			if ( $existing_subscriber ) {
				// Update existing subscriber status.
				$get_subscriber = $mailpoet->getSubscriber( $subscriber['email'] );
				if ( ! empty( $get_subscriber ) ) {
					$subscriber_id = $get_subscriber['id'];
					$wpdb->update( $table_name, [ 'status' => $subscriber['status'] ], [ 'id' => $subscriber_id ] );
				} else {
					// Throw error if subscriber not found.
					throw new Exception( 'Subscriber not found for entered email.' );
				}
			} else {
				// Throw error if adds new email.
				throw new Exception( 'Add existing subscriber email.' );
			}

			$context = $subscriber;

			return $context;
		} catch ( \MailPoet\API\MP\v1\APIException $e ) {
			throw new Exception( $e->getMessage() );
		}
	}
}

UpdateSubscriberStatus::get_instance();
