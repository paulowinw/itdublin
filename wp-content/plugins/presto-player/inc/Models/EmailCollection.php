<?php

namespace PrestoPlayer\Models;

class EmailCollection extends Model {

	/**
	 * Table used to access db
	 *
	 * @var string
	 */
	protected $table = 'presto_player_email_collection';

	/**
	 * Model Schema
	 *
	 * @var array
	 */
	public function schema() {
		return array(
			'id'                  => array(
				'type' => 'integer',
			),
			'enabled'             => array(
				'type' => 'boolean',
			),
			'behavior'            => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'percentage'          => array(
				'type' => 'integer',
			),
			'allow_skip'          => array(
				'type' => 'boolean',
			),
			'email_provider'      => array(
				'type' => 'string',
			),
			'email_provider_list' => array(
				'type' => 'string',
			),
			'email_provider_tag'  => array(
				'type' => 'string',
			),
			'headline'            => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'bottom_text'         => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'button_text'         => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'border_radius'       => array(
				'type' => 'integer',
			),
			'preset_id'           => array(
				'type' => 'integer',
			),
			'created_by'          => array(
				'type'    => 'integer',
				'default' => get_current_user_id(),
			),
			'created_at'          => array(
				'type' => 'string',
			),
			'updated_at'          => array(
				'type' => 'string',
			),
			'deleted_at'          => array(
				'type' => 'string',
			),
		);
	}
}
