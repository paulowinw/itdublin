<?php
/**
 * NewWallPostByUser.
 * php version 5.6
 *
 * @category NewWallPostByUser
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
use SureTriggers\Integrations\Voxel\Voxel;

if ( ! class_exists( 'NewWallPostByUser' ) ) :

	/**
	 * NewWallPostByUser
	 *
	 * @category NewWallPostByUser
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class NewWallPostByUser {


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
		public $trigger = 'voxel_new_wall_post_by_user';

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
				'label'         => __( 'New Wall Post by User', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];
			if ( ! empty( $trigger_data ) && is_array( $trigger_data ) && isset( $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] ) ) {
				$triggers[ $this->integration ][ $this->trigger ]['common_action'] = 'voxel/app-events/post-types/' . $trigger_data[ $this->integration ][ $this->trigger ]['selected_options']['post_type']['value'] . '/wall-post:created';
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
			if ( ! property_exists( $event, 'status' ) || ! property_exists( $event, 'post' ) || ! property_exists( $event, 'author' ) ) {
				return;
			}
			$context['post'] = Voxel::get_post_fields( $event->status->get_post_id() );
			$user            = get_userdata( $event->author->get_id() );
			if ( $user ) {
				$user_data                    = (array) $user->data;
				$context['user_display_name'] = $user_data['display_name'];
				$context['user_name']         = $user_data['user_nicename'];
				$context['user_email']        = $user_data['user_email'];
				$context['user_id']           = $event->status->get_author();
			}
			if ( function_exists( 'Voxel\Timeline\prepare_status_json' ) ) {
				// Get the status details.
				$status_details = \Voxel\Timeline\prepare_status_json( $event->status );
				foreach ( (array) $status_details as $key => $value ) {
					$context['wall_post'][ $key ] = $value;
				}
			}
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
	NewWallPostByUser::get_instance();

endif;
