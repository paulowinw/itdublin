<?php

namespace PrestoPlayer\Database;

use PrestoPlayer\Database\Table;

class Visits {

	protected $table;
	protected $version = 1;
	protected $name    = 'presto_player_visits';

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
			"
            id bigint(20) unsigned NOT NULL auto_increment,
            user_id bigint(20) unsigned NULL,
            duration bigint(20) unsigned NOT NULL,
            video_id bigint(20) unsigned NOT NULL,
            ip_address varchar(39) DEFAULT '' NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            updated_at TIMESTAMP NOT NULL,
            deleted_at TIMESTAMP NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY video_id (video_id),
            KEY ip_address (ip_address),
            KEY created_at (created_at),
            KEY updated_at (updated_at)
        ",
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
