<?php
/**
 * PostReviewed.
 * php version 5.6
 *
 * @category PostReviewed
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

if ( ! class_exists( 'PostReviewed' ) ) :

	/**
	 * PostReviewed
	 *
	 * @category PostReviewed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class PostReviewed {


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
		public $trigger = 'voxel_post_reviewed';

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
				'label'         => __( 'Post Reviewed', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			if ( ! empty( $trigger_data ) && is_array( $trigger_data ) && isset( $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] ) ) {
				$triggers[ $this->integration ][ $this->trigger ]['common_action'] = 'voxel/app-events/post-types/' . $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] . '/review:created';
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
			if ( ! property_exists( $event, 'post' ) || ! property_exists( $event, 'review' ) ) {
				return;
			}
			$context                      = WordPress::get_post_context( $event->post->get_id() );
			$context['post']              = Voxel::get_post_fields( $event->post->get_id() );
			$context['review_content']    = $event->review->get_content();
			$context['review_created_at'] = $event->review->get_created_at();
			$context['review_details']    = $event->review->get_details();
			$context['review_by']         = WordPress::get_user_context( $event->review->get_user_id() );
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
	PostReviewed::get_instance();

endif;
