<?php
/**
 * AddReplyInTopic.
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
class AddReplyInTopic extends AutomateAction {

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
	public $action = 'bbp_add_reply_in_topic';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Reply In Topic', 'suretriggers' ),
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
		if ( ! function_exists( 'bbp_get_topic' ) || ! function_exists( 'bbp_get_forum' ) || ! function_exists( 'bbp_is_forum_category' ) || ! function_exists( 'bbp_get_reply_post_type' ) || ! function_exists( 'bbp_get_topic_reply_count' ) ) {
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
		$topic_id          = $selected_options['topic'];
		$reply_description = $selected_options['reply_content'];
		$anonymous_data    = 0;

		if ( ! bbp_get_topic( $topic_id ) ) {
			throw new Exception( 'Sorry, Discussion does not exist' );
		}

		if ( ! bbp_get_forum( $forum_id ) ) {
			throw new Exception( 'Sorry, Forum does not exist' );
		}
		// Forum exists.
		if ( ! empty( $forum_id ) ) {

			// Forum is a category.
			if ( bbp_is_forum_category( $forum_id ) ) {
				throw new Exception( 'Sorry, This forum is a category. No discussions can be created in this forum' );
				// Forum is not a category.
			} else {

				// Forum is closed and user cannot access.
				if ( function_exists( 'bbp_is_forum_closed' ) && bbp_is_forum_closed( $forum_id ) && ! current_user_can( 'edit_forum', $forum_id ) ) {
					throw new Exception( 'Sorry, This forum has been closed to new discussions' );
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
					
					foreach ( $group_ids as $group_id ) {
						if ( function_exists( 'groups_is_user_member' ) && groups_is_user_member( $user_id, $group_id ) ) {
							$is_member = true;
							break;
						}
					}               
				}

				// Forum is private and user cannot access.
				if ( function_exists( 'bbp_is_forum_private' ) && bbp_is_forum_private( $forum_id ) && function_exists( 'bbp_is_user_keymaster' ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_private_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						throw new Exception( 'Sorry, This forum is private and you do not have the capability to read or create new discussions in it' );
					}

					// Forum is hidden and user cannot access.
				} elseif ( function_exists( 'bbp_is_forum_hidden' ) && bbp_is_forum_hidden( $forum_id ) && function_exists( 'bbp_is_user_keymaster' ) && ! bbp_is_user_keymaster() ) {
					if (
						( empty( $group_ids ) && ! current_user_can( 'read_hidden_forums' ) )
						|| ( ! empty( $group_ids ) && ! $is_member )
					) {
						throw new Exception( 'Sorry, This forum is hidden and you do not have the capability to read or create new discussions in it' );
					}
				}
			}
		}

		/** Unfiltered HTML */
		// Remove kses filters from title and content for capable users and if the nonce is verified.
		if ( current_user_can( 'unfiltered_html' ) ) {
			remove_filter( 'bbp_new_reply_pre_title', 'wp_filter_kses' );
			remove_filter( 'bbp_new_reply_pre_content', 'bbp_encode_bad', 10 );
			remove_filter( 'bbp_new_reply_pre_content', 'bbp_filter_kses', 30 );
		}

		// Filter and sanitize.
		$reply_content = apply_filters( 'bbp_new_reply_pre_content', $reply_description );

		/** Reply Duplicate */
		if ( function_exists( 'bbp_check_for_duplicate' ) && ! bbp_check_for_duplicate(
			[
				'post_type'      => bbp_get_reply_post_type(),
				'post_author'    => $user_id,
				'post_content'   => $reply_content,
				'post_parent'    => $topic_id,
				'anonymous_data' => $anonymous_data,
			]
		) ) {
			throw new Exception( "Duplicate reply detected; it looks as though you've already said that!" );
		}

		/** Topic Closed */
		// If topic is closed, moderators can still reply.
		if ( function_exists( 'bbp_is_topic_closed' ) && bbp_is_topic_closed( $topic_id ) && ! current_user_can( 'moderate' ) ) {
			throw new Exception( 'Sorry, Discussion is closed.' );
		}

		/** Reply Blacklist */
		if ( function_exists( 'bbp_check_for_blacklist' ) && ! bbp_check_for_blacklist( $anonymous_data, $user_id, '', $reply_content ) ) {
			throw new Exception( 'Sorry, Your reply cannot be created at this time.' );
		}

		/** Reply Status */
		// Maybe put into moderation.
		$reply_status = '';
		if ( function_exists( 'bbp_check_for_moderation' ) && function_exists( 'bbp_get_pending_status_id' ) && ! bbp_check_for_moderation( $anonymous_data, $user_id, '', $reply_content ) ) {
			$reply_status = bbp_get_pending_status_id();
		} elseif ( function_exists( 'bbp_get_public_status_id' ) ) {
			$reply_status = bbp_get_public_status_id();
		}

		/** Topic Closed */
		// If topic is closed, moderators can still reply.
		if ( function_exists( 'bbp_is_topic_closed' ) && bbp_is_topic_closed( $topic_id ) && ! current_user_can( 'moderate' ) ) {
			throw new Exception( 'Sorry, Discussion is closed.' );
		}

		/** No Errors */

		// Add the content of the form to $reply_data as an array.
		// Just in time manipulation of reply data before being created.
		$reply_data = apply_filters(
			'bbp_new_reply_pre_insert',
			[
				'post_author'    => $user_id,
				'post_title'     => '',
				'post_content'   => $reply_content,
				'post_status'    => $reply_status,
				'post_parent'    => $topic_id,
				'post_type'      => bbp_get_reply_post_type(),
				'comment_status' => 'closed',
				'menu_order'     => bbp_get_topic_reply_count( $topic_id, false ) + 1,
			]
		);

		// Insert reply.
		$reply_id = wp_insert_post( $reply_data );

		if ( empty( $reply_id ) ) {
			throw new Exception( 'We are facing a problem to creating a reply' );
		}

		/** Trash Check */
		// If this reply starts as trash, add it to pre_trashed_replies.
		// for the topic, so it is properly restored.
		if ( ( function_exists( 'bbp_is_topic_trash' ) && bbp_is_topic_trash( $topic_id ) ) || ( function_exists( 'bbp_get_trash_status_id' ) && bbp_get_trash_status_id() === $reply_data['post_status'] ) ) {

			// Trash the reply.
			wp_trash_post( $reply_id );

			// Only add to pre-trashed array if topic is trashed.
			if ( function_exists( 'bbp_is_topic_trash' ) && bbp_is_topic_trash( $topic_id ) ) {

				// Get pre_trashed_replies for topic.
				$pre_trashed_replies = (array) get_post_meta( $topic_id, '_bbp_pre_trashed_replies', true );

				// Add this reply to the end of the existing replies.
				$pre_trashed_replies[] = $reply_id;

				// Update the pre_trashed_reply post meta.
				update_post_meta( $topic_id, '_bbp_pre_trashed_replies', $pre_trashed_replies );
			}

			/** Spam Check */
			// If reply or topic are spam, officially spam this reply.
		} elseif ( function_exists( 'bbp_get_public_status_id' ) && ( ( function_exists( 'bbp_is_topic_spam' ) && bbp_is_topic_spam( $topic_id ) ) || ( function_exists( 'bbp_get_spam_status_id' ) && bbp_get_spam_status_id() === $reply_data['post_status'] ) ) ) {
			add_post_meta( $reply_id, '_bbp_spam_meta_status', bbp_get_public_status_id() );

			// Only add to pre-spammed array if topic is spam.
			if ( function_exists( 'bbp_is_topic_spam' ) && bbp_is_topic_spam( $topic_id ) ) {

				// Get pre_spammed_replies for topic.
				$pre_spammed_replies = (array) get_post_meta( $topic_id, '_bbp_pre_spammed_replies', true );

				// Add this reply to the end of the existing replies.
				$pre_spammed_replies[] = $reply_id;

				// Update the pre_spammed_replies post meta.
				update_post_meta( $topic_id, '_bbp_pre_spammed_replies', $pre_spammed_replies );
			}
		}

		/**
		 * Removed notification sent and called additionally.
		 * Due to we have moved all filters on title and content.
		 */
		remove_action( 'bbp_new_reply', 'bbp_notify_topic_subscribers', 11 );

		/** Update counts, etc... */
		do_action( 'bbp_new_reply', $reply_id, $topic_id, $forum_id, $anonymous_data, $user_id, false, 0 );

		/** Additional Actions (After Save) */
		do_action( 'bbp_new_reply_post_extras', $reply_id );

		$context = [
			'user_email'        => $selected_options['wp_user_email'],
			'forum_title'       => get_the_title( $forum_id ),
			'forum_link'        => get_the_permalink( $forum_id, true ),
			'reply_description' => $reply_content,
			'reply_link'        => get_the_permalink( $reply_id, true ),
		];
		return $context;
	}
}

AddReplyInTopic::get_instance();
