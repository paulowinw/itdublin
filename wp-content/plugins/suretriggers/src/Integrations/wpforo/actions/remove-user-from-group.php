<?php
/**
 * RemoveUserFromGroup.
 * php version 5.6
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\wpForo\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * RemoveUserFromGroup
 *
 * @category RemoveUserFromGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RemoveUserFromGroup extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'wpForo';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wp_foro_remove_user_from_group';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Remove User from Group', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];
		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields template fields.
	 * @param array $selected_options saved template data.
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
	
		$group_id = $selected_options['group_id'];
		global $wpdb;
		$user_email = $selected_options['wp_user_email'];

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );

			if ( $user ) {
				$user_id = $user->ID;
				if ( function_exists( 'WPF' ) ) {
					WPF()->member->clear_db_cache();
					if ( function_exists( 'wpforo_member' ) ) {
						$user_group_id = wpforo_member( $user_id, 'groupid' );
						if ( $group_id && $group_id === $user_group_id ) {

							$default_group = absint( WPF()->usergroup->default_groupid );
							$sql           = 'UPDATE `' . WPF()->tables->profiles . '` SET `groupid` = %d WHERE `userid` = %d';
							if ( false !== WPF()->db->query( WPF()->db->prepare( $sql, $default_group, $user_id ) ) ) {
								if ( function_exists( 'wpforo_clean_cache' ) ) {
									wpforo_clean_cache( 'avatar', $user_id );
								}
								
								delete_user_meta( intval( $user_id ), '_wpf_member_obj' );
								
								if ( function_exists( 'wpforo_setting' ) ) {
									if ( wpforo_setting( 'seo', 'seo_profile' ) ) {
										WPF()->seo->clear_cache();
									}
								}

								$user_sql = 'SELECT `groupid`  from `' . WPF()->tables->profiles . '` WHERE `userid` = %d';
								$results = $wpdb->get_results( $wpdb->prepare( $user_sql, $user_id ), ARRAY_A );// @phpcs:ignore

								$group = WPF()->usergroup->get_usergroup( $results[0]['groupid'] );
								return array_merge( WordPress::get_user_context( $user_id ), $group );
							}
						} else {
							throw new Exception( 'User is not member of specified group' );
						}
					}
				} else {
					throw new Exception( 'User not found.' );
				}
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
	}

}

RemoveUserFromGroup::get_instance();
