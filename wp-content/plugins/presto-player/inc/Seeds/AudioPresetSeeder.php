<?php

namespace PrestoPlayer\Seeds;

use PrestoPlayer\Models\AudioPreset;

class AudioPresetSeeder {

	/**
	 * To update, change seed data and increment
	 * this version number
	 *
	 * @var integer
	 */
	protected $version = 2;

	public function run() {
		$db_version = get_option( 'presto_audio_preset_seed_version', 0 );

		if ( $db_version < $this->version ) {
			$this->seedDefault();
			$this->seedSimple();
			$this->seedMinimal();
			$this->seedCourse();

			update_option( 'presto_audio_preset_seed_version', $this->version );
		}
	}

	public function seedDefault() {
		$preset = new AudioPreset();
		$preset->updateOrCreate(
			array( 'slug' => 'default' ),
			array(
				'name'                 => __( 'Default', 'presto-player' ),
				'slug'                 => 'default',
				'skin'                 => 'default',
				'icon'                 => 'format-audio',
				'rewind'               => true,
				'play'                 => true,
				'play-large'           => true,
				'fast-forward'         => true,
				'progress'             => true,
				'current-time'         => true,
				'mute'                 => true,
				'volume'               => true,
				'speed'                => true,
				'pip'                  => false,
				// behavior
				'save_player_position' => true,
				'reset_on_end'         => true,
				'sticky_scroll'        => false,
				'is_locked'            => true,
			)
		);
	}

	public function seedSimple() {
		$preset = new AudioPreset();
		$preset->updateOrCreate(
			array( 'slug' => 'simple' ),
			array(
				'name'                 => __( 'Simple', 'presto-player' ),
				'slug'                 => 'simple',
				'icon'                 => 'video-alt3',
				'rewind'               => false,
				'play'                 => true,
				'play-large'           => true,
				'fast-forward'         => false,
				'progress'             => true,
				'current-time'         => false,
				'mute'                 => false,
				'volume'               => false,
				'speed'                => true,
				'pip'                  => false,
				// behavior
				'save_player_position' => false,
				'reset_on_end'         => true,
				'sticky_scroll'        => false,
				'is_locked'            => true,
			)
		);
	}

	public function seedMinimal() {
		$preset = new AudioPreset();
		$preset->updateOrCreate(
			array( 'slug' => 'minimal' ),
			array(
				'name'                 => __( 'Minimal', 'presto-player' ),
				'slug'                 => 'minimal',
				'icon'                 => 'controls-play',
				'rewind'               => false,
				'play'                 => false,
				'play-large'           => true,
				'fast-forward'         => false,
				'progress'             => false,
				'current-time'         => false,
				'mute'                 => false,
				'volume'               => false,
				'speed'                => false,
				'pip'                  => false,
				// behavior
				'save_player_position' => false,
				'reset_on_end'         => true,
				'sticky_scroll'        => false,
				'is_locked'            => true,
			)
		);
	}

	public function seedCourse() {
		$preset = new AudioPreset();
		$preset->updateOrCreate(
			array( 'slug' => 'Course' ),
			array(
				'name'                 => __( 'Course', 'presto-player' ),
				'slug'                 => 'course',
				'skin'                 => 'stacked',
				'icon'                 => 'format-audio',
				'rewind'               => true,
				'play'                 => true,
				'play-large'           => true,
				'fast-forward'         => true,
				'progress'             => true,
				'current-time'         => true,
				'mute'                 => true,
				'volume'               => true,
				'speed'                => true,
				'pip'                  => false,
				// behavior
				'save_player_position' => true,
				'reset_on_end'         => true,
				'sticky_scroll'        => false,
				'is_locked'            => true,
			)
		);
	}
}
