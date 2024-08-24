<?php
/**
 * SetUserExtendedProfile.
 * php version 5.6
 *
 * @category SetUserExtendedProfile
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * SetUserExtendedProfile
 *
 * @category SetUserExtendedProfile
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetUserExtendedProfile extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_set_user_extended_profile';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Set User Extended Profile', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_id = email_exists( $selected_options['wp_user_email'] );

		if ( empty( $user_id ) ) {
			return;
		}

		if ( ! function_exists( 'xprofile_set_field_data' ) ) {
			return;
		}

		$user_fields_data = $selected_options['bb_field_data'];
		if ( ! empty( $user_fields_data ) ) {
			$context = [];
			foreach ( $user_fields_data as $user_selector ) {
				$field_id = $user_selector['bb_fields']['value'];
				if ( ! empty( $user_selector['custom_field_value'] ) ) {
					$value = $user_selector['custom_field_value'];
					if ( function_exists( 'xprofile_set_field_data' ) ) {
						xprofile_set_field_data( $field_id, $user_id, $value );
						if ( function_exists( 'xprofile_get_field_data' ) ) {
							$value = xprofile_get_field_data( $field_id, $user_id );
						}
						$context['user']                                 = WordPress::get_user_context( $user_id );
						$context[ $user_selector['bb_fields']['label'] ] = $value;
					}
				}
			}
			return $context;
		}
	}
}

SetUserExtendedProfile::get_instance();
