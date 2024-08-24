<?php

namespace PrestoPlayer\Database;

use PrestoPlayer\Database\Videos;
use PrestoPlayer\Database\Visits;
use PrestoPlayer\Database\Presets;
use PrestoPlayer\Database\AudioPresets;
use PrestoPlayer\Database\EmailCollection;
use PrestoPlayer\Database\Upgrades\Upgrades;
use PrestoPlayer\Database\Upgrades\PerformanceUpgrade;
use PrestoPlayer\Services\RewriteRulesManager;

class Migrations {

	public static function run() {
		// order of this one is important
		$performance = new PerformanceUpgrade();
		$performance->migrate();

		$visits = new Visits( new Table() );
		$visits->install();

		$presets = new Presets( new Table() );
		$presets->install();

		$audio_presets = new AudioPresets( new Table() );
		$audio_presets->install();

		$videos = new Videos( new Table() );
		$videos->install();

		$videos = new EmailCollection( new Table() );
		$videos->install();

		$audio_presets = new Webhooks( new Table() );
		$audio_presets->install();

		$upgrades = new Upgrades();
		$upgrades->migrate();

		$rewrite_rules_manager = new RewriteRulesManager();
		$rewrite_rules_manager->bootstrap();
	}

	public static function remove() {
		$visits = new Visits( new Table() );
		$visits->uninstall();

		$presets = new Presets( new Table() );
		$presets->uninstall();

		$audio_presets = new AudioPresets( new Table() );
		$audio_presets->uninstall();

		$videos = new Videos( new Table() );
		$videos->uninstall();
	}
}
