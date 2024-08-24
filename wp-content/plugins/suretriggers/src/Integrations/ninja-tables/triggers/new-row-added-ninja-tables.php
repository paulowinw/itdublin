<?php
/**
 * NewRowAddedNinjaTables.
 * php version 5.6
 *
 * @category NewRowAddedNinjaTables
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\NinjaTables\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'NewRowAddedNinjaTables' ) ) :

	/**
	 * NewRowAddedNinjaTables
	 *
	 * @category NewRowAddedNinjaTables
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class NewRowAddedNinjaTables {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'NinjaTables';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'ninja_tables_new_row_added';

		use SingletonLoader;

		/**
		 * Constructor
		 *
		 * @since  1.0.0
		 */
		public function __construct() {
			add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
		}

		/**
		 * Register action.
		 *
		 * @param array $triggers trigger data.
		 * @return array
		 */
		public function register( $triggers ) {
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'New Row Added', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'ninja_table_after_add_item',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param integer $insert_id insert Id.
		 * @param integer $table_id table Id.
		 * @param array   $attributes attributes.
		 *
		 * @return void
		 */
		public function trigger_listener( $insert_id, $table_id, $attributes ) {
			global $wpdb;
			if ( empty( $insert_id ) ) {
				return;
			}
			$results = [];
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'ninja_table_items WHERE table_id = %d AND id = %d ORDER BY id DESC LIMIT 1';
			$results      = $wpdb->get_row( $wpdb->prepare( $sql, $table_id, $insert_id), ARRAY_A );// @phpcs:ignore
			if ( ! empty( $results ) ) {
				$results['value'] = json_decode( $results['value'], true );
				$results['owner'] = WordPress::get_user_context( $results['owner_id'] );
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $results,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	NewRowAddedNinjaTables::get_instance();

endif;
