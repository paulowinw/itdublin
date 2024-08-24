<?php
/**
 * PeepSo core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\PeepSo;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\PeepSo
 */
class PeepSo extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'PeepSo';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'PeepSo', 'suretriggers' );
		$this->description = __( 'PeepSo is a social network plugin for WordPress that allows you to quickly add a social network.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/peepso.svg';

		parent::__construct();
	}

	/**
	 * Get customer context data.
	 *
	 * @param int $post_id post details.
	 * @param int $activity_id activity id.
	 *
	 * @return array
	 */
	public static function get_pp_activity_context( $post_id, $activity_id ) {

		$pp_post                 = get_post( $post_id );
		$context['post_id']      = $pp_post->ID;
		$context['activity_id']  = $activity_id;
		$context['post_author']  = $pp_post->post_author;
		$context['post_content'] = $pp_post->post_content;
		$context['post_title']   = $pp_post->post_title;
		$context['post_excerpt'] = $pp_post->post_excerpt;
		$context['post_status']  = $pp_post->post_status;
		$context['post_type']    = $pp_post->post_type;

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'PeepSo' );
	}

}

IntegrationsController::register( PeepSo::class );
