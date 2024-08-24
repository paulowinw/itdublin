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

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use WP_Post;

if ( ! class_exists( 'UpdatePost' ) ) :


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
	class UpdatePost {


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
		public $trigger = 'post_updated';

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
				'label'         => __( 'User updates a post', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 3,
			];

			return $triggers;

		}


		/**
		 * Trigger listener.
		 *
		 * @param int    $post_ID post id.
		 * @param object $post post.
		 * @param object $update update.
		 * @return void
		 */
		public function trigger_listener( $post_ID, $post, $update ) {  
			if ( ! isset( $post->post_status ) ) {
				return;
			}
			if ( 'draft' !== $post->post_status && ! wp_is_post_revision( $post_ID ) && ! wp_is_post_autosave( $post_ID ) ) {
				$user_id = ap_get_current_user_id();
				$context = WordPress::get_post_context( $post_ID );
				if ( $post instanceof WP_Post ) {
					$taxonomies = get_object_taxonomies( $post, 'objects' );
					if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
						foreach ( $taxonomies as $taxonomy => $taxonomy_object ) {
							$terms = get_the_terms( $post_ID, $taxonomy );
							if ( ! empty( $terms ) && is_array( $terms ) ) {
								foreach ( $terms as $term ) {
									$context[ $taxonomy ] = $term->name;
								}
							}
						}
					}
				}
				$context                 = array_merge( $context, WordPress::get_user_context( $user_id ) );
				$context['post']         = $post_ID;
				$custom_metas            = get_post_meta( $post_ID );
				$context['custom_metas'] = $custom_metas;

				AutomationController::sure_trigger_handle_trigger(
					[
						'trigger' => $this->trigger,
						'context' => $context,
					]
				);
			} else {
				return;
			}
		}
	}


	UpdatePost::get_instance();

endif;
