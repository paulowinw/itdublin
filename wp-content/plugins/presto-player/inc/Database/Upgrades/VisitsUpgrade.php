<?php

namespace PrestoPlayer\Database\Upgrades;

use PrestoPlayer\Models\ReusableVideo;

class VisitsUpgrade {

	protected $table;
	protected $version = 2;
	protected $name    = 'presto_player_visits_upgrade_version';

	public function run() {
		$this->fixReusableVideoStats();
	}

	/**
	 * Fixes error where reusable video ids were stored instead of video id
	 * in visits table
	 *
	 * @return void
	 */
	public function fixReusableVideoStats() {
		global $wpdb;

		$current_version = get_option( $this->name, 0 );

		// we've already done this one
		if ( $this->version <= $current_version ) {
			return;
		}

		// get all reusable video ids
		$reusable_videos = get_posts(
			array(
				'posts_per_page' => -1,
				'post_type'      => 'pp_video_block',
			)
		);

		// bail if we have no reusable videos
		if ( empty( $reusable_videos ) ) {
			return;
		}

		// get visits with this id and update to video
		foreach ( $reusable_videos as $reusable_video ) {
			$video_block = new ReusableVideo( $reusable_video );
			$block       = $video_block->getBlock();

			if ( ! empty( $block['attrs']['id'] ) ) {
				$id = $block['attrs']['id'];
				try {
					$wpdb->update( $wpdb->prefix . 'presto_player_visits', array( 'video_id' => $id ), array( 'video_id' => (int) $reusable_video->ID ) );
				} catch ( \Exception $e ) {
				}
			}
		}

		update_option( $this->name, $this->version );
	}
}
