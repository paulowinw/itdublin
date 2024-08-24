<?php
/**
 * GetPostMetadata.
 * php version 5.6
 *
 * @category GetPostMetadata
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
 * GetPostMetadata
 *
 * @category GetPostMetadata
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetPostMetadata extends AutomateAction {

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
	public $action = 'get_post_metadata';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Post Metadata', 'suretriggers' ),
			'action'   => 'get_post_metadata',
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
		$response = [];
		$post_id  = $selected_options['post_id'];
		$meta_key = isset( $selected_options['meta_key'] ) ? $selected_options['meta_key'] : '';
		
		$post = get_post( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Post not found for the specified Post ID.' );
		}
		$response = get_object_vars( $post );
		if ( '' !== $meta_key ) {
			$post_meta_data = get_post_meta( $post_id, $meta_key, true );
			if ( '' == $post_meta_data ) {
				return [
					'success' => 'false',
					'message' => 'No metadata found for specified meta key',
				];
			}
			$response['post_meta'] = [
				'meta_key'   => $meta_key,
				'meta_value' => $post_meta_data,
			];
		} else {
			$post_meta_data        = get_post_meta( $post_id );
			$response['post_meta'] = $post_meta_data;
		}

		return $response;

	}
}

GetPostMetadata::get_instance();
