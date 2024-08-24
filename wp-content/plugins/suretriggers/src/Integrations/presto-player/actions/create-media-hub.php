<?php
/**
 * CreateMediaHub.
 * php version 5.6
 *
 * @category CreateMediaHub
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PrestoPlayer\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use SureTriggers\Integrations\WordPress\WordPress;
use Exception;

/**
 * CreateMediaHub
 *
 * @category CreateMediaHub
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class CreateMediaHub extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PrestoPlayer';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'pp_create_mediahub';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Create Media Hub', 'suretriggers' ),
			'action'   => 'pp_create_mediahub',
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
	 * @return bool|array
	 * @throws Exception Error.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		
		$title     = $selected_options['media_hub_title'];
		$video_url = $selected_options['media_hub_youtube_video_url'];
		$preset    = $selected_options['video_preset'];
		if ( '' == $preset ) {
			$preset = 4;
		}
		$post_author = $selected_options['post_author'];

		// Pattern to match YouTube video ID.
		$pattern = '/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/';

		// Execute the regex pattern on the URL.
		if ( preg_match( $pattern, $video_url, $matches ) ) {
			// Get the matched video ID.
			$video_id       = $matches[1];
			$media_hub_post = [
				'post_title'   => $title,
				'post_content' => '<!-- wp:presto-player/reusable-edit --><div class="wp-block-presto-player-reusable-edit"><!-- wp:presto-player/youtube {"id":1,"src":"' . $video_url . '","preset":' . $preset . ',"video_id":"' . $video_id . '"} /--></div><!-- /wp:presto-player/reusable-edit -->',
				'post_status'  => 'publish',
				'post_type'    => 'pp_video_block',
				'post_author'  => $post_author,
			];
		} else {
			return [
				'message' => __( 'Invalid YouTube URL.', 'suretriggers' ),
			];
		}
		
		$id = wp_insert_post( $media_hub_post );
		if ( $id ) {
			return WordPress::get_post_context( $id );
		} else {
			return [
				'message' => __( 'There was an error creating the video!', 'suretriggers' ),
			];
		}
	}
}

CreateMediaHub::get_instance();
