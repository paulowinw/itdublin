<?php
/**
 * CourseAddedToLDGroup.
 * php version 5.6
 *
 * @category CourseAddedToLDGroup
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

if ( ! class_exists( 'CourseAddedToLDGroup' ) ) :


	/**
	 * CourseAddedToLDGroup
	 *
	 * @category CourseAddedToLDGroup
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class CourseAddedToLDGroup {


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
		public $trigger = 'course_added_to_ld_group';

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
				'action'        => 'course_added_to_ld_group',
				'common_action' => 'ld_added_course_group_access',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param int $course_id            Course ID.
		 * @param int $group_id          Course ID.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $course_id, $group_id ) {

			if ( ! function_exists( 'get_post_type' ) ||
			! function_exists( 'learndash_get_post_type_slug' ) ) {
				return;
			}

			if ( get_post_type( $course_id ) !== learndash_get_post_type_slug( 'course' ) && 
			get_post_type( $group_id ) !== learndash_get_post_type_slug( 'group' ) ) {
				return;
			}

			$context['course_id']                 = $course_id;
			$context['sfwd_group_id']             = $group_id;
			$context['group_title']               = get_the_title( $group_id );
			$context['course_title']              = get_the_title( $course_id );
			$context['course_url']                = get_permalink( $course_id );
			$context['course_featured_image_id']  = get_post_meta( $course_id, '_thumbnail_id', true );
			$context['course_featured_image_url'] = get_the_post_thumbnail_url( $course_id );
			$context['group_name']                = get_the_title( $group_id );

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	CourseAddedToLDGroup::get_instance();

endif;
