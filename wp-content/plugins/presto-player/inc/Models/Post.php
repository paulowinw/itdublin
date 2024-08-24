<?php

namespace PrestoPlayer\Models;

class Post {

	protected $post;

	public function __construct( \WP_Post $post ) {
		$this->post = $post;
	}

	/**
	 * Find video id in post using multiple methods
	 *
	 * @return int|false
	 */
	public function findVideoId() {
		// first check for id in block
		$id = $this->getVideoIdFromBlock();
		if ( $id ) {
			return (int) $id;
		}

		$id = $this->getVideoIdFromShortcode();
		if ( $id ) {
			return (int) $id;
		}

		$id = $this->getVideoIdFromContent();
		if ( $id ) {
			return (int) $id;
		}

		return false;
	}

	/**
	 * Find the video id from the shortcode
	 *
	 * @return int|false
	 */
	public function getVideoIdFromShortcode() {
		$pattern = get_shortcode_regex();
		$content = $this->post->post_content;
		preg_match_all( "/$pattern/", $content, $matches );

		$shortcode = array_keys( $matches[2], 'presto_player' );
		if ( ! $shortcode ) {
			return false;
		}
		if ( empty( $matches[3][0] ) ) {
			return false;
		}

		// get media hub id
		$atts = shortcode_parse_atts( $matches[3][0] );
		if ( ! empty( $atts['id'] ) ) {
			$this->post = get_post( $atts['id'] );
			return $this->findVideoId();
		}
	}

	/**
	 * Get the video id from the block
	 */
	public function getVideoIdFromBlock() {
		$blocks = parse_blocks( $this->post->post_content );
		foreach ( $blocks as $block ) {
			// inside wrapper block
			if ( 'presto-player/reusable-edit' === $block['blockName'] && ! empty( $block['innerBlocks'] ) ) {
				$block = $block['innerBlocks'][0];
			}

			// we have a reusable display block
			if ( 'presto-player/reusable-display' === $block['blockName'] ) {
				// find the media hub post
				if ( ! empty( $block['attrs']['id'] ) ) {
					$block = $this->getMediaHubBlockFromPost( $block['attrs']['id'] );
				}
			}

			// in case block needs to be filtered
			$block = apply_filters( 'presto_player_get_block_from_content', $block );

			// find the id attribute
			if ( ! empty( $block ) && in_array( $block['blockName'], Block::getBlockTypes() ) ) {
				if ( ! empty( $block['attrs']['id'] ) ) {
					return $block['attrs']['id'];
				}
			}
		}

		return false;
	}

	/**
	 * Fallback - get video id from comment in content
	 */
	public function getVideoIdFromContent() {
		$content = $this->post->post_content;

		preg_match_all( '/(?<=<!--presto-player:video_id=)(.*)(?=-->)/', $content, $matches );
		if ( ! empty( $matches[0][0] ) ) {
			return (int) $matches[0][0];
		}

		return false;
	}

	/**
	 * Retrieve the inner block from media hub post.
	 *
	 * @param  int $id id of the post.
	 * @return array|false
	 */
	public function getMediaHubBlockFromPost( $id ) {
		if ( ! $id ) {
			return false;
		}

		// get the media hub post.
		$block_post = get_post( $id );

		// if it has content, get the first block.
		if ( ! empty( $block_post->post_content ) ) {
			$inner_blocks = parse_blocks( $block_post->post_content );
			if ( ! empty( $inner_blocks[0]['innerBlocks'][0] ) ) {
				return $inner_blocks[0]['innerBlocks'][0] ?? false;
			}
		}

		return false;
	}
}
