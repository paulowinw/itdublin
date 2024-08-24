<?php
/**
 * Voxel integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\Voxel;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\Voxel
 */
class Voxel extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'Voxel';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Voxel', 'suretriggers' );
		$this->description = __( 'Voxel is a complete no code solution in a single packageto create WordPress dynamic sites.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/voxel.svg';

		parent::__construct();
	}

	/**
	 * Update post.
	 *
	 * @access public
	 * @since 1.0
	 * @param array  $fields    Workflow step fields.
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type.
	 * @return array|bool|string
	 */
	public static function voxel_update_post( $fields, $post_id, $post_type ) {
		if ( ! class_exists( 'Voxel\Post' ) || ! class_exists( 'Voxel\Post_Type' ) ) {
			return [];
		}
		$post_title = isset( $fields['title'] ) && '' !== $fields['title'] ? $fields['title'] : '';
		$post       = \Voxel\Post::force_get( $post_id );
		if ( ! $post ) {
			return wp_json_encode(
				[
					'success' => false,
					'message' => esc_attr__( 'Post not found', 'suretriggers' ),
				]
			);
		}
		if ( $post_title ) {
			$args = [
				'ID'         => $post_id,
				'post_title' => $post_title,
			];
			wp_update_post( $args );
		}
		$post_type   = \Voxel\Post_Type::get( $post_type );
		$post_fields = $post_type->get_fields();
		$field_opts  = self::get_fields();

		foreach ( $post_fields as $key => $field ) {
			$post_fields[ $key ] = $field->get_props();
		}

		// Loop through the post fields.
		foreach ( $post_fields as $key => $field ) {
			$field_key  = $key;
			$field_type = $field['type'];
			$post_field = $post->get_field( $field_key );
			// If field is not available, then skip.
			if ( ! $post_field ) {
				continue;
			}

			// If field is ui-step, ui-html and ui-heading, then skip.
			if ( in_array( $field_type, [ 'ui-step', 'ui-html', 'ui-heading', 'ui-image' ], true ) ) {
				continue;
			}

			$field_inputs = $field_opts[ $field_type ]['fields'];
			$field_value  = '';

			// If input field is location, then update the location.
			if ( 'location' === $field_key ) {
				$location_values = $field_opts[ $field_key ]['value'];
				$gallery_value   = [];

				foreach ( $location_values as $value_item => $item_value ) {
					if ( isset( $fields[ $field_key . '_' . $value_item ] ) ) {
						$location_value[ $value_item ] = $fields[ $field_key . '_' . $value_item ];
					}
				}

				if ( ! empty( $location_value ) ) {
					$post_field->update( $location_value );
				}

				continue;
			}

			// If field inputs are more than one, then get the value from the inputs.
			if ( count( $field_inputs ) > 1 ) {
				$field_value = [];
				foreach ( $field_inputs as $input_key => $input_field ) {
					if ( isset( $fields[ $field_key . '_' . $input_field['key'] ] ) ) {
						$field_value[ $field_key ] = $fields[ $field_key . '_' . $input_field['key'] ];
					}
				}
			}

			// Update work hours field.
			if ( 'work-hours' === $field_type ) {
				$work_hours_value = [];
				$work_days_value  = [];
				$field_value      = [];

				foreach ( $field_inputs as $input_key => $input_field ) {
					if ( 'work_days' === $input_field['key'] && isset( $fields[ $field_key . '_' . $input_field['key'] ] ) ) {
						$work_days_value = explode( ',', $fields[ $field_key . '_' . $input_field['key'] ] );

						$field_value['days'] = array_map(
							function( $day ) {
								return trim( $day );
							},
							$work_days_value
						);
					}

					if ( 'work_hours' === $input_field['key'] && isset( $fields[ $field_key . '_' . $input_field['key'] ] ) && '' !== trim( $fields[ $field_key . '_' . $input_field['key'] ] ) ) {
						$work_hours_value = explode( ',', $fields[ $field_key . '_' . $input_field['key'] ] );

						foreach ( $work_hours_value as $wkey => $wvalue ) {
							$work_hours_item = explode( '-', $wvalue );

							if ( 2 === count( $work_hours_item ) ) {
								$work_hours_value[ $wkey ] = [
									'from' => trim( $work_hours_item[0] ),
									'to'   => trim( $work_hours_item[1] ),
								];
							}
						}

						$field_value['hours'] = $work_hours_value;
					}

					if ( 'work_status' === $input_field['key'] && isset( $fields[ $field_key . '_' . $input_field['key'] ] ) ) {
						$field_value['status'] = $fields[ $field_key . '_' . $input_field['key'] ];
					}
				}

				if ( ! empty( $field_value ) ) {
					$post_field->update( [ $field_value ] );
				}

				continue;
			}

			// Update event-date field.
			if ( 'recurring-date' === $field_type || 'event-date' === $field_type ) {
				$event_date_value = [];
				$field_value      = [];

				if ( isset( $fields[ $field_key . '_event_start_date' ] ) ) {
					$event_date_value['start'] = $fields[ $field_key . '_event_start_date' ];
				}

				if ( isset( $fields[ $field_key . '_event_end_date' ] ) ) {
					$event_date_value['end'] = $fields[ $field_key . '_event_end_date' ];
				}

				if ( isset( $fields[ $field_key . '_event_frequency' ] ) ) {
					$event_date_value['frequency'] = $fields[ $field_key . '_event_frequency' ];
				}

				if ( isset( $fields[ $field_key . '_repeat_every' ] ) ) {
					$event_date_value['unit'] = $fields[ $field_key . '_repeat_every' ];
				}

				if ( isset( $fields[ $field_key . '_event_until' ] ) ) {
					$event_date_value['until'] = $fields[ $field_key . '_event_until' ];
				}

				if ( ! empty( $event_date_value ) ) {
					$post_field->update( [ $event_date_value ] );
				}

				continue;
			}

			// If field is available in the fields, then update the post field.
			if ( isset( $fields[ $field_key ] ) ) {
				$field_value = $fields[ $field_key ];
				if ( '' != $field_value ) {
					if ( in_array( $field_type, [ 'file', 'image', 'profile-avatar' ], true ) ) {
						$field_value = [
							[
								'source'  => 'existing',
								'file_id' => (int) $field_value,
							],
						];
					} elseif ( 'post-relation' === $field_type ) {
						$field_value = array_map(
							function( $post_id ) {
								return (int) $post_id;
							},
							explode( ',', $field_value )
						);
					} elseif ( 'taxonomy' === $field_type ) {
						$field_value = explode( ',', $field_value );
					}
					// If value is boolean false, then set it to false.
					if ( 'false' === $field_value ) {
						$field_value = false;
					}
					$post_field->update( $field_value );
				}
			}
		}
		return true;
	}

	/**
	 * Voxel fields with types and input requirements.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public static function get_fields() {
		return [
			// Post type fields.
			'title'           => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'title',
						'label' => esc_attr__( 'Title', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'description'     => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'description',
						'label' => esc_attr__( 'Description', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'timezone'        => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'timezone',
						'label' => esc_attr__( 'Timezone', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'location'        => [
				'type'   => 'array',
				'value'  => [
					'address'   => '',
					'latitude'  => '',
					'longitude' => '',
				],
				'fields' => [
					[
						'key'   => 'address',
						'label' => esc_attr__( 'Address', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'latitude',
						'label' => esc_attr__( 'Latitude', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'longitude',
						'label' => esc_attr__( 'Longitude', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'email'           => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'email',
						'label' => esc_attr__( 'Email', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'logo'            => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image ID', 'suretriggers' ),
					],
				],
			],
			'cover-image'     => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image ID', 'suretriggers' ),
					],
				],
			],
			'gallery'         => [
				'type'   => 'array',
				'value'  => [
					[ 0 ],
				],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image IDs, separated by comma', 'suretriggers' ),
					],
				],
			],
			'featured-image'  => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image IDs, separated by comma', 'suretriggers' ),
					],
				],
			],
			'website'         => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'website',
						'label' => esc_attr__( 'Website URL', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'phone-number'    => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'phone_number',
						'label' => esc_attr__( 'Phone Number', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'event-date'      => [
				'type'   => 'array',
				'value'  => [
					'start'     => '',
					'end'       => '',
					'frequency' => '',
					'unit'      => '',
					'until'     => '',
				],
				'fields' => [
					[
						'key'   => 'event_start_date',
						'label' => esc_attr__( 'Event Start Date', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'event_end_date',
						'label' => esc_attr__( 'Event End Date', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'event_frequency',
						'label' => esc_attr__( 'Event Frequency', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'repeat_every',
						'label' => esc_attr__( 'Event Unit', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Accepted values: day, week, month, year', 'suretriggers' ),
					],
					[
						'key'   => 'event_until',
						'label' => esc_attr__( 'Event Until', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'work-hours'      => [
				'type'   => 'array',
				'value'  => [
					[
						'days',
						'status',
						'hours',
					],
				],
				'fields' => [
					[
						'key'   => 'work_days',
						'label' => esc_attr__( 'Work Days', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Accepted values: mon, tue, wed, thu, fri, sat, sun', 'suretriggers' ),
					],
					[
						'key'   => 'work_hours',
						'label' => esc_attr__( 'Work Hours', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Enter value pairs as start and end time, separated by dash. For multiple pairs, use comma separator. Eg. 09:00-17:00, 09:00-12:00', 'suretriggers' ),
					],
					[
						'key'   => 'work_status',
						'label' => esc_attr__( 'Work Status', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Accepted values: hours, open, close, appointments_only', 'suretriggers' ),
					],
				],
			],
			'profile-picture' => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image ID', 'suretriggers' ),
					],
				],
			],
			'profile-avatar'  => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image ID', 'suretriggers' ),
					],
				],
			],
			'profile-name'    => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'profile_name',
						'label' => esc_attr__( 'Profile Name', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			// Custom field types.
			'text'            => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'text',
						'label' => esc_attr__( 'Text', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'number'          => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'number',
						'label' => esc_attr__( 'Number', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'switcher'        => [
				'type'   => 'switcher',
				'value'  => true,
				'fields' => [
					[
						'key'   => 'switcher',
						'label' => esc_attr__( 'Switcher', 'suretriggers' ),
						'type'  => 'yes/no',
					],
				],
			],
			'texteditor'      => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'text_editor',
						'label' => esc_attr__( 'Text Editor', 'suretriggers' ),
						'type'  => 'textarea',
					],
				],
			],
			'taxonomy'        => [
				'type'   => 'array',
				'value'  => [],
				'fields' => [
					[
						'key'   => 'taxonomy',
						'label' => esc_attr__( 'Taxonomy Slug', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide related taxonomy slug(s). If multiples allowed, separate with comma.', 'suretriggers' ),
					],
				],
			],
			'product'         => [
				'type'   => 'array',
				'value'  => [],
				'fields' => [
					[
						'key'   => 'product',
						'label' => esc_attr__( 'Product ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Product ID', 'suretriggers' ),
					],
				],
			],
			'phone'           => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'phone',
						'label' => esc_attr__( 'Phone', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'url'             => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'url',
						'label' => esc_attr__( 'URL', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'image'           => [
				'type'   => 'array',
				'value'  => [
					'url' => '',
					'alt' => '',
				],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image IDs. Separate with comma.', 'suretriggers' ),
					],
				],
			],
			'file'            => [
				'type'   => 'array',
				'value'  => [
					'url' => '',
					'alt' => '',
				],
				'fields' => [
					[
						'key'   => 'file_id',
						'label' => esc_attr__( 'File ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide File ID', 'suretriggers' ),
					],
				],
			],
			'repeater'        => [
				'type'   => 'array',
				'value'  => [
					[
						'url' => '',
						'alt' => '',
					],
				],
				'fields' => [
					[
						'key'   => 'repeater',
						'label' => esc_attr__( 'Repeater', 'suretriggers' ),
						'type'  => 'repeater',
					],
				],
			],
			'recurring-date'  => [
				'type'   => 'array',
				'value'  => [
					[
						'start'     => '',
						'end'       => '',
						'frequency' => '',
						'unit'      => '',
						'until'     => '',
					],
				],
				'fields' => [
					[
						'key'   => 'event_start_date',
						'label' => esc_attr__( 'Event Start Date', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'event_end_date',
						'label' => esc_attr__( 'Event End Date', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'event_frequency',
						'label' => esc_attr__( 'Event Frequency', 'suretriggers' ),
						'type'  => 'text',
					],
					[
						'key'   => 'repeat_every',
						'label' => esc_attr__( 'Event Unit', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Accepted values: day, week, month, year', 'suretriggers' ),
					],
					[
						'key'   => 'event_until',
						'label' => esc_attr__( 'Event Until', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'post-relation'   => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'post_id',
						'label' => esc_attr__( 'Post ID', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Post ID', 'suretriggers' ),
					],
				],
			],
			'date'            => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'date',
						'label' => esc_attr__( 'Date', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			'select'          => [
				'type'   => 'text',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'select',
						'label' => esc_attr__( 'Select', 'suretriggers' ),
						'type'  => 'select',
					],
				],
			],
			'color'           => [
				'type'   => 'color',
				'value'  => '',
				'fields' => [
					[
						'key'   => 'color',
						'label' => esc_attr__( 'Color', 'suretriggers' ),
						'type'  => 'text',
					],
				],
			],
			// Layout fields.
			'ui-image'        => [
				'type'   => 'array',
				'value'  => [ 0 ],
				'fields' => [
					[
						'key'   => 'image_id',
						'label' => esc_attr__( 'Image IDs', 'suretriggers' ),
						'type'  => 'text',
						'help'  => esc_attr__( 'Provide Image IDs. Separate with comma.', 'suretriggers' ),
					],
				],
			],
		];
	}

	/**
	 * Sanitize the content.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $content Content to sanitize.
	 * @return string|bool
	 */
	public static function sanitize_content( $content ) {
		if ( ! class_exists( 'Voxel\Timeline\Fields\Status_Message_Field' ) ) {
			return false;
		}
		$field   = new \Voxel\Timeline\Fields\Status_Message_Field();
		$content = $field->sanitize( $content );
		$field->validate( $content );

		return $content;
	}

	/**
	 * Sanitize the files.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $files Files to sanitize.
	 * @return array
	 */
	public static function sanitize_files( $files ) {
		if ( ! function_exists( 'Voxel\get' ) ) {
			return [];
		}
		if ( ! \Voxel\get( 'settings.timeline.posts.images.enabled', true ) || ! class_exists( 'Voxel\Timeline\Fields\Status_Files_Field' ) ) {
			return [];
		}

		$field = new \Voxel\Timeline\Fields\Status_Files_Field();
		$files = $field->sanitize( $files );
		$field->validate( $files );
		$file_ids = $field->prepare_for_storage( $files );

		return $file_ids;
	}

	/**
	 * Get Post Fields and it's values.
	 *
	 * @access public
	 * @since 1.0
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get_post_fields( $post_id ) {
		if ( ! class_exists( 'Voxel\Post' ) ) {
			return [];
		}
		$post         = \Voxel\Post::force_get( $post_id );
		$context      = [];
		$context_data = [];
		if ( ! empty( $post ) ) {
			$fields = $post->get_fields();
			if ( is_array( $fields ) && ! empty( $fields ) ) {
				foreach ( $fields as $field ) {
					$field_key     = $field->get_key();
					$field_type    = $field->get_type();
					$field_value   = $field->get_value();
					$field_content = null;
					switch ( $field_type ) {
						case 'taxonomy':
							$field_content = join(
								', ',
								array_map(
									function( $term ) {
										return $term->get_label();
									},
									$field_value
								)
							);
							break;
						case 'location':
							$field_content = $field_value['address'];
							break;
						case 'work-hours':
							$hours = [];
							if ( is_array( $field_value ) && ! empty( $field_value ) ) {
								foreach ( $field_value as $work_hour ) {
									if ( 'hours' === $work_hour['status'] ) {
										foreach ( $work_hour['days'] as $day ) {
											foreach ( $work_hour['hours'] as $hour_key => $hour ) {
												$hours[ $day . '_' . $hour_key ] = $hour['from'] . '-' . $hour['to'];
											}
										}
									}
								}
							}
							$field_content                         = $field_value;
							$context[ $field_key . '_simplified' ] = wp_json_encode( $hours );
							break;
						case 'file':
						case 'image':
						case 'profile-avatar':
						case 'gallery':
							if ( is_array( $field_value ) ) {
								foreach ( $field_value as $file_key => $file_id ) {
									$field_content[ $field_key . '_' . $file_key . '_url' ] = wp_get_attachment_url( $file_id );
								}
							} else {
								$field_content[ $field_key . '_url' ] = wp_get_attachment_url( $field_value );
							}
							break;
						default:
							$field_content = $field_value;
							break;
					}
					$context[ $field_key ] = $field_content;
				}
			}
		}
		if ( ! empty( $context ) ) {
			foreach ( $context as $key => $value ) {
				$context_data[ 'field_' . $key ] = $value;
			}
		}
		return $context_data;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		$bricks_theme = wp_get_theme( 'voxel' );
		return $bricks_theme->exists();
	}
}

IntegrationsController::register( Voxel::class );
