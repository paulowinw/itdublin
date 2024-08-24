<?php
/**
 * CreateOrObtainCustomer.
 * php version 5.6
 *
 * @category CreateOrObtainCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentSupport\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use FluentSupport\App\Models\Customer;

/**
 * CreateOrObtainCustomer
 *
 * @category CreateOrObtainCustomer
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateOrObtainCustomer extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentSupport';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'create_or_obtain_customer_fluent_support';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Or Obtain Customer', 'suretriggers' ),
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
	 * @param array $selected_options selected_options.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$email      = sanitize_email( $selected_options['customer_email'] );
		$first_name = sanitize_text_field( $selected_options['first_name'] );
		$last_name  = sanitize_text_field( $selected_options['last_name'] );

		if ( ! is_email( $email ) ) {
			throw new Exception( 'Invalid email.' );
		}

		if ( ! class_exists( 'FluentSupport\App\Models\Customer' ) ) {
			throw new Exception( 'Error: Plugin did not installed correctly. Some classes are missing.' );
		}

		$customer_record = Customer::where( 'email', $email )->first();

		if ( ! $customer_record ) {
			$customer = [
				'email'      => $email,
				'first_name' => $first_name,
				'last_name'  => $last_name,
			];

			Customer::create( $customer );
			$customer_record = Customer::where( 'email', $email )->first();
		}

		return $customer_record->getAttributes();
	}
}

CreateOrObtainCustomer::get_instance();
