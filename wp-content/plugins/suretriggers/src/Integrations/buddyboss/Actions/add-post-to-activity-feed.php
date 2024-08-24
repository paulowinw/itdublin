<?php
/**
 * AddPostToActivityFeed.
 * php version 5.6
 *
 * @category AddPostToActivityFeed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\BuddyBoss\BuddyBoss;

/**
 * AddPostToActivityFeed
 *
 * @category AddPostToActivityFeed
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostToActivityFeed extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_add_post_to_activity_feed';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add post to activity feed', 'suretriggers' ),
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
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['bb_author'] ) || ! is_email( $selected_options['bb_author'] ) ) {
			throw new Exception( 'Invalid email.' );
		}
		$user_id = email_exists( $selected_options['bb_author'] );

		if ( false === $user_id ) {
			throw new Exception( 'User with email ' . $selected_options['bb_author'] . ' does not exists.' );
		}

		$content = $selected_options['bb_activity_content'];
		$action  = ( isset( $selected_options['bb_activity_action'] ) ) ? $selected_options['bb_activity_action'] : '';
		if ( function_exists( 'bp_activity_add' ) ) {
			$activity_id = bp_activity_add(
				[
					'action'        => $action,
					'content'       => $content,
					'component'     => 'activity',
					'type'          => 'activity_update',
					'user_id'       => $user_id,
					'primary_link'  => $selected_options['bb_activity_action_link'],
					'hide_sitewide' => $selected_options['hide_sitewide'],
				]
			);

			// Check if link preview is active.
			if ( ! function_exists( 'bp_is_activity_link_preview_active' ) ) {
				throw new Exception( 'Link preview is not activated.' );
			}
			if ( $activity_id && bp_is_activity_link_preview_active() ) {
				// Check if content has links.
				$links = BuddyBoss::st_content_has_links( $content );
			
				if ( ! empty( $links ) ) {
					// Get URL parsed data.
					if ( ! function_exists( 'bp_core_parse_url' ) ) {
						throw new Exception( 'Link preview function is not present.' );
					}
					$parse_url_data = bp_core_parse_url( $links[0] );

					// If empty data then send error.
					if ( empty( $parse_url_data ) ) {
						throw new Exception( 'There was a problem generating a link preview.' );
					}
					
					if ( ! empty( $parse_url_data['images'] ) ) {
						$preview_data = [
							'url'         => $links[0],
							'title'       => $parse_url_data['title'],
							'description' => $parse_url_data['description'],
							'image_url'   => $parse_url_data['images'][0],
						];
					}
					if ( ! empty( $preview_data ) ) {
						if ( function_exists( 'bb_media_sideload_attachment' ) && function_exists( 'bp_activity_update_meta' ) ) {
							// Sideload the image as attachment.
							$attachment_id = bb_media_sideload_attachment( $preview_data['image_url'] );
							if ( $attachment_id ) {
								$preview_data['attachment_id'] = $attachment_id;
								unset( $preview_data['image_url'] );
							}
							// Update activity meta for link preview.
							bp_activity_update_meta( $activity_id, '_link_preview_data', $preview_data );
						}
					}
				}
			}

			if ( function_exists( 'bp_activity_get_specific' ) ) {
				$context = bp_activity_get_specific( [ 'activity_ids' => $activity_id ] );
		
				if ( isset( $context['activities'] ) && ! empty( $context['activities'] ) ) {
					return $context['activities'][0];
				}
			}
		}
		throw new Exception( SURE_TRIGGERS_ACTION_ERROR_MESSAGE );
	}
}

AddPostToActivityFeed::get_instance();
