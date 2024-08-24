<?php
/**
 * RemoveMembership.
 * php version 5.6
 *
 * @category RemoveMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MemberPress\Actions;

use Exception;
use MeprHooks;
use MeprSubscription;
use MeprTransaction;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * RemoveMembership
 *
 * @category RemoveMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveMembership extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MemberPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mp_remove_membership';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove from Membership', 'suretriggers' ),
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
	 * @throws Exception Throws exception.
	 *
	 * @return array|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! $user_id ) {
			throw new Exception( 'User not found with this email address.' );
		}

		if ( is_array( $selected_options['memberpressproduct'] ) ) {
			$membership_id = $selected_options['memberpressproduct']['value'];
		} else {
			$membership_id = $selected_options['memberpressproduct'];
		}
		$user_obj = get_user_by( 'id', $user_id );
		$table    = MeprSubscription::account_subscr_table(
			'created_at',
			'DESC',
			'',
			'',
			'any',
			'',
			false,
			[
				'member'   => $user_obj->user_login,
				'statuses' => [
					MeprSubscription::$active_str,
					MeprSubscription::$suspended_str,
					MeprSubscription::$cancelled_str,
				],
			],
			MeprHooks::apply_filters( 'mepr_user_subscriptions_query_cols', [ 'id', 'product_id', 'created_at' ] )
		);

		if ( 0 === $table['count'] ) {
			$this->set_error(
				[
					'msg' => __( 'Empty subscription table ', 'suretriggers' ),
				]
			);
			return false;
		}

		foreach ( $table['results'] as $row ) {
			if ( $row->product_id === $membership_id || '-1' === $membership_id ) {
				if ( 'subscription' === $row->sub_type ) {
					$sub = new MeprSubscription( $row->id );
				} elseif ( 'transaction' === $row->sub_type ) {
					$sub = new MeprTransaction( $row->id );
				}
				$sub->destroy();
				$member = $sub->user();
				$member->update_member_data();
			}
		}

		$context               = [];
		$context['user_email'] = $selected_options['wp_user_email'];

		if ( '-1' !== $membership_id ) {
			$context['membership_id']                 = $membership_id;
			$context['membership_title']              = get_the_title( $membership_id );
			$context['membership_url']                = get_permalink( $membership_id );
			$context['membership_featured_image_id']  = get_post_meta( $membership_id, '_thumbnail_id', true );
			$context['membership_featured_image_url'] = get_the_post_thumbnail_url( $membership_id );
		}

		return $context;
	}
}

RemoveMembership::get_instance();
