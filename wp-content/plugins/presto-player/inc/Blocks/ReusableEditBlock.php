<?php
/**
 * Reusable Video Block Class
 *
 * @package PrestoPlayer\Blocks
 */

namespace PrestoPlayer\Blocks;

/**
 * Reusable Edit Block Class
 */
class ReusableEditBlock {
	/**
	 * Register Block
	 *
	 * @return void
	 */
	public function register() {
		register_block_type(
			PRESTO_PLAYER_PLUGIN_DIR . 'src/admin/blocks/blocks/reusable-edit',
		);
	}
}
