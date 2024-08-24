<?php
/**
 * ProfileReviewed.
 * php version 5.6
 *
 * @category ProfileReviewed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

if ( ! class_exists( 'ProfileReviewed' ) ) :

	/**
	 * ProfileReviewed
	 *
	 * @category ProfileReviewed
	 * @package  SureTriggers
	 * @author   BSF <username@example.com>
	 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
	 * @link     https://www.brainstormforce.com/
	 * @since    1.0.0
	 *
	 * @psalm-suppress UndefinedTrait
	 */
	class ProfileReviewed {


		/**
		 * Integration type.
		 *
		 * @var string
		 */
		public $integration = 'Voxel';


		/**
		 * Trigger name.
		 *
		 * @var string
		 */
		public $trigger = 'voxel_profile_reviewed';

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
				'label'         => __( 'Profile Reviewed', 'suretriggers' ),
				'action'        => $this->trigger,
				'common_action' => 'voxel/app-events/post-types/profile/review:created',
				'function'      => [ $this, 'trigger_listener' ],
				'priority'      => 10,
				'accepted_args' => 1,
			];

			return $triggers;
		}

		/**
		 * Trigger listener
		 *
		 * @param object $event Event.
		 * @return void
		 */
		public function trigger_listener( $event ) {
			if ( ! property_exists( $event, 'post' ) || ! class_exists( 'Voxel\Timeline\Status' )
			|| ! function_exists( 'Voxel\Timeline\prepare_status_json' ) || ! class_exists( 'Voxel\Post_Type' ) ) {
				return;
			}
			$context = [];
			// Get the review details.
			$args           = [
				'post_id' => $event->post->get_id(),
			];
			$statuses       = \Voxel\Timeline\Status::query( $args );
			$review_details = \Voxel\Timeline\prepare_status_json( $statuses[0] );
			foreach ( (array) $review_details as $key => $value ) {
				if ( 'user_can_edit' == $key || 'publisher' == $key || 'user_can_edit' == $key || 'user_can_moderate' == $key ) {
					continue;
				}
				if ( 'files' === $key ) {
					$value = wp_json_encode( $value );
				} elseif ( 'reviews' === $key ) {
					$review_ratings   = isset( $value['ratings'] ) && is_array( $value['ratings'] ) ? $value['ratings'] : [];
					$value['ratings'] = [];
					$type             = \Voxel\Post_Type::get( 'profile' );
				
					if ( ! empty( $review_ratings ) ) {
						$rating_levels = $type->reviews->get_rating_levels();
						$categories    = $type->reviews->get_categories();
				
						foreach ( $categories as $category ) {
							$category_key   = $category['key'];
							$category_label = strtolower( $category['label'] );
				
							if ( isset( $review_ratings[ $category_key ] ) && $category_label ) {
								foreach ( $rating_levels as $rating_level ) {
									if ( $review_ratings[ $category_key ] === $rating_level['score'] ) {
										$value['ratings'][ $category_label ] = $rating_level['label'];
										break;
									}
								}
							}
						}
					}
				} else {
					$key = 'review_' . $key;
				}
				$context[ $key ] = $value;
			}
			if ( ! empty( $context ) && ! empty( $context['review_user'] ) ) {
				unset( $context['review_user']['avatar'] );
			}

			AutomationController::sure_trigger_handle_trigger(
				[
					'trigger' => $this->trigger,
					'context' => $context,
				]
			);
		}
	}

	/**
	 * Ignore false positive
	 *
	 * @psalm-suppress UndefinedMethod
	 */
	ProfileReviewed::get_instance();

endif;
