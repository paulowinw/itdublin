<?php
/**
 * ChangePaymentStatus.
 * php version 5.6
 *
 * @category ChangePaymentStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Paymattic\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use WPPayForm\App\Models\Submission;


/**
 * ChangePaymentStatus
 *
 * @category ChangePaymentStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangePaymentStatus extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Paymattic';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'wppay_payment_change_status';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Change Payment Status ', 'suretriggers' ),
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
	 * @throws Exception Error.
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! ( class_exists( 'WPPayForm\App\Models\Submission' ) ) ) {
			return;
		}
		$submisson_id     = $selected_options['submission_id'];
		$new_status       = $selected_options['new_status'];
		$submission_model = new Submission();
		$updated          = $submission_model->updateSubmission(
			$submisson_id,
			[
				'payment_status' => $new_status,
			]
		);
		if ( $updated ) {
			$submission = $submission_model->getSubmission( $submisson_id );
			do_action( 'wppayform/after_payment_status_change', $submisson_id, $new_status ); //phpcs:ignore
			return $submission->toArray();
		} else {
			throw new Exception( 'Failed to update status' );
		}
		
	}

}

ChangePaymentStatus::get_instance();
