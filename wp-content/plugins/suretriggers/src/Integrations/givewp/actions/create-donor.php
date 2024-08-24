<?php
/**
 * CreateDonor.
 * php version 5.6
 *
 * @category CreateDonor
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
 * CreateDonor
 *
 * @category CreateDonor
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateDonor extends AutomateAction {

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
	public $action = 'givewp_create_donor';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Donor', 'suretriggers' ),
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

		$first_name   = $selected_options['first_name'];
		$last_name    = $selected_options['last_name'];
		$email        = $selected_options['email'];
		$company_name = $selected_options['company_name'];

		if ( ! class_exists( 'Give_Donor' ) || ! function_exists( 'Give' ) ) {
			return;
		}

		if ( is_email( $email ) ) {
			$donor_data = [
				'email' => $email,
				'name'  => $first_name . ' ' . $last_name,
			];
			$donor      = new \Give_Donor();
			if ( method_exists( $donor, 'create' ) ) {
				$donor_id = $donor->create( $donor_data );

				if ( isset( $donor_id ) && isset( $company_name ) ) {
					\Give()->donor_meta->update_meta( $donor_id, '_give_donor_company', $company_name );
				}
				return \Give()->donors->get_donor_by( 'id', $donor_id );
			}
		} else {
			throw new Exception( 'Invalid Email.' );
		}
	}

}
CreateDonor::get_instance();
