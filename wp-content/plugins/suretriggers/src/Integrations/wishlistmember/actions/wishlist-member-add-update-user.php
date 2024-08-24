<?php
/**
 * WishlistMemberAddOrUpdateUser.
 * php version 5.6
 *
 * @category WishlistMemberAddOrUpdateUser
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
 * WishlistMemberAddOrUpdateUser
 *
 * @category WishlistMemberAddOrUpdateUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WishlistMemberAddOrUpdateUser extends AutomateAction {


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
	public $action = 'wishlist_member_add_update_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add/Update Member', 'suretriggers' ),
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
		$email    = sanitize_email( $selected_options['user_email'] );
		$level_id = $selected_options['wlm_levels'];
		$user     = get_user_by( 'email', $email );
		if ( empty( $email ) || empty( $level_id ) ) {
			return false;
		}
		
		$userdata = [
			'user_login' => $selected_options['user_name'],
			'user_email' => $email,
			'first_name' => $selected_options['first_name'],
			'last_name'  => $selected_options['last_name'],
			'user_pass'  => $selected_options['user_pass'],
		];
		
		$userdata['company']  = $selected_options['company'];
		$userdata['address1'] = $selected_options['address1'];
		$userdata['address2'] = $selected_options['address2'];
		$userdata['city']     = $selected_options['city'];
		$userdata['state']    = $selected_options['state'];
		$userdata['zip']      = $selected_options['zip'];
		$userdata['country']  = $selected_options['country'];
		
		if ( $user ) {
			$user_id        = $user->ID;
			$userdata['ID'] = $user_id;
			/**
			 * Skipping if empty value.
			 */
			if ( empty( $userdata['user_login'] ) ) {
				unset( $userdata['user_login'] );
			}
			if ( empty( $userdata['first_name'] ) ) {
				unset( $userdata['first_name'] );
			}
			if ( empty( $userdata['last_name'] ) ) {
				unset( $userdata['last_name'] );
			}
			if ( empty( $userdata['last_name'] ) ) {
				unset( $userdata['last_name'] );
			}
			if ( empty( $selected_options['password'] ) ) {
				unset( $userdata['user_pass'] );
			}
			if ( empty( $selected_options['company'] ) ) {
				unset( $userdata['company'] );
			}
			if ( empty( $selected_options['state'] ) ) {
				unset( $userdata['state'] );
			}
			if ( empty( $selected_options['address1'] ) ) {
				unset( $userdata['address1'] );
			}
			if ( empty( $selected_options['address2'] ) ) {
				unset( $userdata['address2'] );
			}
			if ( empty( $selected_options['city'] ) ) {
				unset( $userdata['city'] );
			}
			if ( empty( $selected_options['zip'] ) ) {
				unset( $userdata['zip'] );
			}
			if ( empty( $selected_options['country'] ) ) {
				unset( $userdata['country'] );
			}
			if ( function_exists( 'wlmapi_update_member' ) ) {
				wlmapi_update_member( $user_id, $userdata );
			}       
		} else {
			if ( function_exists( 'wlmapi_add_member' ) ) {
				wlmapi_add_member( $userdata );
			}
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

WishlistMemberAddOrUpdateUser::get_instance();
