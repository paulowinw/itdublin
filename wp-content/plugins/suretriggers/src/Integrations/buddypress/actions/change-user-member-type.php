<?php
/**
 * ChangeUserMemberType.
 * php version 5.6
 *
 * @category ChangeUserMemberType
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyPress\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * ChangeUserMemberType
 *
 * @category ChangeUserMemberType
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ChangeUserMemberType extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'change_user_member_type';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( "Set the user's member type to a specific type", 'suretriggers' ),
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
	 * @throws Exception Exception.
	 *
	 * @return bool|array|void 
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$members = $selected_options['bp_member_type'];
		
		$member_type = [];
		foreach ( $members as $value ) {
			$member_type[] = $value['value'];
			if ( function_exists( 'bp_get_member_type_object' ) ) {
				if ( ! bp_get_member_type_object( $value['value'] ) ) {
					return;
				}
			}
		}       

		/*
		* If an invalid member type is passed, someone's doing something
		* fishy with the POST request, so we can fail silently.
		*/
		if ( function_exists( 'bp_set_member_type' ) ) {
			if ( bp_set_member_type( $user_id, $member_type ) ) {
				if ( function_exists( 'bp_get_member_type' ) ) {
					$member_type_arr        = bp_get_member_type( $user_id, false );
					$context['member_type'] = $member_type_arr;
				}
				$context['user'] = WordPress::get_user_context( $user_id );
				return $context;         
			}
		}
	}
}

ChangeUserMemberType::get_instance();
