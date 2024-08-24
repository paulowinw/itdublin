<?php
/**
 * EnrollToMembership.
 * php version 5.6
 *
 * @category EnrollToMembership
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

/**
 * EnrollToMembership
 *
 * @category EnrollToMembership
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class EnrollToMembership extends AutomateAction {


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
	public $action = 'lms_enroll_to_membership';

	use SingletonLoader;


	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Enroll User in a Membership', 'suretriggers' ),
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
	 *
	 * @psalm-suppress InvalidScalarArgument
	 * @psalm-suppress UndefinedMethod
	 *
	 * @return bool|array|object
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		/**
		 * Int member ship ID
		 *
		 * @var int|mixed|null $membership_id Membership ID.
		 */
		$membership_id = isset( $selected_options['llms_membership'] ) ? $selected_options['llms_membership'] : '0';
		$membership    = get_post( (int) $membership_id );

		if ( ! $membership ) {
			$this->set_error(
				[
					'msg' => __( 'No membership is available ', 'suretriggers' ),
				]
			);
			return false;
		}
		llms_enroll_student( $user_id, $membership_id, 'SureTriggers' );

		$membership_data = LifterLMS::get_lms_membership_context( $membership_id );

		return $membership_data;
	}

}

EnrollToMembership::get_instance();
