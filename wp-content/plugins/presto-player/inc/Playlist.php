<?php
namespace PrestoPlayer;

use PrestoPlayer\Services\ReusableVideos;
use PrestoPlayer\Blocks\AudioBlock;
use PrestoPlayer\Blocks\SelfHostedBlock;
use PrestoPlayer\Blocks\VimeoBlock;
use PrestoPlayer\Blocks\YouTubeBlock;
use PrestoPlayer\Pro\Blocks\BunnyCDNBlock;

class Playlist {

	/**
	 * Parses the attributes with respect to the provider.
	 *
	 * @param string $block_name Block name.
	 * @param array  $attributes Attributes of the block.
	 *
	 * @return array
	 */
	public function parsed_attributes( $block_name, $attributes ) {
		$attributes = wp_parse_args(
			$attributes,
			array(
				'id'                             => '',
				'src'                            => '',
				'title'                          => '',
				'provider'                       => '',
				'class'                          => '',
				'custom_field'                   => '',
				'poster'                         => '',
				'preload'                        => 'auto',
				'preset'                         => 0,
				'autoplay'                       => false,
				'plays_inline'                   => false,
				'chapters'                       => array(),
				'overlays'                       => array(),
				'tracks'                         => array(),
				'muted_autoplay_preview'         => false,
				'muted_autoplay_caption_preview' => false,
			),
		);

		switch ( $block_name ) {
			case 'presto-player/self-hosted':
				return ( new SelfHostedBlock() )->getAttributes( $attributes, '' );

			case 'presto-player/youtube':
				return ( new YouTubeBlock() )->getAttributes( $attributes, '' );

			case 'presto-player/vimeo':
				return ( new VimeoBlock() )->getAttributes( $attributes, '' );

			case 'presto-player/bunny':
				return class_exists( BunnyCDNBlock::class ) ? ( new BunnyCDNBlock() )->getAttributes( $attributes, '' ) : '';

			case 'presto-player/audio':
				return ( new AudioBlock() )->getAttributes( $attributes, '' );
		}
	}
}
