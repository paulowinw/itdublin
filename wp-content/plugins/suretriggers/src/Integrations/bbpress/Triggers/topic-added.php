<?php
/**
 * TopicCreated.
 * php version 5.6
 *
 * @category TopicCreated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BbPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'TopicCreated' ) ) :
	/**
	 * TopicCreated
	 *
	 * @category TopicCreated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class TopicCreated {

		use SingletonLoader;

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'bbPress';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'bbpress_topic_created';

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
		 * @param array $triggers triggers.
		 *
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Topic Created.', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'bbp_new_topic',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int   $topic_id topic id.
		 * @param int   $forum_id forum id.
		 * @param array $anonymous_data anonymous_data.
		 * @param int   $topic_author topic author.
		 * @return void
		 */
		public function trigger_listener( $topic_id, $forum_id, $anonymous_data, $topic_author ) {
			$topic             = get_the_title( $topic_id );
			$topic_link        = get_the_permalink( $topic_id );
			$topic_description = get_the_content( null, false, $topic_id );
			$topic_status      = get_post_status( $topic_id );

			$forum             = get_the_title( $forum_id );
			$forum_link        = get_the_permalink( $forum_id );
			$forum_description = get_the_content( null, false, $forum_id );
			$forum_status      = get_post_status( $forum_id );

			$forum = [
				'forum'             => $forum_id,
				'forum_title'       => $forum,
				'forum_link'        => $forum_link,
				'forum_description' => $forum_description,
				'forum_status'      => $forum_status,
			];

			$topic = [
				'topic_title'       => $topic,
				'topic_link'        => $topic_link,
				'topic_description' => $topic_description,
				'topic_status'      => $topic_status,
			];
			
			$user_id = ap_get_current_user_id();
			$context = array_merge(
				WordPress::get_user_context( intval( '"' . $user_id . '"' ) ),
				$forum,
				$topic
			);
		
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	TopicCreated::get_instance();
endif;
