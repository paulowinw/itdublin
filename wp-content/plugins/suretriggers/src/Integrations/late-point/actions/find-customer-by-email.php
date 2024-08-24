<?php
/**
 * FindCustomerByEmail.
 * php version 5.6
 *
 * @category FindCustomerByEmail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\LatePoint\Actions;

use OsCustomerModel;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\LatePoint\LatePoint;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * FindCustomerByEmail
 *
 * @category FindCustomerByEmail
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindCustomerByEmail extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'LatePoint';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'lp_find_customer_by_email';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Find Customer By Email', 'suretriggers' ),
			'action'   => 'lp_find_customer_by_email',
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
	 * @throws Exception Exception.
	 *
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		return LatePoint::find_object_by_email( $selected_options, 'Customer' );
	}

}

FindCustomerByEmail::get_instance();
