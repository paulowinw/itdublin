<?php
/**
 * GetPostByID.
 * php version 5.6
 *
 * @category GetPostByID
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
 * GetPostByID
 *
 * @category GetPostByID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetPostByID extends AutomateAction {

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
	public $action = 'get_post_by_id';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Post by ID', 'suretriggers' ),
			'action'   => 'get_post_by_id',
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
		
		$post = get_post( $post_id );

		if ( ! $post ) {
			throw new Exception( 'Post not found for the specified Post ID.' );
		}
		
		$post_metas = get_post_meta( $post_id );

		$response               = get_object_vars( $post );
		$response['post_metas'] = $post_metas;

		$current_taxonomies = get_object_taxonomies( $post, 'objects' );
		$taxonomies         = [];
		foreach ( $current_taxonomies as $tax_title => $tax ) {
			$terms = get_the_terms( $post, $tax_title );
			if ( is_array( $terms ) ) {
				foreach ( $terms as $key => $term ) {
					$taxonomies[ $tax_title ][] = [
						'name'    => $term->name,
						'slug'    => $term->slug,
						'term_id' => $term->term_id,
					];
				}
			}
		}
		$response['taxonomies'] = $taxonomies;

		$post_thumbnail_id = get_post_thumbnail_id( $post->ID );
		if ( $post_thumbnail_id ) {
			$featured_image = wp_get_attachment_image_src( $post_thumbnail_id, 'full' );
			if ( $featured_image ) {
				$response['featured_image']    = $featured_image[0];
				$response['featured_image_id'] = $post_thumbnail_id;
			}
		}

		return $response;

	}
}

GetPostByID::get_instance();
