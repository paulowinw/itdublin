<?php
/**
 * AddSubscriberToList.
 * php version 5.6
 *
 * @category AddSubscriberToList
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
 * AddSubscriberToList
 *
 * @category AddSubscriberToList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddSubscriberToList extends AutomateAction {



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
	public $action = 'add_subscriber_to_list';

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
			'label'    => __( 'Add New Subscriber', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		// Shorthand.
		$subscriber                    = [];
		$list_id                       = $selected_options['mailpoet_list'];
		$first_name                    = $selected_options['subscriber_first_name'];
		$last_name                     = $selected_options['subscriber_last_name'];
		$email                         = $selected_options['subscriber_email'];
		$subscriber_status             = $selected_options['subscriber_status'];
		$subscriber_confirmation_email = $selected_options['subscriber_confirmation_email'];

		// Bail if not list selected.
		if ( '' === $list_id ) {
			return;
		}

		if ( isset( $email ) && ! empty( $email ) ) {
			$subscriber['email'] = sanitize_email( $email );
		}

		if ( isset( $first_name ) && ! empty( $first_name ) ) {
			$subscriber['first_name'] = $first_name;
		}

		if ( isset( $last_name ) && ! empty( $last_name ) ) {
			$subscriber['last_name'] = $last_name;
		}

		if ( isset( $subscriber_status ) && ! empty( $subscriber_status ) ) {
			$subscriber['status'] = $subscriber_status;
		}

		$disable_confirmation_email = true;
		if ( isset( $subscriber_confirmation_email ) ) {
			$disable_confirmation_email = $subscriber_confirmation_email;
			if ( '0' === $disable_confirmation_email ) {
				$disable_confirmation_email = false;
			} else {
				$disable_confirmation_email = true;
			}
		}

		$options = [
			'send_confirmation_email' => $disable_confirmation_email,
		];

		$list_ids = [];
		foreach ( $list_id as $value ) {
			$list_ids[] = $value['value'];
		}

		// Get the MailPoet API.
		$mailpoet = \MailPoet\API\API::MP( 'v1' );

		try {
			// Try to get the subscriber.
			$existing_subscriber = $mailpoet->getSubscriber( $subscriber['email'] );
		} catch ( \MailPoet\API\MP\v1\APIException $e ) {
			// Handle case where subscriber does not exist (continue to add a new subscriber).
			$existing_subscriber = null;
		}

		if ( $existing_subscriber ) {
			// Subscriber exists.
			$mailpoet->subscribeToLists( $existing_subscriber['id'], $list_ids, $options );
			$subscriber_id = $existing_subscriber['id'];

			if ( false === $disable_confirmation_email ) {
				$table_name = $wpdb->prefix . 'mailpoet_subscribers';
				$wpdb->update( $table_name, [ 'status' => $subscriber['status'] ], [ 'id' => $subscriber_id ] );
			}       
		} else {
			// Subscriber does not exist, add new subscriber.
			$new_subscriber = $mailpoet->addSubscriber( $subscriber, $list_ids, $options );
			$subscriber_id  = $new_subscriber['id'];

			if ( false === $disable_confirmation_email ) {
				$table_name = $wpdb->prefix . 'mailpoet_subscribers';
				$wpdb->update( $table_name, [ 'status' => $subscriber['status'] ], [ 'id' => $subscriber_id ] );
			}       
		}
		$context = [ 'user_email' => $subscriber['email'] ];

		return $context;
		
	}

}

AddSubscriberToList::get_instance();
