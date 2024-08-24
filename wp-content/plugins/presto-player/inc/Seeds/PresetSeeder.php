<?php

namespace PrestoPlayer\Seeds;

use PrestoPlayer\Models\Preset;

class PresetSeeder {

	/**
	 * To update, change seed data and increment
	 * this version number
	 *
	 * @var integer
	 */
	protected $version = 4;

	public function run() {
		$db_version = get_option( 'presto_preset_seed_version', 0 );

		if ( $db_version < $this->version ) {
			$this->seedDefault();
			$this->seedCourse();
			$this->seedSimple();
			$this->seedMinimal();
			$this->seedYoutube();

			update_option( 'presto_preset_seed_version', $this->version );
		}
	}

	public function seedDefault() {
		$preset  = new Preset();
		$presets = $preset->fetch( array( 'slug' => 'default' ) );

		$args = array(
			'name'                 => __( 'Default', 'presto-player' ),
			'slug'                 => 'default',
			'skin'                 => 'default',
			'icon'                 => 'format-video',
			'play-large'           => true,
			'rewind'               => true,
			'play'                 => true,
			'fast-forward'         => true,
			'progress'             => true,
			'current-time'         => true,
			'mute'                 => true,
			'volume'               => true,
			'speed'                => false,
			'pip'                  => false,
			'fullscreen'           => true,
			'captions'             => false,
			// behavior
			'save_player_position' => true,
			'auto_hide'            => true,
			'reset_on_end'         => true,
			'sticky_scroll'        => false,
			// youtube
			'hide_youtube'         => false,
			'lazy_load_youtube'    => false,
			'hide_logo'            => false,
			'is_locked'            => true,
		);

		// Preset not present in DB, so set skin to modern.
		if ( empty( $presets->total ) ) {
			$args['skin'] = 'modern';
		}

		$preset->updateOrCreate(
			array( 'slug' => 'default' ),
			$args
		);

		return $preset;
	}

	public function seedCourse() {
		$preset  = new Preset();
		$presets = $preset->fetch( array( 'slug' => 'course' ) );

		$args = array(
			'name'                 => __( 'Course', 'presto-player' ),
			'slug'                 => 'course',
			'skin'                 => 'stacked',
			'icon'                 => 'welcome-learn-more',
			'play-large'           => true,
			'rewind'               => true,
			'play'                 => true,
			'fast-forward'         => true,
			'progress'             => true,
			'current-time'         => true,
			'mute'                 => true,
			'volume'               => true,
			'speed'                => true,
			'pip'                  => true,
			'fullscreen'           => true,
			'captions'             => false,
			// behavior
			'save_player_position' => true,
			'auto_hide'            => true,
			'reset_on_end'         => true,
			'sticky_scroll'        => true,
			// youtube
			'hide_youtube'         => false,
			'lazy_load_youtube'    => false,
			'hide_logo'            => false,
			'is_locked'            => true,
		);

		// Preset not present in DB, so set skin to modern.
		if ( empty( $presets->total ) ) {
			$args['skin'] = 'modern';
		}

		$preset->updateOrCreate(
			array( 'slug' => 'course' ),
			$args
		);

		return $preset;
	}

	public function seedYoutube() {
		$preset  = new Preset();
		$presets = $preset->fetch( array( 'slug' => 'youtube' ) );

		$args = array(
			'name'                 => __( 'Youtube Optimized', 'presto-player' ),
			'slug'                 => 'youtube',
			'skin'                 => 'stacked',
			'icon'                 => 'youtube',
			'play-large'           => true,
			'rewind'               => true,
			'play'                 => true,
			'fast-forward'         => true,
			'progress'             => true,
			'current-time'         => true,
			'mute'                 => true,
			'volume'               => true,
			'speed'                => true,
			'pip'                  => false,
			'fullscreen'           => true,
			'captions'             => false,
			// behavior
			'save_player_position' => false,
			'auto_hide'            => true,
			'reset_on_end'         => true,
			'sticky_scroll'        => false,
			// youtube
			'hide_youtube'         => false,
			'lazy_load_youtube'    => true,
			'action_bar'           => array(
				'enabled'          => true,
				'percentage_start' => 0,
				'text'             => __( 'Subscribe to our channel', 'presto-player' ),
				'background_color' => '#1d1d1d',
				'button_type'      => 'youtube',
				'button_count'     => false,
			),
			'hide_logo'            => false,
			'is_locked'            => true,
		);

		// Preset not present in DB, so set skin to modern.
		if ( empty( $presets->total ) ) {
			$args['skin'] = 'modern';
		}

		$preset->updateOrCreate(
			array( 'slug' => 'youtube' ),
			$args
		);

		return $preset;
	}

	public function seedSimple() {
		$preset  = new Preset();
		$presets = $preset->fetch( array( 'slug' => 'simple' ) );

		$args = array(
			'name'                 => __( 'Simple', 'presto-player' ),
			'slug'                 => 'simple',
			'icon'                 => 'video-alt3',
			'play-large'           => true,
			'rewind'               => false,
			'play'                 => true,
			'fast-forward'         => false,
			'progress'             => true,
			'current-time'         => false,
			'mute'                 => false,
			'volume'               => false,
			'speed'                => false,
			'pip'                  => false,
			'fullscreen'           => true,
			'captions'             => false,
			// behavior
			'save_player_position' => false,
			'auto_hide'            => true,
			'reset_on_end'         => true,
			'sticky_scroll'        => false,
			// youtube
			'hide_youtube'         => true,
			'lazy_load_youtube'    => false,
			'hide_logo'            => false,
			'is_locked'            => true,
		);

		// Preset not present in DB, so set skin to modern.
		if ( empty( $presets->total ) ) {
			$args['skin'] = 'modern';
		}

		$preset->updateOrCreate(
			array( 'slug' => 'simple' ),
			$args
		);

		return $preset;
	}

	public function seedMinimal() {
		$preset  = new Preset();
		$presets = $preset->fetch( array( 'slug' => 'minimal' ) );

		$args = array(
			'name'                 => __( 'Minimal', 'presto-player' ),
			'slug'                 => 'minimal',
			'icon'                 => 'controls-play',
			'play-large'           => true,
			'rewind'               => false,
			'play'                 => false,
			'fast-forward'         => false,
			'progress'             => false,
			'current-time'         => false,
			'mute'                 => false,
			'volume'               => false,
			'speed'                => false,
			'pip'                  => false,
			'fullscreen'           => false,
			'captions'             => false,
			// behavior
			'save_player_position' => false,
			'auto_hide'            => false,
			'reset_on_end'         => true,
			'sticky_scroll'        => false,
			// youtube
			'hide_youtube'         => true,
			'lazy_load_youtube'    => false,
			'hide_logo'            => true,
			'is_locked'            => true,
		);

		// Preset not present in DB, so set skin to modern.
		if ( empty( $presets->total ) ) {
			$args['skin'] = 'modern';
		}

		$preset->updateOrCreate(
			array( 'slug' => 'minimal' ),
			$args
		);

		return $preset;
	}
}
