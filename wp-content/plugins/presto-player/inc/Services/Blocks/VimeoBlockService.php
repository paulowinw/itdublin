<?php

namespace PrestoPlayer\Services\Blocks;

class VimeoBlockService {

	public function register() {
		add_action( 'wp_get_attachment_url', array( $this, 'replaceLink' ), 10, 2 );
	}

	/**
	 * Dynamically replace attachment link
	 *
	 * @param string $url
	 * @param int    $post_id
	 * @return string
	 */
	public function replaceLink( $url, $post_id ) {
		$type        = get_post_meta( $post_id, 'type', true );
		$external_id = get_post_meta( $post_id, 'presto_external_id', true );

		if ( $type !== 'vimeo' ) {
			return $url;
		}

		return 'https://vimeo.com/' . (int) $external_id;
	}

	/**
	 * Get video data from remote
	 *
	 * @param string $id
	 * @return array
	 */
	public function getRemoteVideoData( $id ) {
		$response     = wp_remote_get( 'http://vimeo.com/api/v2/video/' . $id . '.json' );
		$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
		return ! empty( $api_response[0] ) ? $api_response[0] : array();
	}
}
