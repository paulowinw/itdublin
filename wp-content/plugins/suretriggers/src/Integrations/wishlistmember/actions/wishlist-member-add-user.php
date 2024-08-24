<?php
/**
 * WishlistMemberAddUser.
 * php version 5.6
 *
 * @category WishlistMemberAddUser
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
use SureTriggers\Integrations\WishlistMember\WishlistMember;

/**
 * WishlistMemberAddUser
 *
 * @category WishlistMemberAddUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WishlistMemberAddUser extends AutomateAction {


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
	public $action = 'wishlist_member_add_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add User to Membership Level', 'suretriggers' ),
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
		$level_id = $selected_options['wlm_levels'];

		if ( empty( $user_id ) || empty( $level_id ) ) {
			return false;
		}

		$args = [
			'Users' => $user_id,
		];

		wlmapi_add_member_to_level( $level_id, $args );

		$level = wlmapi_get_level( $level_id );

		$context             = [];
		$context['level_id'] = $level_id;

		if ( isset( $level['level'] ) ) {
			$context['level_name'] = $level['level']['name'];
		}

		$user        = WordPress::get_user_context( $user_id );
		$usercontext = WishlistMember::get_user_info( (int) $level_id, (int) $user_id );
		return array_merge(
			$user,
			$context,
			$usercontext
		);
	}
}

WishlistMemberAddUser::get_instance();
