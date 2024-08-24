<?php
/**
 * UpdateRowInTable.
 * php version 5.6
 *
 * @category UpdateRowInTable
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
 * UpdateRowInTable
 *
 * @category UpdateRowInTable
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdateRowInTable extends AutomateAction {

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
	public $action = 'ninja_tables_update_row';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Update Row in Table', 'suretriggers' ),
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
	
		$owner_id = $selected_options['owner_id'] ? $selected_options['owner_id'] : '';
		$table_id = $selected_options['table_id'];
		$row_id   = $selected_options['row_id'];
		if ( '' !== $owner_id ) {
			$user = get_userdata( (int) $owner_id );
			if ( ! $user ) {
				throw new Exception( 'No user exist with ' . $owner_id . ' ID' );
			}
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
		$table_name = $wpdb->prefix . 'ninja_table_items';
		$sql        = 'SELECT * FROM ' . $table_name . ' WHERE id = %d';
		$results    = $wpdb->get_row( $wpdb->prepare( $sql, $row_id ), ARRAY_A ); // @phpcs:ignore
		if ( empty( $results ) ) {
			throw new Exception( 'No row exist with ' . $row_id . ' ID' );
		}
		
		if ( 'null' === $results['value'] ) {
			$values = $formatted_row;
		} else {
			$values = array_replace( (array) wp_json_encode( $results['value'] ), $formatted_row );
		}
	
		$data = [
			'table_id'   => $table_id,
			'attribute'  => 'value',
			'owner_id'   => $owner_id,
			'value'      => wp_json_encode( $values ),
			'updated_at' => gmdate( 'Y-m-d H:i:s' ),
		];
		if ( '' !== $owner_id ) {
			$data['owner_id'] = $owner_id;
		}
		
		$where = [
			'id' => $row_id,
		];
		$wpdb->update( $table_name, $data, $where );
		
		
		$results['value'] = $values;
		$results['owner'] = WordPress::get_user_context( $results['owner_id'] );
		do_action( 'ninja_table_after_update_item', $row_id, $table_id, $formatted_row );
		return $results;
		
		
	}
}

UpdateRowInTable::get_instance();
