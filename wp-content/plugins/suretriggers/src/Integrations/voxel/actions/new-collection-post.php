<?php
/**
 * NewCollectionPost.
 * php version 5.6
 *
 * @category NewCollectionPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use SureTriggers\Integrations\Voxel\Voxel;

/**
 * NewCollectionPost
 *
 * @category NewCollectionPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class NewCollectionPost extends AutomateAction {

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
	public $action = 'voxel_create_new_collection_post';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create New Collection Post', 'suretriggers' ),
			'action'   => 'voxel_create_new_collection_post',
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
		$user_id = $selected_options['wp_user_id'];
		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		if ( is_email( $user_id ) ) {
			$user                = get_user_by( 'email', $user_id );
			$user_id             = $user ? $user->ID : 1;
			$data['post_author'] = $user_id;
		}
		$post_fields = [];
		foreach ( $selected_options['field_row_repeater'] as $key => $field ) {
			$field_name                 = $field['value']['name'];
			$value                      = trim( $selected_options['field_row'][ $key ][ $field_name ] );
			$post_fields[ $field_name ] = $value;
		}   
		
		$data          = [
			'post_type'   => 'collection',
			'post_title'  => $post_fields['title'],
			'post_status' => 'publish',
			'post_author' => $user_id,
		];
		$collection_id = wp_insert_post( $data );

		// Update Collection fields.
		Voxel::voxel_update_post( $post_fields, $collection_id, 'collection' );

		return [
			'success'        => true,
			'message'        => esc_attr__( 'Collection created successfully', 'suretriggers' ),
			'collection_id'  => $collection_id,
			'collection_url' => get_permalink( $collection_id ),
		];
	}

}

NewCollectionPost::get_instance();
