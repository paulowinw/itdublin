<?php
/**
 * ListRowsIntable.
 * php version 5.6
 *
 * @category ListRowsIntable
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\NinjaTables\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * ListRowsIntable
 *
 * @category ListRowsIntable
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ListRowsIntable extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'NinjaTables';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'ninja_tables_list_rows';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Lists Rows in Table', 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$results = [];
		global $wpdb;
	
		$table_id   = $selected_options['table_id'];
		$table_name = $wpdb->prefix . 'ninja_table_items';
		$sql        = 'SELECT * FROM ' . $table_name . ' WHERE table_id = %d';
		$results    = $wpdb->get_results( $wpdb->prepare( $sql, $table_id ), ARRAY_A ); // @phpcs:ignore
		if ( empty( $results ) ) {
			throw new Exception( 'No row exist with ' . $table_id . ' table ID' );
		}
		

		return $results;
		
		
	}
}

ListRowsIntable::get_instance();
