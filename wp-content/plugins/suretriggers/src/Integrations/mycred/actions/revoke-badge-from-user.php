<?php
/**
 * RevokeBadgeFromUser.
 * php version 5.6
 *
 * @category RevokeBadgeFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MyCred\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RevokeBadgeFromUser
 *
 * @category RevokeBadgeFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokeBadgeFromUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MyCred';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'revoke_badge_from_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Badge', 'suretriggers' ),
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
	 *
	 * @return array|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$badge_id = $selected_options['cred_badge'];

		if ( empty( $badge_id ) || empty( $user_id ) || ! function_exists( 'mycred_get_users_badges' ) || ! function_exists( 'mycred_get_user_meta' ) || ! function_exists( 'mycred_delete_user_meta' ) ) {
			return false;
		}

		$badges = mycred_get_users_badges( absint( $user_id ) );

		if ( is_array( $badges ) && ! empty( $badges ) ) {
			foreach ( $badges as $k => $v ) {
				if ( $badge_id == $k ) {
					$meta_key = 'mycred_badge' . $k;
					mycred_delete_user_meta( absint( $user_id ), $meta_key );

					return array_merge(
						WordPress::get_user_context( $user_id ),
						[
							'badge_id' => $k,
						]
					);
				} else {
					return [
						'success' => false,
						'msg'     => __( 'The user does not have the selected badge.', 'suretriggers' ),
					];   
				}
			}
		} else {
			return [
				'success' => false,
				'msg'     => __( 'The user does not have badges.', 'suretriggers' ),
			];   
		}
	}
}

RevokeBadgeFromUser::get_instance();
