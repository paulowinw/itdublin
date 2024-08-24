<?php
/**
 * SliceWPListAffiliates.
 * php version 5.6
 *
 * @category SliceWPListAffiliates
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
 * SliceWPListAffiliates
 *
 * @category SliceWPListAffiliates
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SliceWPListAffiliates extends AutomateAction {

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
	public $action = 'slicewp_list_affiliates';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'List Affiliates', 'suretriggers' ),
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
		global $wpdb;
		$status = $selected_options['status'];
		if ( 'all' === $status ) {
			$query = "SELECT *
			FROM {$wpdb->prefix}slicewp_affiliates ORDER BY id DESC";
		} else {
			$query = $wpdb->prepare(
				"SELECT *
			FROM {$wpdb->prefix}slicewp_affiliates WHERE status=%s ORDER BY id DESC",
				$status
			);
		
		}
		$affiliate_results = $wpdb->get_results( $query ); //phpcs:ignore

		if ( empty( $affiliate_results ) ) {
			throw new Exception( 'Not able to list affiliates, try later.' );
		} else {
			return $affiliate_results;
		}
	}
}

SliceWPListAffiliates::get_instance();
