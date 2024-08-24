<?php
/**
 * UserMetaKeyUpdated.
 * php version 5.6
 *
 * @category UserMetaKeyUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserMetaKeyUpdated
 *
 * @category UserMetaKeyUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserMetaKeyUpdated {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'updated_user_meta';

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
			'label'         => __( 'User\'s specific meta key is updated', 'suretriggers' ),
			'action'        => 'updated_user_meta',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 4,
		];

		return $triggers;
	}


	/**
	 * Trigger listener
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $_meta_value Metadata value.
	 *
	 * @return void
	 */
	public function trigger_listener( $meta_id, $object_id, $meta_key, $_meta_value ) {

		$context               = WordPress::get_user_context( $object_id );
		$context['meta_key']   = $meta_key;
		$context['meta_value'] = $_meta_value;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}


UserMetaKeyUpdated::get_instance();
