<?php

namespace PrestoPlayer\Services\Blocks;

class YoutubeBlockService {

	public function register() {
		add_action( 'wp_get_attachment_url', array( $this, 'replaceLink' ), 10, 2 );
	}

	public function replaceLink( $url, $post_id ) {
		$type        = get_post_meta( $post_id, 'presto_video_type', true );
		$external_id = get_post_meta( $post_id, 'presto_external_id', true );

		if ( $type !== 'youtube' ) {
			return $url;
		}

		return 'https://www.youtube.com/watch?v=' . (int) $external_id;
	}

	/**
	 * Get video data from remote
	 *
	 * @param string $id
	 * @return array
	 */
	public function getRemoteVideoData( $id ) {
		$response = wp_remote_get(
			add_query_arg(
				array(
					'format' => 'json',
					'url'    => esc_url_raw( 'https://www.youtube.com/watch?v=' . $id ),
				),
				'https://www.youtube.com/oembed'
			)
		);

		// handle errors silently since it's progressive enhancement
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );
		return json_decode( $body, true );
	}
}
