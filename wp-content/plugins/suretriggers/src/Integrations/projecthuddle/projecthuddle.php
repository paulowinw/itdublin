<?php
/**
 * ProjectHuddle core integrations file
 *
 * @since   1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\ProjectHuddle;

use Project_Huddle;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\ProjectHuddle
 */
class ProjectHuddle extends Integrations {



	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'ProjectHuddle';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'SureFeedback', 'suretriggers' );
		$this->description = __( 'A WordPress plugin for Website & Design feedback.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/projecthuddle.png';
		add_action( 'ph_website_pre_rest_update_thread_attribute', [ $this, 'ph_after_comment_resolved' ], 10, 3 );
		parent::__construct();
	}

	/**
	 * On Comment resolved.
	 *
	 * @param string $attr Resolved.
	 * @param string $value Value.
	 * @param string $object Post object.
	 * @return void
	 */
	public function ph_after_comment_resolved( $attr, $value, $object ) {

		if ( 'resolved' !== $attr ) {
			return;
		}
		
		// if it is resolved, do something!
		if ( $value ) {
			$comment = $object;
			do_action( 'suretriggers_ph_after_comment_approval', $comment );
		}
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( '\Project_Huddle' );
	}

}

IntegrationsController::register( ProjectHuddle::class );
