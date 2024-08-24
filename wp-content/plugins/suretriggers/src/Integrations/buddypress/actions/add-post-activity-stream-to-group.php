<?php
/**
 * AddPostActivityStreamToGroup.
 * php version 5.6
 *
 * @category AddPostActivityStreamToGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use BP_Activity_Activity;

/**
 * AddPostActivityStreamToGroup
 *
 * @category AddPostActivityStreamToGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostActivityStreamToGroup extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'add_post_activity_stream_to_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add a post to the activity stream of a group', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$group_id       = $selected_options['bp_group'];
		$action         = $selected_options['activity_action'];
		$action_link    = $selected_options['activity_action_link'];
		$action_content = $selected_options['activity_content'];
		$action_author  = $selected_options['wp_user_email'];
		
		if ( empty( $action_author ) || ! is_email( $action_author ) ) {
			throw new Exception( 'Invalid sender email.' );
		}

		$action_author_user = get_user_by( 'email', $action_author );

		if ( empty( $group_id ) ) {
			return false;
		}
		$activity = false;
		global $wpdb;
		if ( '-1' === $group_id ) {
			$statuses   = [ 'public', 'private', 'hidden' ];
			$in_str_arr = array_fill( 0, count( $statuses ), '%s' );
			$in_str     = join( ',', $in_str_arr );
			$results    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status IN %s", $in_str ) );
			if ( $results ) {
				foreach ( $results as $result ) {
					$hide_sitewide = false;
					if ( in_array(
						$result->status,
						[
							'private',
							'hidden',
						],
						true
					) ) {
						$hide_sitewide = true;
					}
					$user = get_user_by( 'email', $action_author );
					if ( $user ) {
						if ( function_exists( 'bp_activity_add' ) ) {
							$activity = bp_activity_add(
								[
									'action'        => $action,
									'content'       => $action_content,
									'primary_link'  => $action_link,
									'component'     => 'groups',
									'item_id'       => $result->id,
									'type'          => 'activity_update',
									'user_id'       => $user->ID,
									'hide_sitewide' => $hide_sitewide,
								]
							);
							if ( is_wp_error( $activity ) ) {
								break;
							}
							if ( ! $activity ) {
								break;
							}
						}
					} else {
						throw new Exception( 'User email not exists.' );
					}
				}
			}
		} else {
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE id = %d", $group_id ) );
			if ( $results ) {
				foreach ( $results as $result ) {
					$hide_sitewide = false;
					if ( in_array(
						$result->status,
						[
							'private',
							'hidden',
						],
						true
					) ) {
						$hide_sitewide = true;
					}
					$user = get_user_by( 'email', $action_author );
					if ( $user ) {
						if ( function_exists( 'bp_activity_add' ) ) {
							$activity = bp_activity_add(
								[
									'action'        => $action,
									'content'       => $action_content,
									'primary_link'  => $action_link,
									'component'     => 'groups',
									'item_id'       => $result->id,
									'type'          => 'activity_update',
									'user_id'       => $user->ID,
									'hide_sitewide' => $hide_sitewide,
								]
							);
							if ( is_wp_error( $activity ) ) {
								break;
							}
							if ( ! $activity ) {
								break;
							}
						}
					} else {
						throw new Exception( 'User email not exists.' );
					}
				}
			}
		}

		if ( is_wp_error( $activity ) ) {
			throw new Exception( $activity->get_error_message() );
		} elseif ( ! $activity ) {
			throw new Exception( 'There is an error on posting stream.' );
		} else {
			if ( class_exists( 'BP_Activity_Activity' ) ) {
				$context = new BP_Activity_Activity( $activity );
				if ( is_object( $context ) ) {
					$context = get_object_vars( $context );
				}
				if ( $action_author_user ) {
					if ( property_exists( $action_author_user, 'ID' ) ) {
						$action_author_id = $action_author_user->ID;
						return array_merge(
							WordPress::get_user_context( $action_author_id ),
							$context
						);
					}
				}
			}
		}
	}
}

AddPostActivityStreamToGroup::get_instance();
