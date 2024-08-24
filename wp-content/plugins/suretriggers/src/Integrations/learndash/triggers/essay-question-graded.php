<?php
/**
 * EssayQuestionGraded.
 * php version 5.6
 *
 * @category EssayQuestionGraded
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

if ( ! class_exists( 'EssayQuestionGraded' ) ) :


	/**
	 * EssayQuestionGraded
	 *
	 * @category EssayQuestionGraded
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class EssayQuestionGraded {


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
		public $trigger = 'essay_question_graded';

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
				'label'         => __( 'User Added in Group', 'suretriggers' ),
				'action'        => 'essay_question_graded',
				'common_action' => 'learndash_essay_response_data_updated',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int    $quiz_id            Quiz ID.
		 * @param int    $question_id          Question ID.
		 * @param object $essay          Essay.
		 * @param array  $submitted_essay          Submitted Essay.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $quiz_id, $question_id, $essay, $submitted_essay ) {
			// Ensure it's an essay and graded.
			if ( ! is_a( $essay, 'WP_Post' ) || 'sfwd-essays' !== $essay->post_type || 
			'graded' !== $essay->post_status ) {
				return;
			}

			$essay_id         = $essay->ID;
			$course_id        = get_post_meta( $essay->ID, 'course_id', true );
			$step_id          = get_post_meta( $essay->ID, 'lesson_id', true );
			$quiz_post_id     = get_post_meta( $essay->ID, 'quiz_post_id', true );
			$question_post_id = get_post_meta( $essay->ID, 'question_post_id', true );

			$context = WordPress::get_user_context( (int) $essay->post_author );

			$context['quiz_name']           = is_int( $quiz_post_id ) ? (int) get_the_title( $quiz_post_id ) : null;
			$context['sfwd_quiz_id']        = $quiz_post_id;
			$context['course_name']         = is_int( $course_id ) ? (int) get_the_title( $course_id ) : null;
			$context['course_id']           = $course_id;
			$context['lesson_name']         = is_int( $step_id ) ? (int) get_the_title( $step_id ) : null;
			$context['lesson_id']           = $step_id;
			$context['sfwd_question_id']    = $question_post_id;
			$context['question_name']       = is_int( $question_post_id ) ? (int) get_the_title( $question_post_id ) : null;
			$context['essay_id']            = $essay_id;
			$context['essay']               = WordPress::get_post_context( $essay_id );
			$context['essay_points_earned'] = (int) $submitted_essay['points_awarded'];

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	EssayQuestionGraded::get_instance();

endif;
