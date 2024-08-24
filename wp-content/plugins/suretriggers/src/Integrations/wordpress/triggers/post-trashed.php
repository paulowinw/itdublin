<?php
/**
 * PostTrashed.
 * php version 5.6
 *
 * @category PostTrashed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use WP_Post;

if ( ! class_exists( 'PostTrashed' ) ) :


	/**
	 * PostTrashed
	 *
	 * @category PostTrashed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 */
	class PostTrashed {


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
		public $trigger = 'trashed_post';

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
		 *
		 * @return array
		 */
		public function register( $triggers ) {

			$triggers[ $this->integration ][ $this->trigger ] = [
				'label'         => __( 'Post/Page/CPT is moved to Trash', 'suretriggers' ),
				'action'        => $this->trigger,
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;

		}


		/**
		 * Trigger listener
		 *
		 * @param int $post_id trashed post id.
		 *
		 * @return void
		 */
		public function trigger_listener( $post_id ) {

			$context                = (array) get_post( $post_id );
			$user                   = get_userdata( (int) $context['post_author'] );
			$context['post_author'] = ( property_exists( $user, 'user_nicename' ) ) ? $user->user_nicename : $user->user_email;
			$context['post']        = $post_id;
			$post                   = get_post( $post_id );
			if ( $post instanceof WP_Post ) {
				$taxonomies = get_object_taxonomies( $post, 'objects' );
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
					foreach ( $taxonomies as $taxonomy => $taxonomy_object ) {
						$terms = get_the_terms( $post_id, $taxonomy );
						if ( ! empty( $terms ) && is_array( $terms ) ) {
							foreach ( $terms as $term ) {
								$context[ $taxonomy ] = $term->name;
							}
						}
					}
				}
			}
			$custom_metas            = get_post_meta( $post_id );
			$context['custom_metas'] = $custom_metas;
			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);

		}


	}


	PostTrashed::get_instance();

endif;




