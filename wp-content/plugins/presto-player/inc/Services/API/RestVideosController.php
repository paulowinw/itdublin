<?php

namespace PrestoPlayer\Services\API;

use PrestoPlayer\Models\ReusableVideo;
use PrestoPlayer\Models\Video;

class RestVideosController extends \WP_REST_Controller {

	/**
	 * Our namespace
	 *
	 * @var string
	 */
	protected $namespace = 'presto-player';

	/**
	 * API Version
	 *
	 * @var string
	 */
	protected $version = 'v1';

	/**
	 * Endpoint base
	 *
	 * @var string
	 */
	protected $base = 'videos';

	/**
	 * Register controller
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register videos routes
	 *
	 * @return void
	 */
	public function register_routes() {
		// create
		register_rest_route(
			"{$this->namespace}/{$this->version}",
			'/' . $this->base,
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( true ),
				),
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => array(),
				),
				'schema' => array( $this, 'get_schema' ),
			)
		);

		// get item
		register_rest_route(
			"{$this->namespace}/{$this->version}",
			'/' . $this->base . '/(?P<id>\d+)',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'id'      => array(
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
						),
						'context' => array(
							'default' => 'view',
						),
					),
				),
				array(
					'methods'             => \WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'update_item_permissions_check' ),
					'args'                => $this->get_endpoint_args_for_item_schema( false ),
				),
				'schema' => array( $this, 'get_schema' ),
			)
		);

		// return the video block details from the REST API request.
		register_rest_field(
			'pp_video_block',
			'details',
			array(
				'get_callback' => array( $this, 'getVideoDetails' ),
				'schema'       => null,
			)
		);
	}

	/**
	 * Get Video Details
	 *
	 * @param string $object     Rest Object.
	 * @param string $field_name Rest Field.
	 * @param array  $request    Rest Request.
	 *
	 * @return array             Get the video details array
	 */
	public function getVideoDetails( $object = '', $field_name = '', $request = array() ) {
		$post_id = (int) $object['id'];
		if ( ! $post_id ) {
			return array();
		}
		return ( new ReusableVideo( $post_id ) )->getAttributes();
	}

	/**
	 * API Schema
	 *
	 * @return array
	 */
	public function get_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'video',
			'type'       => 'object',
			'properties' => array(
				'id'            => array(
					'description' => esc_html__( 'Unique identifier for the object.', 'presto-player' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit', 'embed' ),
					'readonly'    => true,
				),
				'title'         => array(
					'description'       => esc_html__( 'Title of the video.', 'presto-player' ),
					'type'              => 'string',
					'validate_callback' => 'is_string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'type'          => array(
					'description'       => esc_html__( 'Slug for the video type (one of youtube, vimeo, attachment, link)', 'presto-player' ),
					'type'              => 'string',
					'required'          => true,
					'validate_callback' => 'is_string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'external_id'   => array(
					'description'       => esc_html__( 'External unique id.', 'presto-player' ),
					'type'              => 'string',
					'validate_callback' => 'is_string',
					'sanitize_callback' => 'sanitize_text_field',
				),
				'attachment_id' => array(
					'description' => esc_html__( 'The id of the attachment.', 'presto-player' ),
					'type'        => 'integer',
				),
				'post_id'       => array(
					'description' => esc_html__( 'The post the video is attached to.', 'presto-player' ),
					'type'        => 'integer',
				),
				'src'           => array(
					'description'       => esc_html__( 'Video source url.', 'presto-player' ),
					'type'              => 'string',
					'validate_callback' => 'is_string',
					'sanitize_callback' => 'esc_url_raw',
				),
				'deleted_at'    => array(
					'type'     => 'string',
					'readonly' => true,
				),
				'updated_at'    => array(
					'type'     => 'string',
					'readonly' => true,
				),
				'created_at'    => array(
					'type'     => 'string',
					'readonly' => true,
				),
			),
		);
	}

	/**
	 * Create item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		// which video to first or create?
		$where = array( 'src' => $item['src'] );

		// if there's an external video id
		if ( $item['external_id'] ) {
			$where = array( 'external_id' => $item['external_id'] );
		} elseif ( $item['attachment_id'] ) {
			$where = array( 'attachment_id' => $item['attachment_id'] );
		}

		// create video
		$video = new Video();
		$video->updateOrCreate( $where, $item );

		$data = $this->prepare_item_for_response( $video->toArray(), $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( ! empty( $data ) ) {
			return new \WP_REST_Response( $data, 200 );
		}

		return new \WP_Error( 'cant-create', __( 'Cannot create video.', 'presto-player' ), array( 'status' => 500 ) );
	}

	/**
	 * Get item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		// get video
		$video = new Video( $request['id'] );
		if ( ! $video->created_at ) {
			return new \WP_Error( 'not_found', 'This video does not exist', 404 );
		}

		$data = $this->prepare_item_for_response( $video->toArray(), $request );
		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( ! empty( $data ) ) {
			return new \WP_REST_Response( $data, 200 );
		}

		return new \WP_Error( 'cant-get', __( 'Cannot get video.', 'presto-player' ), array( 'status' => 500 ) );
	}

	/**
	 * Get a collection of items
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$video = new Video();
		$items = $video->fetch(
			array(
				'per_page' => 10000,
				'order_by' => array(
					'created_at' => 'ASC',
				),
			)
		);
		if ( is_wp_error( $items ) ) {
			return $items;
		}

		if ( ! isset( $items->data ) ) {
			return new \WP_Error( 'error', 'Something went wrong' );
		}

		foreach ( $items->data as $item ) {
			$itemdata = $this->prepare_item_for_response( $item->toArray(), $request );
			$data[]   = $this->prepare_response_for_collection( $itemdata );
		}
		return new \WP_REST_Response( $data, 200 );
	}

	public function update_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		$video   = new Video( $request['id'] );
		$updated = $video->update( $item );

		// bail early on error
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$data = $this->prepare_item_for_response( $video->toArray(), $request );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( ! empty( $data ) ) {
			return new \WP_REST_Response( $data, 200 );
		}

		return new \WP_Error( 'cant-update', __( 'Cannot update video.', 'presto-player' ), array( 'status' => 500 ) );
	}


	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		return $this->get_item_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Prepare the item for create or update operation
	 * Ensures we're only passing specific fields and double-checks sanitization
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		$prepared = array(
			'type'          => sanitize_text_field( $request['type'] ),
			'external_id'   => sanitize_text_field( $request['external_id'] ),
			'attachment_id' => (int) $request['attachment_id'],
			'post_id'       => (int) $request['post_id'],
			'src'           => esc_url_raw( $request['src'] ),
		);

		if ( ! empty( $request['title'] ) ) {
			$prepared['title'] = sanitize_text_field( $request['title'] );
		}

		return apply_filters( 'presto_player_rest_prepared_database_item', $prepared );
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed           $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {
		$schema   = $this->get_schema();
		$prepared = array();
		foreach ( $item as $name => $value ) {
			if ( ! empty( $schema['properties'][ $name ] ) ) {
				$prepared[ $name ] = rest_sanitize_value_from_schema( $value, $schema['properties'][ $name ], $name );
			}
		}

		return apply_filters( 'presto_player_rest_prepared_response_item', $prepared );
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'     => array(
				'description'       => 'Current page of the collection.',
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description'       => 'Maximum number of items to be returned in result set.',
				'type'              => 'integer',
				'default'           => 10,
				'sanitize_callback' => 'absint',
			),
			'search'   => array(
				'description'       => 'Limit results to those matching a string.',
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);
	}
}
