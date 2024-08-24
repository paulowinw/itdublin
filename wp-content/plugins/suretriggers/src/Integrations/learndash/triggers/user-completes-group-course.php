<?php
/**
 * UserCompletesGroupLDCourse.
 * php version 5.6
 *
 * @category UserCompletesGroupLDCourse
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

if ( ! class_exists( 'UserCompletesGroupLDCourse' ) ) :


	/**
	 * UserCompletesGroupLDCourse
	 *
	 * @category UserCompletesGroupLDCourse
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class UserCompletesGroupLDCourse {


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
		public $trigger = 'user_completes_group_ld_course';

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
				'label'         => __( 'User Completes Group Course', 'suretriggers' ),
				'action'        => 'user_completes_group_ld_course',
				'common_action' => 'learndash_group_completed',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 99,
				'accepted_args' => 1,
			];

			return $triggers;

		}

		/**
		 * Trigger listener
		 *
		 * @param array $group_progress  Group Progress.
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function trigger_listener( $group_progress ) {

			if ( ! function_exists( 'ld_course_access_expires_on' ) ) {
				return;
			}

			if ( empty( $group_progress ) ) {
				return;
			}
	
			$user = $group_progress['user'];
			if ( ! $user instanceof \WP_User ) {
				return;
			}
	
			$progress        = $group_progress['progress'];
			$group_completed = false;
			if ( ( ! empty( $progress['total'] ) ) && ( absint( $progress['total'] ) === absint( $progress['completed'] ) ) ) {
				$group_completed = true;
			}
			if ( false === $group_completed ) {
				return;
			}
	
			$user_id    = absint( $user->ID );
			$group      = $group_progress['group'];
			$group_id   = absint( $group->ID );
			$course_ids = $progress['course_ids'];

			$context                             = WordPress::get_user_context( $user_id );
			$context['sfwd_group_id']            = $group_id;
			$context['group_title']              = get_the_title( $group_id );
			$context['group_url']                = get_permalink( $group_id );
			$context['group_featured_image_id']  = get_post_meta( $group_id, '_thumbnail_id', true );
			$context['group_featured_image_url'] = get_the_post_thumbnail_url( $group_id );
			foreach ( $course_ids as $key => $course_id ) {
				$context[ 'completed ' . $key ]['course_id']                 = $course_id;
				$context[ 'completed ' . $key ]['course_title']              = get_the_title( $course_id );
				$context[ 'completed ' . $key ]['course_url']                = get_permalink( $course_id );
				$context[ 'completed ' . $key ]['course_featured_image_id']  = get_post_meta( $course_id, '_thumbnail_id', true );
				$context[ 'completed ' . $key ]['course_featured_image_url'] = get_the_post_thumbnail_url( $course_id );
				$timestamp   = ld_course_access_expires_on( $course_id, $user_id );
				$timestamp   = is_numeric( $timestamp ) ? (int) $timestamp : null;
				$date_format = get_option( 'date_format' );
				if ( is_string( $date_format ) ) {
					$context[ 'completed ' . $key ]['course_access_expiry_date'] = wp_date( $date_format, $timestamp );
				}
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	UserCompletesGroupLDCourse::get_instance();

endif;
