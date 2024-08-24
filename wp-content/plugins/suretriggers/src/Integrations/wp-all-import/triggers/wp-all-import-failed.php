<?php
/**
 * WpAllImportFailed.
 * php version 5.6
 *
 * @category WpAllImportFailed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WpAllImport\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'WpAllImportFailed' ) ) :

	/**
	 * WpAllImportFailed
	 *
	 * @category WpAllImportFailed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class WpAllImportFailed {

		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'WpAllImport';

		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'wp_all_import_failed';

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
				'label'         => __( 'Import Failed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmxi_after_xml_import',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int    $import_id Import ID.
		 * @param object $import_obj Import Object.
		 *
		 * @return void|array|bool
		 */
		public function trigger_listener( $import_id, $import_obj ) {

			if ( empty( $import_id ) ) {
				return false;
			}

			/** 
			 * Ignoring next line
			 * 
			 * @phpstan-ignore-next-line 
			 * */
			if ( $import_obj->failed == 0 ) { //phpcs:ignore
				return;
			}
			
			$context['import'] = $import_obj;
			
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	WpAllImportFailed::get_instance();

endif;
