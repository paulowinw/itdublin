<?php
/**
 * UserProfilefieldUpdated.
 * php version 5.6
 *
 * @category UserProfilefieldUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Controllers\GlobalSearchController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * UserProfilefieldUpdated
 *
 * @category UserProfilefieldUpdated
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class UserProfilefieldUpdated {


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
	public $trigger = 'profile_update';

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
			'label'         => __( 'User\'s profile field is updated', 'suretriggers' ),
			'action'        => 'profile_update',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 2,
		];

		return $triggers;

	}

	/**
	 * Trigger listener
	 *
	 * @param int    $user_id user id.
	 * @param object $old_user_data old user data object.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $user_id, $old_user_data ) {

		// Setup vars.
		$fields = GlobalSearchController::get_instance()->search_user_field_options();

		$user_fields = wp_list_pluck( $fields['options'], 'value' );

		$new_user_data = get_userdata( $user_id );

		foreach ( $user_fields as $user_field ) {

			// Skip field if not updated.
			if ( $new_user_data->$user_field === $old_user_data->$user_field ) {
				continue;
			}

			$context                        = WordPress::get_user_context( $user_id );
			$context['profile_field']       = $user_field;
			$context['profile_field_value'] = $new_user_data->$user_field;

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);

		}
	}
}

/**
 * Ignore false positive
 *
 * @psalm-suppress UndefinedMethod
 */
UserProfilefieldUpdated::get_instance();
