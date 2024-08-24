<?php
/**
 * Integration base class file
 *
 * @package SureTrigger
 * @since 1.0.0
 */

namespace SureTriggers\Integrations;

use SureTriggers\Controllers\EventController;

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * Class Integrations
 *
 * @package SureTriggers\Integrations
 */
abstract class Integrations {

	/**
	 * ID of the integration
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Integration Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Integration Description
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Integration icon/logo URL
	 *
	 * @var string
	 */
	protected $icon_url;

	/**
	 * Contains configuration form fields.
	 *
	 * @var array
	 */
	protected $config_fields = [];

	/**
	 * Contains saved configurations
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Contains errors list
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * If the form should be verify or not.
	 *
	 * @var bool
	 */
	protected $form_validation = false;

	/**
	 * Get api key page URL
	 *
	 * @var null|bool
	 */
	protected $api_page_url = null;

	/**
	 * Contains it's actions list, if any. For the future usage
	 *
	 * @var array
	 */
	protected $actions = [];

	/**
	 * Contains it's triggers list, if any. For the future usage
	 *
	 * @var array
	 */
	protected $triggers = [];

	/**
	 * Integrations constructor.
	 */
	public function __construct() {
		$this->process_events();
	}

	/**
	 * Process and get all events
	 *
	 * @return void
	 */
	public function process_events() {
		$events = EventController::get_instance();

		if ( ! empty( $events->triggers[ $this->id ] ) ) {
			$this->triggers = $events->triggers[ $this->id ];
		}

		if ( ! empty( $events->actions[ $this->id ] ) ) {
			$this->actions = $events->actions[ $this->id ];
		}
	}

	/**
	 * If enabled or not
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) $this->is_plugin_installed();
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @return bool
	 */
	abstract public function is_plugin_installed();

	/**
	 * Returns ID
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns integration name
	 *
	 * @return string
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Returns integration description
	 *
	 * @return string
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Get the integration URL
	 *
	 * @return string
	 */
	public function get_icon_url() {
		return $this->icon_url;
	}

	/**
	 * Get config form fields
	 *
	 * @return array
	 */
	public function get_config_fields() {
		return $this->config_fields;
	}

	/**
	 * Get saved
	 *
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Get errors if any
	 *
	 * @return array
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Determine if the form is require validation.
	 *
	 * @return bool
	 */
	public function form_validation() {
		return $this->form_validation;
	}

	/**
	 * Get API key page URL
	 *
	 * @return bool
	 */
	public function get_api_page_url() {
		return $this->api_page_url;
	}

	/**
	 * Get actions if any
	 *
	 * @return array
	 */
	public function get_actions() {
		return $this->actions;
	}

	/**
	 * Get triggers if any
	 *
	 * @return array
	 */
	public function get_triggers() {
		return $this->triggers;
	}

	/**
	 * Default validation abstract method (optional)
	 *
	 * @param array $args Form input as $args.
	 *
	 * @return false
	 */
	public function validation( $args = [] ) {
		return false;
	}
}
