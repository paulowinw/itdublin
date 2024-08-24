<?php
/**
 * UserSubmitsEssayLDQuiz.
 * php version 5.6
 *
 * @category UserSubmitsEssayLDQuiz
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LearnDash\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'UserSubmitsEssayLDQuiz' ) ) :


	/**
	 * UserSubmitsEssayLDQuiz
	 *
	 * @category UserSubmitsEssayLDQuiz
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserSubmitsEssayLDQuiz {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'LearnDash';

		/**
		 * Action name.
		 *
		 * @var string
		 */
		public $trigger = 'user_submits_essay_ld_quiz';

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
		 * Register a action.
		 *
		 * @param array $triggers actions.
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'User Submits Essay Quiz', 'suretriggers' ),
				'action'        => 'user_submits_essay_ld_quiz',
				'common_action' => 'learndash_new_essay_submitted',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int   $essay_id Essay ID.
		 * @param array $essay_args Essay Args.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $essay_id, $essay_args ) {
			
			if ( 0 === (int) $essay_id || empty( $essay_args ) ) {
				return;
			}

			$question_post_id = get_post_meta( $essay_id, 'question_post_id', true );
			$quiz_post_id     = get_post_meta( $essay_id, 'quiz_post_id', true );
			$course_id        = get_post_meta( $essay_id, 'course_id', true );
			$lesson_id        = get_post_meta( $essay_id, 'lesson_id', true );

			$context = WordPress::get_user_context( $essay_args['post_author'] );

			$context['quiz_name']        = is_int( $quiz_post_id ) ? (int) get_the_title( $quiz_post_id ) : null;
			$context['sfwd_quiz_id']     = $quiz_post_id;
			$context['question_name']    = is_int( $question_post_id ) ? (int) get_the_title( $question_post_id ) : null;
			$context['sfwd_question_id'] = $question_post_id;
			$context['course_name']      = is_int( $course_id ) ? (int) get_the_title( $course_id ) : null;
			$context['course_id']        = $course_id;
			$context['lesson_name']      = is_int( $lesson_id ) ? (int) get_the_title( $lesson_id ) : null;
			$context['lesson_id']        = $lesson_id;
			$context['essay_id']         = $essay_id;
			$context['essay']            = WordPress::get_post_context( $essay_id );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserSubmitsEssayLDQuiz::get_instance();

endif;
