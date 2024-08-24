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

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use SureTriggers\Integrations\Voxel\Voxel;

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
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_get_post_by_id';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Post By ID', 'suretriggers' ),
			'action'   => 'voxel_get_post_by_id',
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
		$post_id = (int) $selected_options['post_id'];
		$post    = get_post( $post_id );

		if ( ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		if ( ! $post ) {
			throw new Exception( 'Post not found' );
		}

		// Get the post fields.
		$post_fields = [
			'post_id'        => $post->ID,
			'post_title'     => $post->post_title,
			'post_content'   => $post->post_content,
			'post_status'    => $post->post_status,
			'post_author_id' => $post->post_author,
			'post_permalink' => get_permalink( $post->ID ),
			'post_type'      => get_post_type( $post->ID ),
		];

		// Set the fields.
		$fields = [];
		// Get the post fields.
		$wp_post = \Voxel\Post::force_get( $post_id );
		if ( $wp_post ) {
			// Loop through each field and add to the simple entry.
			foreach ( $wp_post->get_fields() as $field ) {
				$key = $field->get_key();

				if ( $field->get_type() === 'taxonomy' ) {
					$content = join(
						', ',
						array_map(
							function( $term ) {
								return $term->get_label();
							},
							$field->get_value()
						)
					);
				} elseif ( $field->get_type() === 'location' ) {
					$content = isset( $field->get_value()['address'] ) ? $field->get_value()['address'] : null;
				} else {
					$content = $field->get_value();
				}

				$fields[ $key ] = is_array( $content ) ? wp_json_encode( $content ) : $content;
			}

			// If fields are available, then add to the simple entry.
			if ( ! empty( $fields ) ) {
				$post_fields['all_fields'] = $fields;

				// Loop through each field and add to the simple entry.
				foreach ( $fields as $key => $value ) {
					$post_fields[ 'field_' . $key ] = $value;
				}
			}
		}
		$author = get_user_by( 'id', $post->post_author );
		if ( $author ) {
			$user = get_userdata( $author->ID );
			if ( ! empty( $user ) ) {
				$user_data                        = (array) $user->data;
				$post_fields['user_id']           = $user_data['ID'];
				$post_fields['user_name']         = $user_data['user_nicename'];
				$post_fields['user_display_name'] = $user_data['display_name'];
				$post_fields['user_email']        = $user_data['user_email'];
			}
		}

		return $post_fields;
	}

}

GetPostByID::get_instance();
