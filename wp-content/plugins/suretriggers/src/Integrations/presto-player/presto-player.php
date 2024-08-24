<?php
/**
 * PrestoPlayer core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\PrestoPlayer;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\PrestoPlayer
 */
class PrestoPlayer extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'PrestoPlayer';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'PrestoPlayer', 'suretriggers' );
		$this->description = __( 'Connect with your fans, faster your community.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/presto-player.svg';

		parent::__construct();
	}

	/**
	 * Is Plugin dependent plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return function_exists( 'presto_player_plugin' );
	}
}

IntegrationsController::register( PrestoPlayer::class );
