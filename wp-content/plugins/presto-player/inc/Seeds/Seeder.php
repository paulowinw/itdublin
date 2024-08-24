<?php

namespace PrestoPlayer\Seeds;

class Seeder {

	protected $seeders;

	public function __construct( PresetSeeder $presetSeeder, AudioPresetSeeder $audioPresetSeeder ) {
		$this->seeders[] = $presetSeeder;
		$this->seeders[] = $audioPresetSeeder;
	}

	public function register() {
		add_action( 'admin_init', array( $this, 'seed' ) );
	}

	public function seed() {
		foreach ( $this->seeders as $seeder ) {
			$seeder->run();
		}
	}
}
