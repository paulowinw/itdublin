<?php
/**
 * FindUserMetaByKey.
 * php version 5.6
 *
 * @category FindUserMetaByKey
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WordPress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * FindUserMetaByKey
 *
 * @category FindUserMetaByKey
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class FindUserMetaByKey extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'find_user_meta_by_key';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User: Find User meta by key', 'suretriggers' ),
			'action'   => 'find_user_meta_by_key',
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
	 * @param array $selected_options selected_options.
	 * @return array|bool
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$dynamic_response = [];
		if ( empty( $user_id ) ) {
			$email = $selected_options['wp_user_email'];
			$user  = get_user_by( 'email', $email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}
		if ( ! empty( $selected_options['meta_key'] ) ) {
			$meta_key = $selected_options['meta_key'];
		} else {
			$meta_key = '';
		}

		if ( '' !== $meta_key ) {
			$meta_value         = get_user_meta( $user_id, $meta_key, true );
			$dynamic_response[] = [
				'user_id'    => $user_id,
				'meta_key'   => $meta_key,
				'meta_value' => $meta_value,
			];
			
		}
		return $dynamic_response;
	}
}

FindUserMetaByKey::get_instance();
