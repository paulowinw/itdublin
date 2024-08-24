<?php

namespace PrestoPlayer\Models;

class Preset extends Model {

	/**
	 * Table used to access db
	 *
	 * @var string
	 */
	protected $table = 'presto_player_presets';

	/**
	 * Model Schema
	 *
	 * @var array
	 */
	public function schema() {
		return array(
			'id'                     => array(
				'type' => 'integer',
			),
			'name'                   => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'slug'                   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_title',
			),
			'icon'                   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'skin'                   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'caption_style'          => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'caption_background'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_hex_color',
			),
			'play'                   => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'play-large'             => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'rewind'                 => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'fast-forward'           => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'progress'               => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'current-time'           => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'mute'                   => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'volume'                 => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'speed'                  => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'pip'                    => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'fullscreen'             => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'captions'               => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'reset_on_end'           => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'auto_hide'              => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'show_time_elapsed'      => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'captions_enabled'       => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'sticky_scroll'          => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'sticky_scroll_position' => array(
				'type'    => 'string',
				'default' => 'bottom right',
			),
			'on_video_end'           => array(
				'type'    => 'string',
				'default' => 'select',
			),
			'play_video_viewport'    => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'save_player_position'   => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'hide_youtube'           => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'lazy_load_youtube'      => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'hide_logo'              => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'border_radius'          => array(
				'type'    => 'integer',
				'default' => 0,
			),
			'cta'                    => array(
				'type' => 'array',
			),
			'watermark'              => array(
				'type' => 'array',
			),
			'search'                 => array(
				'type' => 'array',
			),
			'email_collection'       => array(
				'type' => 'array',
			),
			'action_bar'             => array(
				'type' => 'array',
			),
			'is_locked'              => array(
				'type'    => 'boolean',
				'default' => false,
			),
			'created_by'             => array(
				'type'    => 'integer',
				'default' => get_current_user_id(),
			),
			'created_at'             => array(
				'type' => 'string',
			),
			'updated_at'             => array(
				'type'    => 'string',
				'default' => current_time( 'mysql' ),
			),
			'deleted_at'             => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * These attributes are queryable
	 *
	 * @var array
	 */
	protected $queryable = array( 'slug' );

	/**
	 * Create a preset in the db
	 *
	 * @param  array $args
	 * @return integer
	 */
	public function create( $args = array() ) {
		// name is required
		if ( empty( $args['name'] ) ) {
			return new \WP_Error( 'missing_parameter', __( 'You must enter a name for the preset.', 'presto-player' ) );
		}

		// generate slug on the fly
		$args['slug'] = ! empty( $args['slug'] ) ? $args['slug'] : sanitize_title( $args['name'] );

		// create
		return parent::create( $args );
	}
}
