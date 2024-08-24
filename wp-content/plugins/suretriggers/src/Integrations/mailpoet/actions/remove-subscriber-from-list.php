<?php
/**
 * RemoveSubscriberFromList.
 * php version 5.6
 *
 * @category RemoveSubscriberFromList
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
use MailPoet\Entities\SubscriberEntity;

/**
 * RemoveSubscriberFromList
 *
 * @category RemoveSubscriberFromList
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveSubscriberFromList extends AutomateAction {



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
	public $action = 'remove_subscriber_from_list';

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
			'label'    => __( 'Remove User from List', 'suretriggers' ),
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

		// Shorthand.
		$list_id              = $selected_options['mailpoet_list'];
		$mailpoet_subscribers = $selected_options['mailpoet_subscribers'];
		$subscriber_id        = '';

		// Bail if not list selected.
		if ( '' === $list_id ) {
			return;
		}

		if ( isset( $mailpoet_subscribers ) && ! empty( $mailpoet_subscribers ) ) {
			$subscriber_id = $mailpoet_subscribers;
		}

		try {

			$list_ids = [];

			$list_id       = $list_id;
			$subscriber_id = $subscriber_id;
			$mailpoet      = \MailPoet\API\API::MP( 'v1' );
			$subscriber    = $mailpoet->getSubscriber( $subscriber_id );
			$subscriptions = $subscriber['subscriptions'];

			if ( ! empty( $subscriptions ) && 'all' === $list_id ) {
				foreach ( $subscriptions as $subscription ) {
					$list_ids[] = $subscription['segment_id'];
				}
			} else {
				foreach ( $list_id as $value ) {
					$list_ids[] = $value['value'];
				}
			}

			$mailpoet->unsubscribeFromLists( $subscriber_id, $list_ids );

			$context['response'] = 'Subscriber Removed from the list.';
			return $context;

		} catch ( \MailPoet\API\MP\v1\APIException $e ) {
			throw new Exception( $e->getMessage() );
		}
	}

}

RemoveSubscriberFromList::get_instance();
