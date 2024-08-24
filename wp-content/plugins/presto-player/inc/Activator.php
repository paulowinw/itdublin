<?php

namespace PrestoPlayer;

use PrestoPlayer\Files;
use PrestoPlayer\Database\Migrations;

class Activator {

	public static function activate() {
		// run migrations
		Migrations::run();

		// file stuff
		$activator = new Files();
		$activator->addPrivateFolder();

		/**
		 * Reset rewrite rules to avoid go to permalinks page
		 * through deleting the database options to force WP to do it
		 * because of on activation not work well flush_rewrite_rules()
		 */
		delete_option( 'rewrite_rules' );
	}
}
