<?php

namespace PrestoPlayer\Blocks;

class MediaHubBlock {

	/**
	 * Register Block
	 *
	 * @return void
	 */
	public function register() {
		register_block_type(
			PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/media-hub',
		);
	}
}
