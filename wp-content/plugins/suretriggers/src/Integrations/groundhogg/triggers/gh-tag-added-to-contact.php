<?php
/**
 * GhTagAddedToContact.
 * php version 5.6
 *
 * @category GhTagAddedToContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Groundhogg\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;

if ( ! class_exists( 'GhTagAddedToContact' ) ) :

	/**
	 * GhTagAddedToContact
	 *
	 * @category GhTagAddedToContact
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class GhTagAddedToContact {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Groundhogg';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'gh_tag_added_to_contact';

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
				'label'         => __( 'Tag Added to Contact', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'groundhogg/contact/tags_applied',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 2,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $class Class.
		 * @param array  $tag_id Tag ID.
		 * @return void
		 */
		public function trigger_listener( $class, $tag_id ) {
			
			if ( ! class_exists( '\Groundhogg\DB\Tags' ) ) {
				return;
			}

			$tags    = new \Groundhogg\DB\Tags();
			$name    = $tags->get_tag_by( 'tag_id', $tag_id[0] );
			$context = $name;
			if ( is_object( $context ) ) {
				$context = get_object_vars( $context );
			}
			$context['tag_id'] = $tag_id[0];
			
			if ( method_exists( $class, 'get_data' ) ) {
				$user_data          = $class->get_data();
				$context['contact'] = $user_data;
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
	GhTagAddedToContact::get_instance();

endif;
