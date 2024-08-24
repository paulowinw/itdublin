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

namespace SureTriggers\Integrations\LifterLMS\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Traits\SingletonLoader;
use Exception;

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
	public $integration = 'LifterLMS';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lms_mark_lesson_complete';

	use SingletonLoader;


	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark lesson complete for User', 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 *
	 * @return void|bool|array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$lesson_id  = $selected_options['lesson'];
		$user_email = $selected_options['wp_user_email'];

		if ( is_email( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			if ( $user ) {
				$user_id = $user->ID;
				if ( ! function_exists( 'llms_mark_complete' ) ) {
					$this->set_error(
						[
							'msg' => __( 'The function llms_mark_complete does not exist', 'suretriggers' ),
						]
					);
					return false;
				}
				// Mark lesson completed.
				llms_mark_complete( $user_id, $lesson_id, 'lesson' );
				return LifterLMS::get_lms_lesson_context( $lesson_id );
			} else {
				throw new Exception( 'User not exists.' );
			}
		} else {
			throw new Exception( 'Enter valid email address.' );
		}
	}

}

MarkLessonComplete::get_instance();
