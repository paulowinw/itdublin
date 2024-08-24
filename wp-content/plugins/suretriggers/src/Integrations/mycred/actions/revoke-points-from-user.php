<?php
/**
 * RevokePointsFromUser.
 * php version 5.6
 *
 * @category RevokePointsFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MyCred\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * RevokePointsFromUser
 *
 * @category RevokePointsFromUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class RevokePointsFromUser extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MyCred';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'revoke_points_from_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Revoke Badge', 'suretriggers' ),
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
	 *
	 * @return array|bool
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$point_type = $selected_options['point_type'];
		$points     = $selected_options['points'];

		if ( empty( $point_type ) || empty( $user_id ) || ! function_exists( 'mycred_get_types' ) || ! function_exists( 'mycred_subtract' ) || ! function_exists( 'mycred_add' ) ) {
			return false;
		}

		$description = ! empty( $selected_options['description'] ) ? $selected_options['description'] : __( 'Awarded by SureTriggers', 'suretriggers' );

		if ( '-1' == $point_type ) {
			$point_types = mycred_get_types();
			if ( is_array( $point_types ) && ! empty( $point_types ) ) {
				foreach ( $point_types as $key => $value ) {
					mycred_subtract( $value, absint( $user_id ), absint( - $points ), $description, '', '', $key );
				}
			}
		} else {
			mycred_subtract( 'Points', absint( $user_id ), absint( - $points ), $description, '', '', $point_type );
		}
		return array_merge(
			WordPress::get_user_context( $user_id ),
			[
				'points'      => $points,
				'point_type'  => $point_type,
				'description' => $description,
			]
		);
	}
}

RevokePointsFromUser::get_instance();
