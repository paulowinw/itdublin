<?php
/**
 * UpdatePost.
 * php version 5.6
 *
 * @category UpdatePost
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
 * UpdatePost
 *
 * @category UpdatePost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UpdatePost extends AutomateAction {

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
	public $action = 'update_post';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Post: Update a Post', 'suretriggers' ),
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
	 * @return array|bool
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		$post_id = $selected_options['post'];

		$meta_array = [];

		if ( empty( $selected_options['post_details'] ) ) {
			throw new Exception( 'No post data found to update the post!' );
		}

		foreach ( $selected_options['post_details'] as $meta ) {
			$meta_key = $meta['post_key'];

			if ( ! empty( $meta_key['value'] ) ) {
				$meta_key = $meta_key['value'];
			}

			$meta_value = $meta['post_value'];
			if ( ! empty( $meta_value['value'] ) ) {
				$meta_value = $meta_value['value'];
			}

			$meta_array[ $meta_key ] = $meta_value;
		}

		// Update the user.
		if ( empty( $meta_array ) ) {
			$this->set_error(
				[
					'msg' => __( 'No post meta array found to update!', 'suretriggers' ),
				]
			);
			return false;
		}

		$meta_array['ID'] = $post_id;
		if ( ! empty( $meta_array['post_slug'] ) ) {
			$meta_array['post_name'] = $meta_array['post_slug'];
		}

		wp_update_post( $meta_array );

		return $meta_array;
	}
}

UpdatePost::get_instance();
