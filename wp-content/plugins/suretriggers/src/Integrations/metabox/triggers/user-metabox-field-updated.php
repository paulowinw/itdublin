<?php
/**
 * UserMetaboxFieldUpdated.
 * php version 5.6
 *
 * @category UserMetaboxFieldUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MetaBox\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'UserMetaboxFieldUpdated' ) ) :

	/**
	 * UserMetaboxFieldUpdated
	 *
	 * @category UserMetaboxFieldUpdated
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class UserMetaboxFieldUpdated {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'MetaBox';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'user_metabox_field_updated';

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
				'label'         => __( 'User Metabox Field Updated', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => [ 'added_user_meta', 'updated_user_meta' ],
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 4,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param int $meta_id Meta ID.
		 * @param int $object_id Object ID.
		 * @param int $meta_key Meta Key.
		 * @param int $meta_value Meta Value.
		 * @return void|bool
		 */
		public function trigger_listener( $meta_id, $object_id, $meta_key, $meta_value ) {

			if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
				return false;
			}

			$fields_allowed = array_keys( rwmb_get_object_fields( 'user', 'user' ) );

			if ( ! in_array( $meta_key, $fields_allowed, true ) ) {
				return false;
			}

			$context = [
				'field_id' => $meta_key,
				$meta_key  => $meta_value,
				'user'     => WordPress::get_user_context( $object_id ),
			];

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
	UserMetaboxFieldUpdated::get_instance();

endif;
