<?php

namespace PrestoPlayer\Models;

use PrestoPlayer\Services\Blocks\VimeoBlockService;
use PrestoPlayer\Services\Blocks\YoutubeBlockService;

class Video extends Model {

	/**
	 * Table used to access db
	 *
	 * @var string
	 */
	protected $table = 'presto_player_videos';

	/**
	 * Model Schema
	 *
	 * @var array
	 */
	public function schema() {
		return array(
			'id'            => array(
				'type' => 'integer',
			),
			'title'         => array(
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post',
			),
			'type'          => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'src'           => array(
				'type'              => 'string',
				'sanitize_callback' => 'esc_url_raw',
			),
			'external_id'   => array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'attachment_id' => array(
				'type' => 'integer',
			),
			'post_id'       => array(
				'type' => 'integer',
			),
			'created_by'    => array(
				'type'    => 'integer',
				'default' => get_current_user_id(),
			),
			'created_at'    => array(
				'type' => 'string',
			),
			'updated_at'    => array(
				'type' => 'string',
			),
			'deleted_at'    => array(
				'type' => 'string',
			),
		);
	}

	/**
	 * These attributes are queryable
	 *
	 * @var array
	 */
	protected $queryable = array(
		'src',
		'video_id',
		'title',
		'type',
		'attachment_id',
		'external_id',
	);

	public function set( $args ) {
		parent::set( $args );

		if ( ! empty( $this->attributes->attachment_id ) ) {
			$title                   = get_the_title( $this->attributes->attachment_id );
			$src                     = wp_get_attachment_url( $this->attributes->attachment_id );
			$this->attributes->title = $title ? $title : $this->attributes->title;
			$this->attributes->src   = $src ? $src : $this->attributes->src;
		}

		return $this;
	}

	/**
	 * Get the videos embedded title from noembed.com
	 *
	 * @return int Post ID
	 */
	public function getEmbeddedTitle( $src = '' ) {
		if ( empty( $src ) ) {
			return '';
		}
		$response = wp_remote_get( 'https://noembed.com/embed?dataType=json&url=' . urlencode( $src ) );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$body         = wp_remote_retrieve_body( $response );
		$api_response = json_decode( $body, true );
		return $api_response['title'] ?? '';
	}

	/**
	 * Maybe auto-create title if not set
	 *
	 * @param  array $args
	 * @return array
	 */
	public function maybeAutoCreateTitle( $args ) {
		// remotely get the title if not provided
		if ( empty( $args['title'] ) && in_array( $args['type'], array( 'youtube', 'vimeo' ) ) ) {
			$title = $this->getEmbeddedTitle( $args['src'] );
			if ( ! is_wp_error( $title ) && ! empty( $title ) ) {
				$args['title'] = $title;
			}
		}

		// fallback to url
		$args['title'] = empty( $args['title'] ) ? $args['src'] : $args['title'];

		// return args.
		return $args;
	}

	/**
	 * Create a new video
	 *
	 * @param  array $args
	 * @return integer
	 */
	public function create( $args = array() ) {
		// required params
		if ( empty( $args['external_id'] ) && empty( $args['attachment_id'] ) && empty( $args['src'] ) ) {
			return new \WP_Error( 'invalid_parameters', 'You must enter an attachment_id, external_id or src.' );
		}

		$args = $this->maybeAutoCreateTitle( $args );

		// create
		return parent::create( $args );
	}

	/**
	 * Maybe auto-create title if not set
	 *
	 * @param  array $args
	 * @return void
	 */
	public function update( $args = array() ) {
		if ( ! empty( $args['attachment_id'] ) && ! empty( $args['title'] ) ) {
			wp_update_post(
				array(
					'ID'         => $args['attachment_id'],
					'post_title' => $args['title'],
				)
			);
		}
		return parent::update( $args );
	}

	/**
	 * Get the video's created at date.
	 *
	 * @return string Created At date
	 */
	public function getCreatedAt() {
		return $this->created_at;
	}

	/**
	 * Get the video title.
	 *
	 * @return string Title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Get the attachment id.
	 *
	 * @return int Attachment ID
	 */
	public function getAttachmentID() {
		return $this->attachment_id;
	}

	/**
	 * Get the attachment post title.
	 *
	 * @param int $attachment_id Attachment ID
	 *
	 * @return string|false Title or false if not found
	 */
	public function getAttachmentPostTitle( $attachment_id = null ) {
		if ( empty( $attachment_id ) ) {
			return false;
		}
		$attachment       = get_post( $attachment_id );
		$attachment_title = $attachment->post_title;
		if ( ! empty( $attachment_title ) ) {
			return $attachment_title;
		}
		return false;
	}
}
