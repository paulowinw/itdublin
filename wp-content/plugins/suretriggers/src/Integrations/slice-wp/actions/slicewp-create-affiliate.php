<?php
/**
 * SliceWPCreateAffiliate.
 * php version 5.6
 *
 * @category SliceWPCreateAffiliate
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
 * SliceWPCreateAffiliate
 *
 * @category SliceWPCreateAffiliate
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SliceWPCreateAffiliate extends AutomateAction {

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
	public $action = 'slicewp_create_affiliate';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Affiliate', 'suretriggers' ),
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
		$wp_user = get_user_by( 'email', $selected_options['user_login'] );
		if ( $wp_user ) {
			$userid               = $wp_user->data->ID;
			$affiliate['user_id'] = $userid;
		}
		
		$affiliate                  = [];
		$affiliate['status']        = $selected_options['status'];
		$date                       = $selected_options['affiliate_date'];
		$affiliate['payment_email'] = $selected_options['payment_email'];
		$affiliate['welcome_email'] = $selected_options['welcome_email'];
		$affiliate['welcome_email'] = ( 'true' === $affiliate['welcome_email'] ) ? true : false;
		$affiliate['date_created']  = $date;
		$affiliate['date_modified'] = $date;


		if ( ! function_exists( 'slicewp_insert_affiliate' ) ) {
			throw new Exception( 'Slicewp functions not found.' );
		}

		if ( false === $wp_user ) {
			throw new Exception( 'User does not exist.' );
		}

		// Insert affiliate into the database.
		$affiliate_id = slicewp_insert_affiliate( $affiliate );
		if ( ! $affiliate_id ) {
			throw new Exception( 'Not able to create new affiliate, try later.' );
		} else {
			$affiliate['affiliate_id'] = $affiliate_id;
			unset( $affiliate['welcome_email'] );
			return $affiliate;
		}
	}
}

SliceWPCreateAffiliate::get_instance();
