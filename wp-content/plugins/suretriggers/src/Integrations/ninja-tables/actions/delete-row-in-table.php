<?php
/**
 * DeleteRowInTable.
 * php version 5.6
 *
 * @category DeleteRowInTable
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
 * DeleteRowInTable
 *
 * @category DeleteRowInTable
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class DeleteRowInTable extends AutomateAction {

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
	public $action = 'ninja_tables_delete_row';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Delete Row in Table', 'suretriggers' ),
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
		$row_id     = $selected_options['row_id'];
		$table_name = $wpdb->prefix . 'ninja_table_items';
		$sql        = 'SELECT * FROM ' . $table_name . ' WHERE id = %d';
		$results    = $wpdb->get_row( $wpdb->prepare( $sql, $row_id ), ARRAY_A ); // @phpcs:ignore
		if ( empty( $results ) ) {
			throw new Exception( 'No row exist with ' . $row_id . ' ID' );
		}
		$where = [
			'id' => $row_id,
		];
		$wpdb->delete( $table_name, $where );

		return true;
		
		
	}
}

DeleteRowInTable::get_instance();
