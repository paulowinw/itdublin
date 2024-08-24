<?php
/**
 * FluentCRM core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\FluentCRM;

use FluentCrm\App\Models\Lists;
use FluentCrm\App\Models\Tag;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\FluentCRM
 */
class FluentCRM extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'FluentCRM';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'FluentCRM', 'suretriggers' );
		$this->description = __( 'FluentCRM is a Self Hosted Email Marketing Automation Plugin for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/fluentCRM.svg';

		parent::__construct();
	}

	/**
	 * Fetch tag data.
	 *
	 * @param int $tag_id tag id.
	 * @return mixed
	 */
	public function get_tag_data( $tag_id ) {
		$tag = Tag::where( 'id', $tag_id )->get();
		return $tag;
	}

	/**
	 * Fetch list data.
	 *
	 * @param int $list_id list data.
	 * @return mixed
	 */
	public function get_list_data( $list_id ) {
		$list = Lists::find( $list_id );
		return $list;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'FLUENTCRM' );
	}

}

IntegrationsController::register( FluentCRM::class );
