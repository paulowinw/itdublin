<?php
/**
 * PublishJob.
 * php version 5.6
 *
 * @category PublishJob
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\WPJobManager\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * PublishJob
 *
 * @category PublishJob
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class PublishJob {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WPJobManager';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wpjob_manager_transition_post_status';

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
			'label'         => __( 'User publishes a job of a type', 'suretriggers' ),
			'action'        => $this->trigger,
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
	 * @param int    $new_status new_status.
	 * @param int    $old_status old_status.
	 * @param object $post post.
	 *
	 * @return void
	 */
	public function trigger_listener( $new_status, $old_status, $post ) {
		// Bail if post status hasn't changed.
		if ( $old_status === $new_status ) {
			return;
		}

		// Bail if post type is not a job.
		// Bail if post is already published.
		// Bail if post is not published.
		if ( isset( $post->post_type ) ) {
			if ( 'job_listing' !== $post->post_type || 'publish' === $old_status || 'publish' !== $new_status ) {
				return;
			}       
		}
		
		$context = [];
		if ( isset( $post->ID ) ) {
			$user_id      = absint( get_post_field( 'post_author', $post->ID ) );
			$terms        = get_the_terms( $post->ID, 'job_listing_type' );
			$term_id      = ( empty( $terms ) || is_wp_error( $terms ) ) ? [] : wp_list_pluck( $terms, 'term_id' );
			$post_content = WordPress::get_post_context( $post->ID );
			$post_meta    = WordPress::get_post_meta( $post->ID );
			$context      = array_merge( $post_content, [ $post_meta ], WordPress::get_user_context( $user_id ) );
			foreach ( $context as $key => $job ) {
				$newkey = str_replace( 'post', 'wpjob', $key );
				unset( $context[ $key ] );
				$context[ $newkey ] = $job;
			}
			if ( ! empty( $term_id ) ) {
				$context['job_type'] = $term_id[0];
			}
		}
		
		

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

PublishJob::get_instance();
