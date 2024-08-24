<?php
/**
 * PostTopicInForum.
 * php version 5.6
 *
 * @category PostTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * PostTopicInForum
 *
 * @category PostTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PostTopicInForum extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_post_topic_in_forum';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Post Topic In Forum', 'suretriggers' ),
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
	 * @return bool|array 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( is_array( $selected_options['forum_id'] ) ) {
			$forum_id = $selected_options['forum_id']['value'];
		} else {
			$forum_id = $selected_options['forum_id'];
		}
		$topic_title   = $selected_options['topic_title'];
		$topic_content = $selected_options['topic_content'];
		$topic_author  = $selected_options['topic_creator'];

		if ( ! function_exists( 'bbp_insert_topic' ) || ! function_exists( 'bbp_get_public_status_id' ) || ! function_exists( 'bbp_get_topic' ) ) {
			return [];
		}
		if ( is_email( $topic_author ) ) {
			$user = get_user_by( 'email', $topic_author );
			if ( $user ) {
				$creator_id = $user->ID;
				// Create the initial topic.
				$topic_id               = bbp_insert_topic(
					[
						'post_parent'  => $forum_id,
						'post_title'   => $topic_title,
						'post_content' => $topic_content,
						'post_status'  => bbp_get_public_status_id(),
						'post_author'  => $creator_id,
					],
					[ 'forum_id' => $forum_id ]
				);
				$context['creator']     = WordPress::get_user_context( $creator_id );
				$context['topic']       = bbp_get_topic( $topic_id, ARRAY_A );
				$context['forum_id']    = $forum_id;
				$context['forum_title'] = get_the_title( $forum_id );
				return $context;
			} else {
				throw new Exception( 'Creator user does not exist!!' );
			}
		} else {
			throw new Exception( 'Invalid Email!!' );
		}   
	}
}

PostTopicInForum::get_instance();
