<?php
/**
 * LearnDashAchievements core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\LearnDashAchievements;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\LearnDashAchievements
 */
class LearnDashAchievements extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'LearnDashAchievements';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'LearnDash Achievements', 'suretriggers' );
		$this->description = __( 'The most powerful learning management system for WordPress. LearnDash Achievements empowers you to recognize and celebrate your learners` accomplishments with customizable rewards and achievements.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/learnDash-achievements.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'SFWD_LMS' ) && class_exists( '\LearnDash_Achievements' );
	}
}

IntegrationsController::register( LearnDashAchievements::class );
