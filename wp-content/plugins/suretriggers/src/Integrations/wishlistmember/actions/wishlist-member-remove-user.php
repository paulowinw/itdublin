<?php
/**
 * WishlistMemberRemoveUser.
 * php version 5.6
 *
 * @category WishlistMemberRemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WishlistMember\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * WishlistMemberRemoveUser
 *
 * @category WishlistMemberRemoveUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WishlistMemberRemoveUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WishlistMember';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wishlistmember_remove_user_levels';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Membership Level', 'suretriggers' ),
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
		$wlm_level_id = $selected_options['wlm_remove_levels'];

		if ( empty( $user_id ) || empty( $wlm_level_id ) ) {
			return false;
		}

		wlmapi_remove_member_from_level( $wlm_level_id, $user_id );

		$level = wlmapi_get_level( $wlm_level_id );

		$context             = [];
		$context['level_id'] = $wlm_level_id;

		if ( isset( $level['level'] ) ) {
			$context['level_name'] = $level['level']['name'];
		}

		return array_merge(
			WordPress::get_user_context( $user_id ),
			$context
		);
	}
}

WishlistMemberRemoveUser::get_instance();
