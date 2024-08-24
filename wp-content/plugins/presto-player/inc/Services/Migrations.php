<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Database\Migrations as DatabaseMigrations;

class Migrations {


	public function register() {
		DatabaseMigrations::run();
	}
}
