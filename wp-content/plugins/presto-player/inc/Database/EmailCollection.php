<?php

namespace PrestoPlayer\Database;

use PrestoPlayer\Database\Table;

class EmailCollection {

	protected $table;
	protected $version = 1;
	protected $name    = 'presto_player_email_collection';

	public function __construct( Table $table ) {
		$this->table = $table;
	}

	public function getName() {
		global $wpdb;
		return $wpdb->prefix . $this->name;
	}

	/**
	 * Add videos table
	 * This is used for global video analytics
	 *
	 * @return void
	 */
	public function install() {
		return $this->table->create(
			$this->name,
			'
            id bigint(20) unsigned NOT NULL auto_increment,
            enabled boolean DEFAULT 0 NOT NULL,
            behavior varchar(155) NOT NULL,
            percentage bigint(20) NULL,
            allow_skip boolean DEFAULT 0 NOT NULL,
            headline varchar(155) NOT NULL,
            bottom_text varchar(155) NOT NULL,
            button_text varchar(155) NOT NULL,
            preset_id bigint(20) NULL,
            border_radius bigint(20) NOT NULL,
            email_provider varchar(155) NULL,
            email_provider_list varchar(155) NULL,
            email_provider_tag varchar(155) NULL,
            created_by bigint(20) unsigned NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
            updated_at TIMESTAMP NOT NULL,
            deleted_at TIMESTAMP NULL,
            PRIMARY KEY  (id),
            KEY preset_id (preset_id)
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
}
