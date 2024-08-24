<?php

namespace PrestoPlayer\Models;

class Webhook extends Model {

	/**
	 * Table used to access db
	 *
	 * @var string
	 */
	protected $table = 'presto_player_webhooks';

	/**
	 * Model Schema
	 *
	 * @var array
	 */
	public function schema() {
		return array(
			'id'         => array(
				'type' => 'integer',
			),
			'name'       => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'url'        => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_url',
			),
			'method'     => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email_name' => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'headers'    => array(
				'type' => 'array',
			),
			'created_by' => array(
				'type'    => 'integer',
				'default' => get_current_user_id(),
			),
			'created_at' => array(
				'type' => 'string',
			),
			'updated_at' => array(
				'type'    => 'string',
				'default' => current_time( 'mysql' ),
			),
			'deleted_at' => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * These attributes are queryable
	 *
	 * @var array
	 */
	protected $queryable = array( 'name' );

	/**
	 * Create a preset in the db
	 *
	 * @param array $args
	 * @return integer
	 */
	public function create( $args = array() ) {
		// name is required
		if ( empty( $args['name'] ) ) {
			return new \WP_Error( 'missing_parameter', __( 'You must enter a name for the webhook.', 'presto-player' ) );
		}

		// generate slug on the fly
		$args['name'] = ! empty( $args['name'] ) ? $args['name'] : sanitize_title( $args['name'] );

		// create
		return parent::create( $args );
	}
}
