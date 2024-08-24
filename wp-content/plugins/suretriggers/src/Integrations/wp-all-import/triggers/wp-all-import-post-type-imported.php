<?php
/**
 * WpAllImportPostTypeImported.
 * php version 5.6
 *
 * @category WpAllImportPostTypeImported
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

if ( ! class_exists( 'WpAllImportPostTypeImported' ) ) :

	/**
	 * WpAllImportPostTypeImported
	 *
	 * @category WpAllImportPostTypeImported
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class WpAllImportPostTypeImported {

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
		public $trigger = 'wp_all_import_post_type_imported';

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
				'label'         => __( 'Post Type Imported', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'pmxi_saved_post',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;
		}

		/**
		 *  Trigger listener
		 *
		 * @param int $post_id Post ID.
		 * @param int $xml_node XMLNode.
		 * @param int $is_update Is update.
		 *
		 * @return void|array|bool
		 */
		public function trigger_listener( $post_id, $xml_node, $is_update ) {

			if ( empty( $post_id ) ) {
				return false;
			}
			
			// Get post type.
			$post_type = get_post_type( $post_id );

			$context['post_type'] = $post_type;
			$context['post']      = WordPress::get_post_context( $post_id );
			
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
	WpAllImportPostTypeImported::get_instance();

endif;
