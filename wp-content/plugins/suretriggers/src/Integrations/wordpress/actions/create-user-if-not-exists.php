<?php
/**
 * CreateUserIfNotExists.
 * php version 5.6
 *
 * @category CreateUserIfNotExists
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * CreateUserIfNotExists
 *
 * @category CreateUserIfNotExists
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateUserIfNotExists extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'create_user_if_not_exists';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User: Create new user if not exists with Email', 'suretriggers' ),
			'action'   => 'create_user_if_not_exists',
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
	 * @return array|object|bool
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$email = sanitize_email( $selected_options['user_email'] );

		/**
		 * User Data
		 */
		
		$user_pass = empty( $selected_options['password'] ) ? wp_generate_password() : $selected_options['password'];
		
		$userdata               = [
			'user_login' => $selected_options['user_name'],
			'user_email' => $email,
			'first_name' => $selected_options['first_name'],
			'last_name'  => $selected_options['last_name'],
			'user_pass'  => $user_pass,
			'role'       => $selected_options['role'],
		];
		$force_update_user_role = $selected_options['force_update_user_role'] ? 'yes' : 'no';

		$add_user_roles = $selected_options['add_user_roles'] ? 'yes' : 'no';
		$show_password  = $selected_options['show_password'];

		$user = get_user_by( 'email', $email );
		
		if ( $user ) {
			$user_id        = $user->ID;
			$userdata['ID'] = $user_id;

			$current_roles = $user->roles;
		
			$specified_role           = $userdata['role'];
			$specified_excluded_roles = [];
			if ( ! empty( $selected_options['exclude_role'] ) ) {
				$specified_excluded_roles = array_column( $selected_options['exclude_role'], 'value' );
			}
			$common_roles = array_values( array_intersect( $specified_excluded_roles, $current_roles ) );
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
			if ( empty( $selected_options['password'] ) ) {
				unset( $userdata['user_pass'] );
			}
			if ( empty( $userdata['role'] ) ) {
				unset( $userdata['role'] );
			} else {
				if ( ! empty( $common_roles ) ) {
					unset( $userdata['role'] );
				} else {
					if ( in_array( 'administrator', $current_roles ) ) {
						if ( 'yes' != $force_update_user_role ) {
							unset( $userdata['role'] );
						}
					} else {
						if ( 'yes' == $add_user_roles && 'yes' != $force_update_user_role ) {
							$user->add_role( $selected_options['role'] );
							unset( $userdata['role'] );
						} else {
							$user_role = $user->roles[0];

							/** 
							 * 
							 * Ignore line
							 * 
							 * @phpstan-ignore-next-line
							 */
							$user_role_capabilities = get_role( $user_role )->capabilities;
									
							/** 
							 * 
							 * Ignore line
							 * 
							 * @phpstan-ignore-next-line
							 */
							$specified_role_capabilities = get_role( $specified_role )->capabilities;
									
							// Check if the user's role has all the capabilities of the specified role.
							$has_all_capabilities = true;
							foreach ( $specified_role_capabilities as $capability => $value ) {
								if ( ! isset( $user_role_capabilities[ $capability ] ) || $user_role_capabilities[ $capability ] !== $value ) {
									$has_all_capabilities = false;
									break;
								}
							}

							if ( $has_all_capabilities ) {
								// User's role has all the capabilities of the specified role so don't update it unless it is force update.
								if ( 'yes' != $force_update_user_role ) {
									unset( $userdata['role'] );
								}
							}
						}
					}
				}
			}
			wp_update_user( wp_slash( $userdata ) );
		} else {
			$user_id = wp_insert_user( wp_slash( $userdata ) );
		}

		if ( ! is_wp_error( $user_id ) ) {
			if ( isset( $selected_options['user_meta'] ) && is_array( $selected_options['user_meta'] ) && count( $selected_options['user_meta'] ) ) {
				foreach ( $selected_options['user_meta'] as $meta ) {
					$meta_value   = $meta['metaValue'];
					$is_meta_json = json_decode( $meta_value, true );
					if ( null !== $is_meta_json ) {
						$meta_value = $is_meta_json;
					}
					update_user_meta( $user_id, $meta['metaKey'], $meta_value );
				}
			}
		}
		
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			
			$user_arr = (array) $user;
			if ( isset( $user_arr['data'] ) ) {
				if ( empty( $user_arr['data']->user_pass ) ) {
					$user_arr['data']->user_pass = __( 'Unable to show the password while updating the user', 'suretriggers' );
				} else {
					if ( 'yes' === $show_password ) {
						$user_arr['data']->user_pass = $user_pass;
					} else {
						unset( $user_arr['data']->user_pass );
					}
				}
				return $user_arr;
			}       
		}
			
		return [];

	}
}

CreateUserIfNotExists::get_instance();
