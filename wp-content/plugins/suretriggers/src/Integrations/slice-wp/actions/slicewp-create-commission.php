<?php
/**
 * SliceWPCreateCommission.
 * php version 5.6
 *
 * @category SliceWPCreateCommission
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\SliceWP\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * SliceWPCreateCommission
 *
 * @category SliceWPCreateCommission
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SliceWPCreateCommission extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'SliceWP';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'slicewp_create_commission';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Commission for specific Affiliate', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$affiliate_id = $selected_options['affiliate_id'];

		if ( ! function_exists( 'slicewp_insert_commission' ) || ! function_exists( 'slicewp_get_affiliate' ) ) {
			return;
		}
		$affiliate         = slicewp_get_affiliate( $affiliate_id );
		$affiliate_user_id = $affiliate->get( 'user_id' );

		if ( $affiliate_user_id ) {
			$user = get_user_by( 'id', $affiliate_user_id );
			if ( $user ) {
				$commission_data['user_name'] = $user->user_login;
			}
			
			$date                             = $selected_options['commission_date'];
			$commission_data['affiliate_id']  = $affiliate_id;
			$commission_data['status']        = $selected_options['status'];
			$commission_data['amount']        = $selected_options['amount'];
			$commission_data['reference']     = $selected_options['reference'];
			$commission_data['date_created']  = $date;
			$commission_data['date_modified'] = $date;
			$commission_data['type']          = $selected_options['type'];
			
		
			// Insert commission into the database.
			$commission_id = slicewp_insert_commission( $commission_data );
		
			if ( $commission_id ) {
				$commission_data['commission_id'] = $commission_id;
				return $commission_data;
			} else {
				throw new Exception( 'We are unable to add commission.' );
			}
		} else {
			throw new Exception( 'The user is not an affiliate.' );
		}
	}
}

SliceWPCreateCommission::get_instance();
