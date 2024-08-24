<?php
/**
 * UserPostsActivityStream.
 * php version 5.6
 *
 * @category UserPostsActivityStream
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use BP_Activity_Activity;

if ( ! class_exists( 'UserPostsActivityStream' ) ) :

	/**
	 * UserPostsActivityStream
	 *
	 * @category UserPostsActivityStream
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserPostsActivityStream {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'BuddyPress';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_posts_activity_stream';

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
				'label'         => __( 'A user posts activity to their stream', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'bp_activity_posted_update',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $content Content.
		 * @param int $user_id User ID.
		 * @param int $activity_id Activity ID.
		 * @return void
		 */
		public function trigger_listener( $content, $user_id, $activity_id ) {
			
			$context['content'] = $content;
			$context['user']    = WordPress::get_user_context( $user_id );
			if ( class_exists( 'BP_Activity_Activity' ) ) {
				$context['activity'] = new BP_Activity_Activity( $activity_id );
			}

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
	UserPostsActivityStream::get_instance();

endif;
