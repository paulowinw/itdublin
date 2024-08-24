<?php

namespace PrestoPlayer\Database;

use PrestoPlayer\Database\Table;

class Webhooks {

	protected $table;

	protected $version = 1;

	protected $name = 'presto_player_webhooks';

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
            url varchar(255) NULL,
            method varchar(155) NULL,
            email_name varchar(155) NULL,
            headers varchar(255) NULL,
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
