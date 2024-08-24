<?php
/**
 * ProfileWallNewPost.
 * php version 5.6
 *
 * @category ProfileWallNewPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\Voxel\Voxel;

if ( ! class_exists( 'ProfileWallNewPost' ) ) :

	/**
	 * ProfileWallNewPost
	 *
	 * @category ProfileWallNewPost
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ProfileWallNewPost {


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
		public $trigger = 'voxel_profile_new_wall_post';

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
				'label'         => __( 'Profile New Wall Post', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/post-types/profile/wall-post:created',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'status' ) || ! property_exists( $event, 'author' ) ) {
				return;
			}
			$context['profile'] = Voxel::get_post_fields( $event->status->get_post_id() );
			$user               = get_userdata( $event->author->get_id() );
			if ( $user ) {
				$user_data                       = (array) $user->data;
				$context['profile_display_name'] = $user_data['display_name'];
				$context['profile_name']         = $user_data['user_nicename'];
				$context['profile_email']        = $user_data['user_email'];
				$context['profile_user_id']      = $event->status->get_author();
			}
			if ( function_exists( 'Voxel\Timeline\prepare_status_json' ) ) {
				// Get the status details.
				$status_details = \Voxel\Timeline\prepare_status_json( $event->status );
				foreach ( (array) $status_details as $key => $value ) {
					$context['wall_post'][ $key ] = $value;
				}
			}

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
	ProfileWallNewPost::get_instance();

endif;
