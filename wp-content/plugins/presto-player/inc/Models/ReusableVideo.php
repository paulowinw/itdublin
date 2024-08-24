<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Blocks\AudioBlock;
use PrestoPlayer\Blocks\VimeoBlock;
use PrestoPlayer\Blocks\YouTubeBlock;
use PrestoPlayer\Blocks\SelfHostedBlock;
use PrestoPlayer\Pro\Blocks\BunnyCDNBlock;
use WP_Query;

/**
 * Reusable Video Model
 */
class ReusableVideo {

	/**
	 * The post object
	 *
	 * @var \WP_Post
	 */
	public $post;

	/**
	 * The post type
	 *
	 * @var string
	 */
	private $post_type = 'pp_video_block';

	/**
	 * The setting name for instant video width option.
	 *
	 * @var string
	 */
	public $instant_video_width_setting_key = 'presto_player_instant_video_width';

	/**
	 * Constructor
	 *
	 * @param int $id The post ID.
	 */
	public function __construct( $id = 0 ) {
		$this->post = \get_post( $id );
		return $this;
	}

	/**
	 * Get attributes properties
	 *
	 * @param string $property The property to get.
	 * @return mixed
	 */
	public function __get( $property ) {
		return isset( $this->post->$property ) ? $this->post->$property : null;
	}

	/**
	 * Create a new video post
	 *
	 * @param array $args Arguments to pass to the wp_insert_post function.
	 *
	 * @return int
	 */
	public function create( $args = array() ) {
		return wp_insert_post(
			wp_parse_args(
				$args,
				array(
					'post_type' => $this->post_type,
				)
			)
		);
	}

