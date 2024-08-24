<?php
/**
 * AddTopicInForum.
 * php version 5.6
 *
 * @category AddTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;

/**
 * AddTopicInForum
 *
 * @category AddTopicInForum
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddTopicInForum extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'bbPress';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'add_topic_to_forum';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Topic to Forum', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( ! class_exists( 'bbPress' ) ) {
			return;
		}
		if ( ! function_exists( 'bbp_get_topic_post_type' ) || ! function_exists( 'bbp_get_topic' ) || ! function_exists( 'bbp_get_forum' ) || ! function_exists( 'bbp_is_forum_category' ) || ! function_exists( 'bbp_get_reply_post_type' ) || ! function_exists( 'bbp_get_topic_reply_count' ) ) {
			return;
		}
		
		$user_id = $selected_options['wp_user_email'];
		if ( is_email( $user_id ) ) {
			$user = get_user_by( 'email', $user_id );
			if ( $user ) {
				$user_id = $user->ID;
			} 
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Please enter valid email address.', 'suretriggers' ),
			];

			return $error;
		}
		$forum_id          = $selected_options['forum'];
		$topic_title       = $selected_options['topic_title'];
		$topic_description = $selected_options['topic_content'];
		$anonymous_data    = 0;

		if ( ! empty( $forum_id ) ) {

			// Forum is a category.
			if ( bbp_is_forum_category( $forum_id ) ) {
				throw new Exception( 'Sorry, This forum is a category. No discussions can be created in this forum.' );
				
				// Forum is not a category.
			} else {

				// Forum is closed and user cannot access.
				if ( function_exists( 'bbp_is_forum_closed' ) && bbp_is_forum_closed( $forum_id ) && ! current_user_can( 'edit_forum', $forum_id ) ) {
					throw new Exception( 'Sorry, This forum has been closed to new discussions.' );
				}

				/**
				 * Added logic for group forum
				 * Current user is part of that group or not.
				 * We need to check manually because bbpress updating that caps only on group forum page and
				 * in API those conditional tag will not work.
				 */
				$is_member = false;
				$group_ids = [];
				if ( function_exists( 'bbp_get_forum_group_ids' ) ) {
					$group_ids = bbp_get_forum_group_ids( $forum_id );
					if ( ! empty( $group_ids ) ) {
						foreach ( $group_ids as $group_id ) {
							if ( function_exists( 'groups_is_user_member' ) && groups_is_user_member( $user_id, $group_id ) ) {
								$is_member = true;
								break;
							}
						}
					}
				}

				// Forum is private and user cannot access.
				if ( function_exists( 'bbp_is_forum_private' ) && bbp_is_forum_private( $forum_id ) && function_exists( 'bbp_is_user_keymaster' ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_private_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						throw new Exception( 'Sorry, This forum is private and you do not have the capability to read or create new discussions in it.' );
					}

					// Forum is hidden and user cannot access.
				} elseif ( function_exists( 'bbp_is_forum_hidden' ) && bbp_is_forum_hidden( $forum_id ) && function_exists( 'bbp_is_user_keymaster' ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_hidden_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						$action_data['complete_with_errors'] = true;
						throw new Exception( 'Sorry, This forum is hidden and you do not have the capability to read or create new discussions in it.' );
					}
				}
			}
		}

		if ( function_exists( 'bbp_check_for_duplicate' ) && ! bbp_check_for_duplicate(
			[
				'post_type'      => bbp_get_topic_post_type(),
				'post_author'    => $user_id,
				'post_content'   => $topic_description,
				'anonymous_data' => $anonymous_data,
			]
		) ) {
			
			throw new Exception( "Duplicate discussion detected; it looks as though you've already said that!." );
		}

		/** Topic Blacklist */
		if ( function_exists( 'bbp_check_for_blacklist' ) && ! bbp_check_for_blacklist( $anonymous_data, $user_id, $topic_title, $topic_description ) ) {
			
			throw new Exception( 'Sorry, Your discussion cannot be created at this time.' );
		}

		/** Topic Status */
		// Maybe put into moderation.
		if ( function_exists( 'bbp_get_pending_status_id' ) && function_exists( 'bbp_check_for_moderation' ) && ! bbp_check_for_moderation( $anonymous_data, $user_id, $topic_title, $topic_description ) ) {
			$topic_status = bbp_get_pending_status_id();

		} elseif ( function_exists( 'bbp_get_public_status_id' ) ) {
			$topic_status = bbp_get_public_status_id();
		}
		$topic_status = '';
		/** No Errors */
		// Add the content of the form to $topic_data as an array.
		// Just in time manipulation of topic data before being created.
		$topic_data = apply_filters(
			'bbp_new_topic_pre_insert',
			[
				'post_author'    => $user_id,
				'post_title'     => $topic_title,
				'post_content'   => $topic_description,
				'post_status'    => $topic_status,
				'post_parent'    => $forum_id,
				'post_type'      => bbp_get_topic_post_type(),
				'tax_input'      => [],
				'comment_status' => 'closed',
			]
		);

		// Insert topic.
		$topic_id = wp_insert_post( $topic_data );

		if ( empty( $topic_id ) ) {
			$append_error = 'We are facing a problem to creating a topic.';
			throw new Exception( $append_error );

		}

		/** Trash Check */
		// If the forum is trash, or the topic_status is switched to.
		// trash, trash it properly.
		if ( function_exists( 'bbp_get_trash_status_id' ) && ( bbp_get_trash_status_id() === get_post_field( 'post_status', $forum_id ) 
			|| bbp_get_trash_status_id() === $topic_data['post_status'] ) ) {

			// Trash the reply.
			wp_trash_post( $topic_id );
		}

		/** Spam Check */
		// If reply or topic are spam, officially spam this reply.
		if ( function_exists( 'bbp_get_public_status_id' ) && ( function_exists( 'bbp_get_spam_status_id' ) && bbp_get_spam_status_id() === $topic_data['post_status'] ) ) {
			add_post_meta( $topic_id, '_bbp_spam_meta_status', bbp_get_public_status_id() );
		}

		/**
		 * Removed notification sent and called additionally.
		 * Due to we have moved all filters on title and content.
		 */
		remove_action( 'bbp_new_topic', 'bbp_notify_forum_subscribers', 11 );

		/** Update counts, etc... */
		do_action( 'bbp_new_topic', $topic_id, $forum_id, $anonymous_data, $user_id );

		/** Additional Actions (After Save) */
		do_action( 'bbp_new_topic_post_extras', $topic_id );

		if ( function_exists( 'bbp_notify_forum_subscribers' ) ) {
			/**
			 * Sends notification emails for new topics to subscribed forums.
			 */
			bbp_notify_forum_subscribers( $topic_id, $forum_id, $anonymous_data, $user_id );
		}


		$context = [
			'user_email'        => $selected_options['wp_user_email'],
			'forum_title'       => get_the_title( $forum_id ),
			'forum_link'        => get_the_permalink( $forum_id ),
			'topic_title'       => $topic_title,
			'topic_description' => $topic_description,
			'topic_lonk'        => get_the_permalink( $topic_id ),
		];
			
		return $context;
	}
}

AddTopicInForum::get_instance();
