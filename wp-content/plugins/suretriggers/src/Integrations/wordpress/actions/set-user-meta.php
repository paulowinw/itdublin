<?php
/**
 * SetUserMeta.
 * php version 5.6
 *
 * @category SetUserMeta
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
 * SetUserMeta
 *
 * @category SetUserMeta
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetUserMeta extends AutomateAction {

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
	public $action = 'set_user_meta';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'User: Set User meta', 'suretriggers' ),
			'action'   => 'set_user_meta',
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
		if ( empty( $selected_options['user_meta_operations'] ) ) {
			throw new Exception( 'No user meta operation found' );
		}

		$dynamic_response = [];

		foreach ( $selected_options['user_meta_operations'] as $meta ) {
			$email   = $meta['user_email'];
			$user_id = 0;

			$user = get_user_by( 'email', $email );
			if ( $user ) {
				$user_id = $user->ID;
			}

			$opr          = $meta['operation'];
			$meta_key     = $meta['meta_key'];
			$meta_value   = $meta['meta_value'];
			$is_meta_json = json_decode( $meta_value, true );
			if ( null !== $is_meta_json ) {
				$meta_value = $is_meta_json;
			}
			
			$value = get_user_meta( $user_id, $meta_key, true );
			switch ( $opr ) {
				case 'set':
					$value = $meta_value;
					break;
				case 'insert':
					if ( empty( $value ) ) {
						$value = $meta_value;
					} else {
						if ( is_array( $value ) ) {
							$value[] = $meta_value;
						} else {
							$value = [ $value, $meta_value ];
						}
					}
					
					break;
				case 'increment':
					$value += $meta_value;
					break;
				case 'decrement':
					$value -= $meta_value;
					break;
			}

			update_user_meta( $user_id, $meta_key, $value );

			$dynamic_response[] = [
				'user_id'    => $user_id,
				'meta_key'   => $meta_key,
				'meta_value' => $value,
				'operation'  => $opr,
			];
		}

		return $dynamic_response;
	}
}

SetUserMeta::get_instance();
