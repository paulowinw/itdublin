<?php
/**
 * AddRowInTable.
 * php version 5.6
 *
 * @category AddRowInTable
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
 * AddRowInTable
 *
 * @category AddRowInTable
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddRowInTable extends AutomateAction {

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
	public $action = 'ninja_tables_new_row';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Row in Table', 'suretriggers' ),
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
	
		$owner_id = $selected_options['owner_id'];
		$table_id = $selected_options['table_id'];
		$user     = get_userdata( (int) $owner_id );
		if ( ! $user ) {
			throw new Exception( 'No user exist with ' . $owner_id . ' ID' );
		}
		$formatted_row = [];
		if ( ! empty( $selected_options['row_fields'] ) ) {
			foreach ( $selected_options['row_fields'] as $field ) {
				if ( is_array( $field ) && ! empty( $field ) ) {
					foreach ( $field as $key => $value ) {
						if ( false === strpos( $key, 'field_column' ) && '' !== $value ) {
							$formatted_row[ $key ] = $value;
						}
					}
				}
			}
		}
	 
		$data       = [
			'table_id'   => $selected_options['table_id'],
			'owner_id'   => $owner_id,
			'attribute'  => 'value',
			'value'      => wp_json_encode( $formatted_row ),
			'created_at' => gmdate( 'Y-m-d H:i:s' ),
			'updated_at' => gmdate( 'Y-m-d H:i:s' ),
		];
		$table_name = $wpdb->prefix . 'ninja_table_items';
		$wpdb->insert( $table_name, $data );
		
		// Optional: Get the ID of the inserted row.
		$inserted_id      = $wpdb->insert_id;
		$sql              = 'SELECT * FROM ' . $table_name . ' WHERE id = %d ORDER BY id DESC LIMIT 1'; 
		$results          = $wpdb->get_row( $wpdb->prepare( $sql, $inserted_id ), ARRAY_A );// @phpcs:ignore
		$results['value'] = json_decode( $results['value'], true );
		$results['owner'] = WordPress::get_user_context( $owner_id );
		do_action( 'ninja_table_after_add_item', $inserted_id, $table_id, $formatted_row );
		return $results;
		
		
	}
}

AddRowInTable::get_instance();
