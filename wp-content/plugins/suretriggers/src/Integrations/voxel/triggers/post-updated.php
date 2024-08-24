<?php
/**
 * PostUpdated.
 * php version 5.6
 *
 * @category PostUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Controllers\OptionController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Integrations\Voxel\Voxel;

if ( ! class_exists( 'PostUpdated' ) ) :

	/**
	 * PostUpdated
	 *
	 * @category PostUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PostUpdated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Voxel';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'voxel_post_updated';

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
			$trigger_data                                     = OptionController::get_option( 'trigger_data' );
			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Post Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			if ( ! empty( $trigger_data ) && is_array( $trigger_data ) && isset( $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] ) ) {
				$triggers[ $this->integration ][ $this->trigger ]['common_action'] = 'voxel/app-events/post-types/' . $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] . '/post:updated';
			}

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'post' ) ) {
				return;
			}
			$post            = WordPress::get_post_context( $event->post->get_id() );
			$context         = $post;
			$context['post'] = Voxel::get_post_fields( $event->post->get_id() );
			// Get the post type.
			$post_type            = get_post_type( $event->post->get_id() );
			$context['post_type'] = $post_type;

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
	PostUpdated::get_instance();

endif;
