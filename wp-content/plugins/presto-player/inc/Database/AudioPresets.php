<?php

namespace PrestoPlayer\Database;

use PrestoPlayer\Database\Table;

class AudioPresets {

	protected $table;

	protected $version = 20;

	protected $name = 'presto_player_audio_presets';

	public function __construct( Table $table ) {
		$this->table = $table;
	}

	public function getName() {
		global $wpdb;
		return $wpdb->prefix . $this->name;
	}

	/**
	 * Add relationships custom table
	 * This allows for simple, efficient queries
	 *
	 * @return void
	 */
	public function install() {
		return $this->table->create(
			$this->name,
			'
            id bigint(20) unsigned NOT NULL auto_increment,
            name varchar(155) NULL,
            slug varchar(155) NULL,
            icon varchar(155) NULL,
            skin varchar(155) NULL,
            rewind boolean DEFAULT 0 NOT NULL,
            play boolean DEFAULT 0 NOT NULL,
            `play-large` boolean DEFAULT 0 NOT NULL,
            `fast-forward` boolean DEFAULT 0 NOT NULL,
            progress boolean DEFAULT 0 NOT NULL,
            `current-time` boolean DEFAULT 0 NOT NULL,
            mute boolean DEFAULT 0 NOT NULL,
            volume boolean DEFAULT 0 NOT NULL,
            speed boolean DEFAULT 0 NOT NULL,
            pip boolean DEFAULT 0 NOT NULL,
            reset_on_end boolean DEFAULT 0 NOT NULL,
            save_player_position boolean DEFAULT 0 NOT NULL,
            sticky_scroll boolean DEFAULT 0 NOT NULL,
            sticky_scroll_position varchar(16) DEFAULT NULL,
            on_video_end varchar(16) DEFAULT NULL,
            play_video_viewport boolean DEFAULT 0 NOT NULL,
            show_time_elapsed boolean DEFAULT 0 NOT NULL,
            hide_logo boolean DEFAULT 0 NOT NULL,
            border_radius bigint(20) unsigned NULL,
            background_color varchar(155) NULL,
            control_color varchar(155) NULL,
            is_locked boolean DEFAULT 0 NOT NULL,
            cta LONGTEXT NOT NULL,
            email_collection LONGTEXT NOT NULL,
            action_bar LONGTEXT NOT NULL,
            created_by bigint(20) unsigned NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            updated_at TIMESTAMP NOT NULL,
            deleted_at TIMESTAMP NULL,
            PRIMARY KEY  (id),
            KEY name (name)
        ',
			$this->version
		);
	}

	/**
	 * Uninstall tables
	 *
	 * @return void
	 */
	public function uninstall() {
		$this->table->drop( $this->getName() );
	}

	public function exists() {
		return $this->table->exists( $this->name );
	}
}
