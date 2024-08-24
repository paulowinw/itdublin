<?php
/**
 * AddPostSitewideActivityStream.
 * php version 5.6
 *
 * @category AddPostSitewideActivityStream
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use PeepSo;
use SureTriggers\Integrations\WordPress\WordPress;
use PeepSoActivityStream;
use PeepSoUser;

/**
 * AddPostSitewideActivityStream
 *
 * @category AddPostSitewideActivityStream
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostSitewideActivityStream extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PeepSo';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'peepso_add_post_sitewide_activity_stream';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Post to Sitewide Activity Stream', 'suretriggers' ),
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
	 *
	 * @return array
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'PeepSoActivityStream' ) || ! class_exists( 'PeepSo' ) || ! class_exists( 'PeepSoUser' ) ) {
			return [];
		}
		$author     = $user_id;
		$table_name = 'peepso_activities';
		$content    = $selected_options['post_content'];

		// create post.
		$a_post_data = [
			'post_title'   => "{$user_id}-{$author}-" . time(),
			'post_excerpt' => $content,
			'post_content' => $content,
			'post_status'  => 'publish',
			'post_author'  => $author,
			'post_type'    => PeepSoActivityStream::CPT_POST,
		];

		$content = $a_post_data['post_content'];

		$id = wp_insert_post( $a_post_data );

		// add metadata to indicate whether or not to display link previews for this post.
		add_post_meta( $id, '_peepso_display_link_preview', 1, true );

		// check $id for failure.
		if ( 0 === $id ) {
			throw new Exception( 'Unable to create post.' );
		}

		// add data to Activity Stream data table.
		$privacy    = PeepSoUser::get_instance( $user_id )->get_profile_accessibility();
		$a_act_data = [
			'act_owner_id'    => $user_id,
			'act_module_id'   => 1,
			'act_external_id' => $id,
			'act_access'      => $privacy,
			'act_ip'          => PeepSo::get_ip_address(),
		];

		$a_act_data = apply_filters( 'peepso_activity_insert_data', $a_act_data );

		global $wpdb;
		$res = $wpdb->insert( $wpdb->prefix . $table_name, $a_act_data );

		if ( ! is_int( $res ) ) {
			throw new Exception( 'Unable to create activity.' );
		}

		if ( 1 === absint( $a_act_data['act_module_id'] ) ) {
			update_user_meta( $user_id, 'peepso_last_used_post_privacy', $privacy );
		}

		$filtered_content = apply_filters( 'peepso_activity_post_content', $content, $id );
		wp_update_post(
			[
				'ID'           => $id,
				'post_content' => $filtered_content,
			]
		);
		$post    = get_post( $id );
		$context = array_merge(
			WordPress::get_user_context( $user_id ),
			WordPress::get_post_context( $id )
		);
		if ( $post instanceof \WP_Post ) {
			$context['permalink'] = PeepSo::get_page( 'activity_status' ) . $post->post_title;
		}
		return $context;
	}
}

AddPostSitewideActivityStream::get_instance();
