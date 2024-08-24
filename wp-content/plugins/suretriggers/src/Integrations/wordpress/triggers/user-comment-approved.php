<?php
/**
 * UserCommentApproved.
 * php version 5.6
 *
 * @category UserCommentApproved
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
 * Class UserCommentApproved
 *
 * @category UserCommentApproved
 * @package SureTriggers\Integrations\Wordpress\Triggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class UserCommentApproved {


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
	public $trigger = 'transition_comment_status';

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
			'label'         => __( 'User\'s comment on a post is approved', 'suretriggers' ),
			'action'        => 'transition_comment_status',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 3,
		];

		return $triggers;

	}

	/**
	 * Trigger listener
	 *
	 * @param string $new_status New Status.
	 * @param string $old_status Old Status.
	 * @param array  $comment Comment.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function trigger_listener( $new_status, $old_status, $comment ) {
		if ( 'approved' !== $new_status ) {
			return;
		}

		if ( is_object( $comment ) ) {
			$comment = get_object_vars( $comment );
		}
		if ( ! isset( $comment['comment_post_ID'] ) ) {
			return;
		}

		$post = get_post( absint( $comment['comment_post_ID'] ) );

		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! isset( $comment['user_id'] ) ) {
			return;
		}

		$user_id = (int) $comment['user_id'];

		$context['comment_id']           = $comment['comment_post_ID'];
		$context['comment']              = $comment['comment_content'];
		$context['post']                 = $post->ID;
		$context['post_title']           = $post->post_title;
		$context['post_link']            = get_the_permalink( $post->ID );
		$context['comment_author']       = $comment['comment_author'];
		$context['comment_author_email'] = $comment['comment_author_email'];
		$context['comment_date']         = $comment['comment_date'];
		$context['post_author']          = get_the_author_meta( 'display_name', (int) $post->post_author );
		$context                         = array_merge( $context, WordPress::get_user_context( $user_id ) );

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
UserCommentApproved::get_instance();
