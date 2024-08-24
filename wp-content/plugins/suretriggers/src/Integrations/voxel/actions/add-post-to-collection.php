<?php
/**
 * AddPostToCollection.
 * php version 5.6
 *
 * @category AddPostToCollection
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

/**
 * AddPostToCollection
 *
 * @category AddPostToCollection
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostToCollection extends AutomateAction {

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
	public $action = 'voxel_add_post_to_collection';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Post to Collection', 'suretriggers' ),
			'action'   => 'voxel_add_post_to_collection',
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
		$collection_id = (int) $selected_options['collection_post_id'];
		$post_id       = $selected_options['post_id'];

		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		// In case multiple posts, explode the post ID.
		$post_ids = explode( ',', $post_id );
		$items    = [];
		// Loop through the post IDs.
		foreach ( $post_ids as $c_post_id ) {
			// Get the post.
			$post = \Voxel\Post::force_get( $c_post_id );
			if ( ! $post ) {
				throw new Exception( 'Post not found' );
			}

			// Get the collection.
			$collection = \Voxel\Post::force_get( $collection_id );
			if ( ! (
				$collection
				&& $collection->post_type
				&& $collection->get_status() === 'publish'
				&& $collection->post_type->get_key() === 'collection'
			) ) {
				throw new Exception( 'Collection not found' );
			}

			// Get the items field.
			$field = $collection->get_field( 'items' );
			$items = $field->get_value();
			// If items are not available, then set as empty array.
			if ( ! $items ) {
				$items = [];
			}
			// If the post is already in the collection, then skip.
			if ( in_array( $c_post_id, $items, true ) ) {
				continue;
			}
			// Add the post to the items.
			$items[] = (int) $c_post_id;
			$items   = array_unique( $items );
			$field->set_value( $items );
		}

		return [
			'success'          => true,
			'message'          => esc_attr__( 'Post added to collection successfully', 'suretriggers' ),
			'post_id'          => $post_id,
			'collection_id'    => $collection_id,
			'collection_url'   => get_permalink( $collection_id ),
			'collection_items' => wp_json_encode( $items ),
		];
	}

}

AddPostToCollection::get_instance();
