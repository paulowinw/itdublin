<?php
/**
 * WfCreateTopic.
 * php version 5.6
 *
 * @category WfCreateTopic
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
use wpforo\classes\Members;

/**
 * WfCreateTopic
 *
 * @category WfCreateTopic
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class WfCreateTopic extends AutomateAction {


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
	public $action = 'wp_foro_create_topic';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Topic', 'suretriggers' ),
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
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
	
		$user_email = $selected_options['wp_user_email'];
		
		if ( ! function_exists( 'WPF' ) || ! class_exists( 'wpforo\classes\Members' ) || ! function_exists( 'wpfkey' ) || 
		! function_exists( 'wpfval' ) || ! function_exists( 'wpforo_length' ) || ! function_exists( 'wpforo_setting' ) ) {
			return false;
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id                         = $user->ID;
				WPF()->current_user              = $user;
				WPF()->current_userid            = $user->ID;
				WPF()->current_user_login        = $user->user_login;
				WPF()->current_user_email        = $user->user_email;
				WPF()->current_user_display_name = $user->display_name;
				$member                          = new Members();
				$user                            = $member->get_member( $user->ID );
				if ( ! wpfkey( $user, 'groupid' ) || ! method_exists( $member, 'get_member' ) || ! method_exists( $member, 'synchronize_user' ) ) {
					$member->synchronize_user( $user->ID );
					$user = $member->get_member( $user->ID );
				}
				$user_meta                             = get_user_meta( $user->ID );
				WPF()->current_user                    = $user;
				WPF()->current_usermeta                = $user_meta;
				WPF()->current_user_groupid            = WPF()->current_user['groupid'];
				WPF()->current_user_secondary_groupids = WPF()->current_user['secondary_groupids'];
				WPF()->current_user_groupids           = array_unique( array_filter( array_merge( (array) WPF()->current_user_groupid, (array) WPF()->current_user_secondary_groupids ) ) );
				WPF()->current_user_status             = (string) wpfval( $user, 'status' );
				if ( function_exists( 'WPF' ) ) {
					$forum_id        = $selected_options['forum_id'];
					$args['forumid'] = $forum_id;
					$args['title']   = sanitize_title( $selected_options['title'] );
					$args['body']    = preg_replace( '#</pre>[\r\n\t\s\0]*<pre>#isu', "\r\n", (string) $selected_options['content'] );
					$args['userid']  = $user_id;
					$args['tags']    = $selected_options['topic_tags'];
					$args['private'] = ( isset( $selected_options['topic_private'] ) && $selected_options['topic_private'] ? 1 : 0 );
					WPF()->member->set_guest_cookies( $args );
					$min = wpforo_setting( 'posting', 'topic_body_min_length' );
					if ( $min ) {
						if ( wpfkey( $args, 'body' ) && (int) $min > wpforo_length( $args['body'] ) ) {
							throw new Exception( 'The content is too short' );
						}
					}
					if ( ! isset( $args['forumid'] ) ) {
						throw new Exception( 'Add Topic error: No forum selected' );
					}
			
					if ( ! WPF()->forum->get_forum( $args['forumid'] ) ) {
						throw new Exception( 'Add Topic error: No forum selected' );
					}
			
					if ( ! WPF()->perm->forum_can( 'ct', $args['forumid'] ) ) {
						throw new Exception( 'You don\'t have permission to create topic into this forum' );
					}
			
					if ( ! WPF()->perm->can_post_now() ) {
						throw new Exception( 'You are posting too quickly. Slow down.' );
					}
					$topicid = WPF()->topic->add( $args );
					if ( $topicid ) {
						return [
							'topic' => WPF()->topic->get_topic( $topicid ),
							'user'  => WordPress::get_user_context( $args['userid'] ),
						];
					} else {
						throw new Exception( 'Topic not created.' );
					}
				} else {
					throw new Exception( 'Can not create topic.' );
				}
			} else {
				throw new Exception( 'User not found.' );
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

WfCreateTopic::get_instance();
