<?php

use PrestoPlayer\Services\Scripts;
use PrestoPlayer\Models\ReusableVideo;

class DiviPrestoPlayer extends ET_Builder_Module {

	public $slug       = 'prpl_presto_player';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => '',
		'author'     => '',
		'author_uri' => '',
	);

	public function init() {
		$this->name = esc_html__( 'Presto Player Media', 'presto-player' );
		// TODO add icon support
	}

	public function get_fields() {
		return array(
			'video_id'     => array(
				'label'           => esc_html__( 'Choose Media', 'presto-player' ),
				'type'            => 'prpl_video_selector',
				'option_category' => 'basic_option',
				'description'     => esc_html__( 'Select from the media hub.', 'presto-player' ),
				'toggle_slug'     => 'image_video', // https://www.elegantthemes.com/documentation/developers/divi-module/module-settings-groups/
			),
			'url_override' => array(
				'label'           => esc_html__( 'Dynamic URL Override', 'presto-player' ),
				'type'            => 'text',
				'description'     => esc_html__( 'Please choose media above before selecting dynamic URL override.', 'presto-player' ),
				'dynamic_content' => 'text',
				'option_category' => 'basic_option',
				'toggle_slug'     => 'image_video',
			),
		);
	}

	// https://www.elegantthemes.com/documentation/developers/divi-module/advanced-field-types-for-module-settings/
	public function get_advanced_fields_config() {
		return array(
			'background'   => false,
			'link_options' => false,
		);
	}

	public function render( $attrs, $content = null, $render_slug = null ) {
		// global is the most reliable between page builders
		global $load_presto_js;
		$load_presto_js = true;
		( new Scripts() )->blockAssets(); // enqueue block assets

		$video     = new ReusableVideo( $this->props['video_id'] );
		$overrides = array();
		if ( $this->props['url_override'] ) {
			$overrides['src'] = $this->props['url_override'];
		}
		$render = $video->renderBlock( $overrides );
		return $render ? $render : null;
	}
}

new DiviPrestoPlayer();
