<?php
/**
 * WishlistMemberDeleteLevel.
 * php version 5.6
 *
 * @category WishlistMemberDeleteLevel
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
 * WishlistMemberDeleteLevel
 *
 * @category WishlistMemberDeleteLevel
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WishlistMemberDeleteLevel extends AutomateAction {


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
	public $action = 'wishlist_member_delete_level';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Level', 'suretriggers' ),
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
		if ( empty( $level_id ) || ! function_exists( 'wlmapi_delete_level' ) ) {
			return false;
		}
		$response = wlmapi_delete_level( $level_id );
		if ( $response ) {
			return $response;
		} else {
			return [
				'success' => false,
				'msg'     => __( 'Failed to update a level', 'suretriggers' ),
			];   
		}
	}
}

WishlistMemberDeleteLevel::get_instance();
