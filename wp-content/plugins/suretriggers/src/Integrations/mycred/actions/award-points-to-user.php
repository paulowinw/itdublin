<?php
/**
 * AwardPointsToUser.
 * php version 5.6
 *
 * @category AwardPointsToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\MyCred\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * AwardPointsToUser
 *
 * @category AwardPointsToUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AwardPointsToUser extends AutomateAction {


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
	public $action = 'award_points_to_user';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Award Points', 'suretriggers' ),
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
	 * @return array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$points     = $selected_options['points'];
		$point_type = $selected_options['point_type'];

		if ( empty( $points ) || empty( $user_id ) ) {
			return false;
		}

		if ( ! is_numeric( $points ) ) {
			throw new Exception( 'Points should be a numeric value.' );
		}

		$description = ! empty( $selected_options['description'] ) ? $selected_options['description'] : __( 'Awarded by SureTriggers', 'suretriggers' );

		mycred_add( 'Points', absint( $user_id ), $points, $description, '', '', $point_type );

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

AwardPointsToUser::get_instance();
