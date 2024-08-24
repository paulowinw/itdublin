<?php
/**
 * WishlistMemberDeleteExistingMember.
 * php version 5.6
 *
 * @category WishlistMemberDeleteExistingMember
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
 * WishlistMemberDeleteExistingMember
 *
 * @category WishlistMemberDeleteExistingMember
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WishlistMemberDeleteExistingMember extends AutomateAction {


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
	public $action = 'wishlist_member_delete_existing_member';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Existing Member', 'suretriggers' ),
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
		$member_id = $selected_options['wlm_members'];
		if ( empty( $member_id ) || ! function_exists( 'wlmapi_delete_member' ) ) {
			return false;
		}
		$response = wlmapi_delete_member( $member_id );

		if ( $response ) {
			return array_merge(
				[
					'success' => true,
					'msg'     => __( 'Member deleted successfully.', 'suretriggers' ),
				]
			);
		} else {
			return [
				'success' => false,
				'msg'     => __( 'Failed to delete a member.', 'suretriggers' ),
			];   
		}
	}
}

WishlistMemberDeleteExistingMember::get_instance();
