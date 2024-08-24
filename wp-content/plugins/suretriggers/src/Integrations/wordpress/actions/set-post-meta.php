<?php
/**
 * SetPostMeta.
 * php version 5.6
 *
 * @category SetPostMeta
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
 * SetPostMeta
 *
 * @category SetPostMeta
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class SetPostMeta extends AutomateAction {

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
	public $action = 'set_post_meta';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Post: Set Post meta', 'suretriggers' ),
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
	 * @param array $selected_options selected_options.
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['post_meta_operations'] ) ) {
			$this->set_error(
				[
					'msg' => __( 'No post meta operation found!', 'suretriggers' ),
				]
			);
			return false;
		}

		$dynamic_response = [];

		foreach ( $selected_options['post_meta_operations'] as $meta ) {
			$opr = $meta['operation'];
			if ( is_array( $meta['post'] ) ) {
				$post = $meta['post']['value'];
			} else {
				$post = $meta['post'];
			}
			$post_id    = $post;
			$meta_key   = $meta['meta_key'];
			$meta_value = $meta['meta_value'];

			$value = get_post_meta( $post_id, $meta_key, true );

			switch ( $opr ) {
				case 'set':
					$value = $meta_value;
					break;
				case 'insert':
					if ( is_array( $value ) ) {
						$value[] = $meta_value;
					} else {
						$value .= $meta_value;
					}
					break;
				case 'increment':
					$value += $meta_value;
					break;
				case 'decrement':
					$value -= $meta_value;
					break;
			}

			update_post_meta( $post_id, $meta_key, $value );

			$dynamic_response[] = [
				'set_post_id'         => $post_id,
				'set_post_meta_key'   => $meta_key,
				'set_post_meta_value' => $value,
			];
		}

		return $dynamic_response;
	}
}

SetPostMeta::get_instance();
