<?php
/**
 * PostReplyTopicForum.
 * php version 5.6
 *
 * @category PostReplyTopicForum
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
 * PostReplyTopicForum
 *
 * @category PostReplyTopicForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PostReplyTopicForum extends AutomateAction {

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
	public $action = 'bb_post_reply_topic_forum';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Post Reply Topic In Forum', 'suretriggers' ),
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

		if ( ! function_exists( 'bbp_insert_reply' ) || ! function_exists( 'bbp_get_reply' ) ) {
			return [];
		}
		$forum_id      = $selected_options['forum_id'];
		$topic_id      = $selected_options['topic_id'];
		$reply_title   = $selected_options['reply_title'];
		$reply_content = $selected_options['reply_content'];
		$reply_author  = $selected_options['reply_creator'];

		if ( is_email( $reply_author ) ) {
			$user = get_user_by( 'email', $reply_author );
			if ( $user ) {
				$creator_id = $user->ID;
				$reply_id   = bbp_insert_reply(
					[
						'post_parent'  => $topic_id,
						'post_title'   => $reply_title,
						'post_content' => $reply_content,
						'post_author'  => $creator_id,
					],
					[
						'forum_id' => $forum_id,
						'topic_id' => $topic_id,
					]
				);
				return [
					'forum_id'      => $forum_id,
					'forum_title'   => get_the_title( $forum_id ),
					'topic_id'      => $topic_id,
					'topic_title'   => get_the_title( $topic_id ),
					'reply_id'      => $reply_id,
					'reply'         => bbp_get_reply( $reply_id ),
					'reply_creator' => WordPress::get_user_context( $creator_id ),
				];
			} else {
				throw new Exception( 'Reply user does not exist!!' );
			}
		} else {
			throw new Exception( 'Invalid Email!!' );
		}
	}
}

PostReplyTopicForum::get_instance();
