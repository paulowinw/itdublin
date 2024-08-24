<?php
/**
 * NewPost.
 * php version 5.6
 *
 * @category NewPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\Voxel\Voxel;
use Exception;

/**
 * NewPost
 *
 * @category NewPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class NewPost extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_create_new_post';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create New Post', 'suretriggers' ),
			'action'   => 'voxel_create_new_post',
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
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$user_email = $selected_options['post_author_email'];

		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		// Get the post type.
		$post_type = isset( $selected_options['voxel_post_type'] ) && '' !== $selected_options['voxel_post_type'] ? $selected_options['voxel_post_type'] : 'post';

		$post_fields = [];
		foreach ( $selected_options['field_row_repeater'] as $key => $field ) {
			$field_name                 = $field['value']['name'];
			$value                      = trim( $selected_options['field_row'][ $key ][ $field_name ] );
			$post_fields[ $field_name ] = $value;
		}   
		
		$data = [
			'post_type'   => $post_type,
			'post_title'  => $post_fields['title'],
			'post_status' => isset( $selected_options['post_status'] ) && '' !== $selected_options['post_status'] ? $selected_options['post_status'] : 'draft',
		];

		if ( is_email( $user_email ) ) {
			$user                = get_user_by( 'email', $user_email );
			$user_id             = $user ? $user->ID : 1;
			$data['post_author'] = $user_id;
		}
		$post_id = wp_insert_post( $data );

		// Update Collection fields.
		Voxel::voxel_update_post( $post_fields, $post_id, $post_type );

		return [
			'success'  => true,
			'message'  => esc_attr__( 'Post created successfully', 'suretriggers' ),
			'post_id'  => $post_id,
			'post_url' => get_permalink( $post_id ),
		];
	}

}

NewPost::get_instance();
