<?php
/**
 * NewProfileCreated.
 * php version 5.6
 *
 * @category NewProfileCreated
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

if ( ! class_exists( 'NewProfileCreated' ) ) :

	/**
	 * NewProfileCreated
	 *
	 * @category NewProfileCreated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class NewProfileCreated {


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
		public $trigger = 'voxel_new_profile_created';

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
				'label'         => __( 'New Profile Created', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/post-types/profile/post:submitted',
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
			
			if ( ! property_exists( $event, 'post' ) ) {
				return;
			}
			$context = Voxel::get_post_fields( $event->post->get_id() );
			$user    = get_userdata( $event->post->get_author() );
			if ( $user ) {
				$user_data                       = (array) $user->data;
				$context['profile_display_name'] = $user_data['display_name'];
				$context['profile_email']        = $user_data['user_email'];
				$context['profile_user_id']      = $user_data['ID'];
				$context['profile_id']           = $event->post->get_id();
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
	NewProfileCreated::get_instance();

endif;