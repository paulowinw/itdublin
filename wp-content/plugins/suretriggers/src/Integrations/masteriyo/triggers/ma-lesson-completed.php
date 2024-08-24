<?php
/**
 * MaLessonCompleted.
 * php version 5.6
 *
 * @category MaLessonCompleted
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Masteriyo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'MaLessonCompleted' ) ) :

	/**
	 * MaLessonCompleted
	 *
	 * @category MaLessonCompleted
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class MaLessonCompleted {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Masteriyo';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ma_lms_lesson_completed';

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
				'common_action' => 'masteriyo_new_course_progress_item',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int    $item_id The new course progress item ID.
		 * @param object $object The new course progress item object.
		 * @return void
		 */
		public function trigger_listener( $item_id, $object ) {

			if ( ! method_exists( $object, 'get_item_type' ) ) {
				return;
			}
			if ( 'lesson' !== $object->get_item_type() ) {
				return;
			}
			if ( ! function_exists( 'masteriyo_get_lesson' ) ) {
				return;
			}
			if ( method_exists( $object, 'get_item_id' ) && method_exists( $object, 'get_user_id' ) ) {
				$lesson  = masteriyo_get_lesson( $object->get_item_id() );
				$context = array_merge(
					WordPress::get_user_context( $object->get_user_id() ),
					$lesson->get_data()
				);
			
				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			}
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	MaLessonCompleted::get_instance();

endif;
