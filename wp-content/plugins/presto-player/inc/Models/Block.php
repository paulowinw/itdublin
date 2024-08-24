<?php

namespace PrestoPlayer\Models;

class Block {

	public static function getBlockTypes() {
		return apply_filters(
			'presto_player_registered_block_types',
			array(
				'presto-player/reusable-display',
				'presto-player/self-hosted',
				'presto-player/vimeo',
				'presto-player/youtube',
				'presto-player/bunny',
				'presto-player/audio',
				'presto-player/playlist',
			)
		);
	}
}
