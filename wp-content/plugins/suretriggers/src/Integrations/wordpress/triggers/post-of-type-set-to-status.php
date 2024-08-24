<?php
/**
 * PostSetToStatus.
 * php version 5.6
 *
 * @category PostSetToStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use WP_Post;

/**
 * PostSetToStatus
 *
 * @category PostSetToStatus
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PostSetToStatus {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';


	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'post_of_type_set_to_status';

	use SingletonLoader;


	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User\'s specific type of post is set to a status', 'suretriggers' ),
			'action'        => 'post_of_type_set_to_status',
			'common_action' => 'transition_post_status',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 3,
		];

		return $triggers;

	}

	/**
	 * Trigger listener
	 *
	 * @param string $new_status new status.
	 * @param string $old_status old status.
	 * @param object $post post.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $new_status, $old_status, $post ) {

		if ( $old_status === $new_status ) {
			return;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			add_action( "rest_after_insert_{$post->post_type}", [ $this, 'trigger_handler' ], 10, 3 );
			return;
		}

		$this->trigger_handler( $post, '', '' );

	}

	/**
	 * REST request listener
	 *
	 * @param object|WP_Post         $post Inserted or updated post object.
	 * @param string|WP_REST_Request $request Request object.
	 * @param string|bool            $creating True when creating a post, false when updating.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_handler( $post, $request, $creating ) {

		$context                = WordPress::get_post_context( $post->ID );
		$context['post_type']   = $post->post_type;
		$context['post_status'] = $post->post_status;
		if ( $post instanceof WP_Post ) {
			$taxonomies = get_object_taxonomies( $post, 'objects' );
			if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy => $taxonomy_object ) {
					$terms = get_the_terms( $post->ID, $taxonomy );
					if ( ! empty( $terms ) && is_array( $terms ) ) {
						foreach ( $terms as $term ) {
							$context[ $taxonomy ] = $term->name;
						}
					}
				}
			}
		}
		$custom_metas            = get_post_meta( $post->ID );
		$context['custom_metas'] = $custom_metas;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);

	}
}

PostSetToStatus::get_instance();
