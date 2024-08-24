<?php
/**
 * AddNoteToDonor.
 * php version 5.6
 *
 * @category AddNoteToDonor
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GiveWP\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddNoteToDonor
 *
 * @category AddNoteToDonor
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddNoteToDonor extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'GiveWP';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'givewp_add_not_to_donor';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Note To Donor', 'suretriggers' ),
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
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$email      = $selected_options['donor_email'];
		$donor_note = $selected_options['donor_note'];

		if ( ! class_exists( 'Give_Donor' ) || ! function_exists( 'Give' ) ) {
			return;
		}

		if ( is_email( $email ) ) {
			$donor = new \Give_Donor( $email, false );
			if ( property_exists( $donor, 'id' ) ) {
				if ( 0 != $donor->id ) {
					if ( method_exists( $donor, 'add_note' ) ) {
						$donor->add_note( $donor_note );
						$donor_arr = get_object_vars( \Give()->donors->get_donor_by( 'id', $donor->id ) );
						return array_merge( [ 'note' => $donor->get_notes() ], $donor_arr );
					}
				} else {
					throw new Exception( 'Donor does not exist.' );
				}
			}
		} else {
			throw new Exception( 'Invalid Email.' );
		}
	}

}

AddNoteToDonor::get_instance();
