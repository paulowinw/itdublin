<?php
/**
 * AddUpdateSubscriptionCustomFields.
 * php version 5.6
 *
 * @category AddUpdateSubscriptionCustomFields
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WoocommerceSubscriptions\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddUpdateSubscriptionCustomFields
 *
 * @category AddUpdateSubscriptionCustomFields
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddUpdateSubscriptionCustomFields extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WoocommerceSubscriptions';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wc_add_update_subscription_custom_fields';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add or Update Custom Fields', 'suretriggers' ),
			'action'   => 'wc_add_update_subscription_custom_fields',
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
	 * @return object|array|null|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$subscription_id = $selected_options['subscription_id'];
		$meta            = $selected_options['subscription_meta'];

		// Check if function exists to get subscription object.
		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return;
		}

		// Get subscription object using subscription id.
		$subscription = wcs_get_subscription( $subscription_id );

		// Update meta data for subscription.
		if ( $subscription ) {
			foreach ( $meta as $fields ) {
				$meta_key   = $fields['meta_key'];
				$meta_value = $fields['meta_value'];
				$subscription->update_meta_data( $meta_key, $meta_value );
			}
			// Save subscription.
			$subscription->save();

			// Return subscription data.
			return $subscription->get_data();
		} else {
			throw new Exception( 'Subscription not found for the provided Subscription ID.' );
		}
	}
}

AddUpdateSubscriptionCustomFields::get_instance();
