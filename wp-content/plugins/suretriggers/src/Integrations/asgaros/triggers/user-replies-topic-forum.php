<?php
/**
 * AsUserRepliesTopicForum.
 * php version 5.6
 *
 * @category UserRepliesTopicForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Asgaros\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use AsgarosForum;

if ( ! class_exists( 'AsUserRepliesTopicForum' ) ) :

	/**
	 * AsUserRepliesTopicForum
	 *
	 * @category AsUserRepliesTopicForum
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class AsUserRepliesTopicForum {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Asgaros';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'asgaros_user_replies_topic_forum';

		use SingletonLoader;


		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Replies to Topic in Forum', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'asgarosforum_after_add_post_submit',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 5,
				'accepted_args' => 6,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param integer $post_id Post.
		 * @param integer $topic_id Topic.
		 * @param string  $subject Topic.
		 * @param string  $content Content.
		 * @param string  $link Link.
		 * @param integer $author_id author id.
		 * @return void
		 */
		public function trigger_listener( $post_id, $topic_id, $subject, $content, $link, $author_id ) {
			if ( ! class_exists( 'AsgarosForum' ) ) {
				return;
			}
			$context       = [];
			$asgaros_forum = new AsgarosForum();
			if ( ! isset( $post_id ) ) {
				return;
			}

			$topic               = $asgaros_forum->content->get_topic( $topic_id );
			$forum_id            = $topic->parent_id;
			$context['topic_id'] = $topic_id;
			$context['post_id']  = $post_id;
			
			$context['forum_id'] = $forum_id;
			$context['forum']    = $asgaros_forum->content->get_forum( $forum_id );
			$context['topic']    = $asgaros_forum->content->get_topic( $topic_id );
			$context['post']     = $asgaros_forum->content->get_post( $post_id );
			$context['author']   = WordPress::get_user_context( $author_id );
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	AsUserRepliesTopicForum::get_instance();

endif;
