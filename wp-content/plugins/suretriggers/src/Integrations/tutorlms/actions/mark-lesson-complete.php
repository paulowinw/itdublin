<?php
/**
 * MarkLessonComplete.
 * php version 5.6
 *
 * @category MarkLessonComplete
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\TutorLMS\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * MarkLessonComplete
 *
 * @category MarkLessonComplete
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkLessonComplete extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'TutorLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'tlms_mark_lesson_complete';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark Lesson Complete', 'suretriggers' ),
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
	 * @return array|void
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$lesson_id  = isset( $selected_options['lesson_id'] ) ? $selected_options['lesson_id'] : '0';
		$user_email = ( isset( $selected_options['wp_user_email'] ) ) ? $selected_options['wp_user_email'] : '';

		if ( ! function_exists( 'tutils' ) ) {
			return [];
		}

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id   = $user->ID;
				$completed = tutils()->mark_lesson_complete( $lesson_id, $user_id );
				if ( $completed ) {
					$context                = WordPress::get_user_context( $user_id );
					$context['lesson_id']   = $lesson_id;
					$context['lesson_name'] = get_the_title( $lesson_id );
					return $context;
				} else {
					throw new Exception( 'Lesson Cannot be completed.' );
				}
			} else {
				throw new Exception( 'User not found.' );
			}
		} else {
			throw new Exception( 'Enter valid email address.' );
		}
	}
}

MarkLessonComplete::get_instance();
