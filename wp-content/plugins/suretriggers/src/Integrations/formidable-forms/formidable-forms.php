<?php
/**
 * FormidableForms core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\FormidableForms;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\FormidableForms
 */
class FormidableForms extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'FormidableForms';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'FormidableForms', 'suretriggers' );
		$this->description = __( 'A WordPress form builder plugin that lets you build single or multi-page contact forms. ', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/formidableforms.png';
		parent::__construct();
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'FrmHooksController' );
	}

}

IntegrationsController::register( FormidableForms::class );
