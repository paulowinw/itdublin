<?php
/**
 * LessonCompleted.
 * php version 5.6
 *
 * @category LessonCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\AcademyLMS\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'LessonCompleted' ) ) :

	/**
	 * LessonCompleted
	 *
	 * @category LessonCompleted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class LessonCompleted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'AcademyLMS';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ac_lms_lesson_completed';

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
				'label'         => __( 'Lesson Completed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'academy/frontend/after_mark_topic_complete',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param string $topic_type Topic type.
		 * @param int    $course_id Attempted Course ID.
		 * @param int    $topic_id Topic ID.
		 * @param int    $user_id   User ID.
		 * @return void
		 */
		public function trigger_listener( $topic_type, $course_id, $topic_id, $user_id ) {

			if ( empty( $topic_id ) ) {
				return;
			}

			if ( 'lesson' !== $topic_type ) {
				return;
			}

			if ( ! class_exists( '\Academy\Helper' ) ) {
				return;
			}

			$lesson_data = \Academy\Helper::get_lesson( $topic_id );
			if ( is_object( $lesson_data ) ) {
				$lesson_data = get_object_vars( $lesson_data );
			}
			$context                = array_merge( $lesson_data, WordPress::get_user_context( $user_id ) );
			$context['course_data'] = WordPress::get_post_context( $course_id );
			$context['lesson']      = $topic_id;
			$context['course']      = $course_id;
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger'    => $this->trigger,
					'wp_user_id' => $user_id,
					'context'    => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	LessonCompleted::get_instance();

endif;
