<?php
/**
 * UpdateSubscriberToNewList.
 * php version 5.6
 *
 * @category UpdateSubscriberToNewList
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
 * UpdateSubscriberToNewList
 *
 * @category UpdateSubscriberToNewList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateSubscriberToNewList extends AutomateAction {

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
	public $action = 'update_subscriber_to_new_list';

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
			'label'    => __( 'Add User to List', 'suretriggers' ),
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
		$list_id    = $selected_options['mailpoet_list'];
		$email      = $selected_options['subscriber_email'];
		$subscriber = [];

		// Bail if not list selected.
		if ( '' === $list_id ) {
			return;
		}

		if ( isset( $email ) && ! empty( $email ) ) {
			$subscriber['email'] = sanitize_email( $email );
		}

		$list_ids = [];
		foreach ( $list_id as $value ) {
			$list_ids[] = $value['value'];
		}

		// Get the MailPoet API.
		$mailpoet = \MailPoet\API\API::MP( 'v1' );

		try {
			// Check if email is already a subscriber.
			$existing_subscriber = $mailpoet->getSubscriber( $subscriber['email'] );

			if ( $existing_subscriber ) {
				// Add existing subscriber to the list.
				$mailpoet->subscribeToLists( $existing_subscriber['id'], $list_ids );
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

UpdateSubscriberToNewList::get_instance();