	/**
	 * Fetch video posts
	 *
	 * @param array $args Arguments to pass to the WP_Query.
	 *
	 * @return \WP_Post[]
	 */
	public function fetch( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'post_type'   => $this->post_type,
				'post_status' => array( 'publish' ),
			)
		);

		return ( new WP_Query( $args ) )->posts;
	}

	/**
	 * Get all video posts
	 *
	 * @param array $args Arguments to pass to the fetch method.
	 *
	 * @return \WP_Post[]
	 */
	public function all( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'post_type' => $this->post_type,
				'per_page'  => -1,
			)
		);

		return get_posts( $args );
	}

	/**
	 * Get the first video post
	 *
	 * @param array $args Arguments to pass to the fetch method.
	 *
	 * @return ReusableVideo|bool
	 */
	public function first( $args = array() ) {
		$fetched = $this->fetch( wp_parse_args( $args, array( 'per_page' => 1 ) ) );
		return ! empty( $fetched[0] ) ? new static( $fetched[0] ) : false;
	}

	/**
	 * Get block from video post
	 *
	 * @return array
	 */
	public function getBlock() {
		if ( empty( $this->post->post_content ) ) {
			return array();
		}
		$blocks = \parse_blocks( $this->post->post_content );

		if ( empty( $blocks[0]['innerBlocks'] ) ) {
			return $blocks[0];
		}

		return ! empty( $blocks[0]['innerBlocks'][0] ) ? $blocks[0]['innerBlocks'][0] : array();
	}

	/**
	 * Get attributes from the block
	 *
	 * @param array $overrides Attributes to override.
	 *
	 * @return array
	 */
	public function getAttributes( $overrides = array() ) {
		$block = $this->getBlock();
		if ( empty( $block ) ) {
			return '';
		}

		// allow overriding attributes
		$block['attrs'] = wp_parse_args( $overrides, (array) $block['attrs'] );

		// maybe switch provider depending on url
		if ( ! empty( $overrides ) ) {
			$block = $this->maybeSwitchProvider( $block );
		}

		switch ( $block['blockName'] ) {
			case 'presto-player/self-hosted':
				return ( new SelfHostedBlock() )->getAttributes( $block['attrs'] );

			case 'presto-player/youtube':
				return ( new YouTubeBlock() )->getAttributes( $block['attrs'] );

			case 'presto-player/vimeo':
				return ( new VimeoBlock() )->getAttributes( $block['attrs'] );

			case 'presto-player/bunny':
				return ( new BunnyCDNBlock() )->getAttributes( $block['attrs'] );

			case 'presto-player/audio':
				return ( new AudioBlock() )->getAttributes( $block['attrs'] );
		}
	}

	/**
	 * Render block from video post
	 *
	 * @param array $overrides Attributes to override.
	 *
	 * @return string
	 */
	public function renderBlock( $overrides = array() ) {
		$block = $this->getBlock();

		if ( empty( $block ) ) {
			return '';
		}

		// allow overriding attributes
		$block['attrs'] = wp_parse_args( $overrides, (array) $block['attrs'] );

		// maybe switch provider depending on url
		$block = $this->maybeSwitchProvider( $block );

		// remove attachment_id if the src changes.
		if ( ! empty( $overrides['src'] ) ) {
			$block['attrs']['attachment_id'] = null;
		}

		switch ( $block['blockName'] ) {
			case 'presto-player/self-hosted':
				return ( new SelfHostedBlock( true, '1' ) )->html( $block['attrs'], '' );

			case 'presto-player/youtube':
				return ( new YouTubeBlock( true, '1' ) )->html( $block['attrs'], '' );

			case 'presto-player/vimeo':
				return ( new VimeoBlock( true, '1' ) )->html( $block['attrs'], '' );

			case 'presto-player/bunny':
				return class_exists( BunnyCDNBlock::class ) ? ( new BunnyCDNBlock( true, '1' ) )->html( $block['attrs'], '' ) : '';

			case 'presto-player/audio':
				return ( new AudioBlock( true, '1' ) )->html( $block['attrs'], '' );
		}
	}

	/**
	 * Maybe switch provider if the url is overridden
	 *
	 * @param array $block The block to check.
	 */
	protected function maybeSwitchProvider( $block ) {
		if ( empty( $block ) || ! is_array( $block ) ) {
			return $block;
		}

		if ( ! empty( $block['attrs']['src'] ) ) {
			if ( $block['attrs']['src'] ) {
				$filetype = wp_check_filetype( $block['attrs']['src'] );
				if ( isset( $filetype['type'] ) && false !== strpos( $filetype['type'], 'audio' ) ) {
					$block['blockName'] = 'presto-player/audio';
					return $block;
				}
			}

			$yt_rx             = '/^((?:https?:)?\/\/)?((?:www|m)\.)?((?:youtube\.com|youtu.be))(\/(?:[\w\-]+\?v=|embed\/|v\/)?)([\w\-]+)(\S+)?$/';
			$has_match_youtube = preg_match( $yt_rx, $block['attrs']['src'], $yt_matches );

			if ( $has_match_youtube ) {
				$block['blockName'] = 'presto-player/youtube';
				return $block;
			}

			$vm_rx           = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
			$has_match_vimeo = preg_match( $vm_rx, $block['attrs']['src'], $vm_matches );

			if ( $has_match_vimeo ) {
				$block['blockName'] = 'presto-player/vimeo';
				return $block;
			}

			if ( empty( $block['blockName'] ) ) {
				$block['blockName'] = 'presto-player/self-hosted';
				return $block;
			}
		}

		return $block;
	}

	/**
	 * Get reusable video block function.
	 *
	 * @return $content The content of the block.
	 */
	public function content() {
		return ! empty( $this->post->post_content ) ? $this->post->post_content : '';
	}

	/**
	 * Retrieves the poster image URL from the first
	 * 'presto-player/reusable-edit' block in the post content.
	 *
	 * @return string|bool The poster image URL or false if not set.
	 */
	public function getPosterFromBlock() {
		$block = $this->getBlock();

		if ( empty( $block ) ) {
			return false;
		}

		// Attempt to extract the poster attribute from the first inner block.
		return $block['attrs']['poster'] ?? '';
	}

	/**
	 * Check if instant video page is enabled
	 *
	 * @return bool
	 */
	public function instantVideoPageEnabled() {
		if ( empty( $this->post->ID ) ) {
			return false;
		}
		return get_post_meta( $this->post->ID, 'presto_player_instant_video_pages_enabled', true );
	}

	/**
	 * Get instant video width.
	 *
	 * @return string|bool The video width + unit.
	 */
	public function getInstantVideoWidth() {
		if ( empty( $this->post->ID ) ) {
			return false;
		}
		$config = get_option( $this->instant_video_width_setting_key, '800px' );
		return ! empty( $config ) ? $config : '800px';
	}
}
