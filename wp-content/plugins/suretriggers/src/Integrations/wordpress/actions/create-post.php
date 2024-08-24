<?php
/**
 * CreatePost.
 * php version 5.6
 *
 * @category CreatePost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;

/**
 * CreatePost
 *
 * @category CreatePost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreatePost extends AutomateAction {

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
	public $action = 'create_update_post';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Post: Create a Post', 'suretriggers' ),
			'action'   => 'create_update_post',
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
	 * @return bool|object
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$result_arr = [];
		foreach ( $fields as $field ) {
			if ( isset( $field['name'] ) && isset( $selected_options[ $field['name'] ] ) && ( trim( wp_strip_all_tags( $selected_options[ $field['name'] ] ) ) !== '' ) ) {
				if ( 'post_content' === $field['name'] ) {
					$html_content                 = $selected_options[ $field['name'] ];
					$patterns                     = [
						'/<head\b[^>]*>.*?<\/head>/is',
						'/<script\b[^>]*>.*?<\/script>/is',
						'/<style\b[^>]*>.*?<\/style>/is',
					];
					$html_content                 = preg_replace( $patterns, '', $html_content );
					$result_arr[ $field['name'] ] = $html_content;
				} else {
					$result_arr[ $field['name'] ] = $selected_options[ $field['name'] ];
				}
			}           
		}

		$meta_array = [];

		if ( ! empty( $selected_options['post_meta'] ) ) {
			foreach ( $selected_options['post_meta'] as $meta ) {
				$meta_key                = $meta['metaKey'];
				$meta_value              = $meta['metaValue'];
				$meta_array[ $meta_key ] = $meta_value;
			}
			$result_arr['meta_input'] = $meta_array;
		}
		
		if ( isset( $selected_options['post_url'] ) && ! empty( $selected_options['post_url'] ) ) {
			$url         = $selected_options['post_url'];
			$parts       = explode( '/', $url );
			$parts       = array_values( array_filter( $parts ) );
			$slug        = $parts[ count( $parts ) - 1 ]; 
			$post_exists = get_page_by_path( $slug, OBJECT, $selected_options['post_type'] );
			if ( $post_exists ) {
				$result_arr['ID'] = $post_exists->ID;
				wp_update_post( $result_arr );
				return get_post( $post_exists->ID );
			} else {
				throw new Exception( 'The URL entered is incorrect. Please provide the correct URL for the post' );
			}       
		}
		
		$post_id = wp_insert_post( $result_arr );

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			$this->set_error(
				[
					'post_data' => $result_arr,
					'msg'       => __( 'Failed to insert post!', 'suretriggers' ),
				]
			);
			return false;
		}

		return get_post( $post_id );
	}
}

CreatePost::get_instance();
