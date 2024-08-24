<?php
/**
 * DeleteEntryGravityForm.
 * php version 5.6
 *
 * @category DeleteEntryGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\GravityForms\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use GFAPI;

/**
 * DeleteEntryGravityForm
 *
 * @category DeleteEntryGravityForm
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class DeleteEntryGravityForm extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'GravityForms';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'delete_entry_gravity_form';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Entry for Form', 'suretriggers' ),
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
		$form_id  = $selected_options['gravity_form'];
		$entry_id = $selected_options['gravity_form_entry_id'];

		if ( ! class_exists( 'GFAPI' ) ) {
			return;
		}

		$entry_result = GFAPI::entry_exists( $entry_id );
		if ( $entry_result ) {
			$entry = GFAPI::get_entry( $entry_id );
			if ( $form_id === $entry['form_id'] ) {
				$delete_entry = GFAPI::delete_entry( $entry_id );
				if ( is_wp_error( $delete_entry ) ) {
					throw new Exception( $delete_entry->get_error_message() );
				} else {
					$context = [
						'status'   => esc_attr__( 'Success', 'suretriggers' ),
						'response' => esc_attr__( 'Entry deleted successfully.', 'suretriggers' ),
					];
					return $context;
				}
			} else {
				throw new Exception( 'Entry ID is not for specific form.' );    
			}
		} else {
			throw new Exception( 'No Entry Found' );
		}
	}
}

DeleteEntryGravityForm::get_instance();
