<?php
/**
 * LatePoint core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\LatePoint;

use Exception;
use OsAgentModel;
use OsBookingModel;
use OsCustomerModel;
use OsMetaModel;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\LatePoint
 */
class LatePoint extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'LatePoint';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'LatePoint', 'suretriggers' );
		$this->description = __( 'Appointment Scheduling Plugin for WordPress.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/late-point.svg';

		parent::__construct();
	}

	/**
	 * Create/Update booking.
	 *
	 * @param array $selected_options Selected options.
	 * @param bool  $is_update is update.
	 * @return array
	 * @throws Exception Exception.
	 */
	public static function create_or_update_booking( $selected_options, $is_update = false ) {

		if ( ! class_exists( 'OsBookingModel' ) || ! class_exists( 'OsCustomerModel' ) ) {
			throw new Exception( 'LatePoint plugin not installed.' );
		}

		if ( $is_update ) {
			$booking_id = isset( $selected_options['booking_id'] ) ? $selected_options['booking_id'] : null;
			if ( ! $booking_id ) {
				throw new Exception( 'Booking ID not provided.' );
			}

			$booking = new OsBookingModel( $booking_id );
			if ( ! isset( $booking->id ) || ! $booking->id ) {
				throw new Exception( 'Booking not found.' );
			}
			$old_booking = clone $booking;
		} else {
			$booking = new OsBookingModel();
		}

		$customer_type = isset( $selected_options['customer_type'] ) ? $selected_options['customer_type'] : 'new';
		$customer_id   = null;

		if ( 'existing' === $customer_type ) {
			$customer_id = isset( $selected_options['customer_id'] ) ? $selected_options['customer_id'] : null;
			if ( ! $customer_id ) {
				throw new Exception( 'Customer ID not provided.' );
			}
		}

		$start_date = isset( $selected_options['start_date'] ) ? gmdate( 'Y-m-d', strtotime( $selected_options['start_date'] ) ) : '';
		$start_time = isset( $selected_options['start_time'] ) ? gmdate( 'h:i a', strtotime( $selected_options['start_time'] ) ) : '';
		$end_time   = isset( $selected_options['end_time'] ) ? gmdate( 'h:i a', strtotime( $selected_options['end_time'] ) ) : '';

		$booking_params        = [
			'agent_id'         => isset( $selected_options['agent_id'] ) ? $selected_options['agent_id'] : null,
			'location_id'      => isset( $selected_options['agent_id'] ) ? $selected_options['agent_id'] : null,
			'status'           => isset( $selected_options['status'] ) ? $selected_options['status'] : '',
			'service_id'       => isset( $selected_options['service_id'] ) ? $selected_options['service_id'] : null,
			'start_date'       => $start_date,
			'start_time'       => [
				'formatted_value' => $start_time ? strtok( $start_time, ' ' ) : '',
				'ampm'            => $start_time ? substr( $start_time, -2 ) : '',
			],
			'end_time'         => [
				'formatted_value' => $end_time ? strtok( $end_time, ' ' ) : '',
				'ampm'            => $end_time ? substr( $end_time, -2 ) : '',
			],
			'customer_comment' => isset( $selected_options['customer_comment'] ) ? $selected_options['customer_comment'] : '',
			'payment_status'   => 'not_paid',
			'buffer_before'    => isset( $selected_options['buffer_before'] ) ? $selected_options['buffer_before'] : 0,
			'buffer_after'     => isset( $selected_options['buffer_after'] ) ? $selected_options['buffer_after'] : 0,
			'source_url'       => site_url(),
		];
		$booking_custom_fields = [];
		if ( ! empty( $selected_options['booking_fields'] ) ) {
			foreach ( $selected_options['booking_fields'] as $field ) {
				if ( is_array( $field ) && ! empty( $field ) ) {
					foreach ( $field as $key => $value ) {
						if ( false === strpos( $key, 'field_column' ) && '' !== $value ) {
							$booking_custom_fields[ $key ] = $value;
						}
					}
				}
			}
		}
		$total_attendies                   = $booking_custom_fields['total_attendies'] ? $booking_custom_fields['total_attendies'] : 1;
		$booking_params['total_attendies'] = $total_attendies;
		$booking_params['custom_fields']   = $booking_custom_fields;
		
		
		

		$booking->set_data( $booking_params );

		// Set custom end time/date if it was passed in params.
		if ( isset( $booking_params['end_time']['formatted_value'] ) ) { // @phpstan-ignore-line
			$booking->set_custom_end_time_and_date( $booking_params );
		}

		if ( 'new' === $customer_type ) {
			$customer_params = [
				'first_name' => isset( $selected_options['customer_first_name'] ) ? $selected_options['customer_first_name'] : '',
				'last_name'  => isset( $selected_options['customer_last_name'] ) ? $selected_options['customer_last_name'] : '',
				'email'      => isset( $selected_options['customer_email'] ) ? $selected_options['customer_email'] : '',
				'phone'      => isset( $selected_options['customer_phone'] ) ? $selected_options['customer_phone'] : '',
				'notes'      => isset( $selected_options['customer_notes'] ) ? $selected_options['customer_notes'] : '',
			];

			$old_customer_data = [];
			$customer          = new OsCustomerModel();
			$customer          = $customer->where( [ 'email' => $customer_params['email'] ] )->set_limit( 1 )->get_results_as_models();

			if ( isset( $customer->id ) && ! empty( $customer->id ) ) {
				$is_new_customer   = false;
				$customer          = new OsCustomerModel( $customer->id );
				$old_customer_data = $customer->get_data_vars();
			} else {
				$is_new_customer = true;
				$customer        = new OsCustomerModel();
			}
			$customer_custom_fields = [];
			if ( ! empty( $selected_options['customer_fields'] ) ) {
				foreach ( $selected_options['customer_fields'] as $field ) {
					if ( is_array( $field ) && ! empty( $field ) ) {
						foreach ( $field as $key => $value ) {
							if ( false === strpos( $key, 'field_column' ) && '' !== $value ) {
								$customer_custom_fields[ $key ] = $value;
							}
						}
					}
				}
			}
			$customer_params['custom_fields'] = $customer_custom_fields;
			$customer->set_data( $customer_params );
			if ( ! $customer->save() ) {
				$errors    = $customer->get_error_messages();
				$error_msg = isset( $errors[0] ) ? $errors[0] : 'Customer could not be created.';
				throw new Exception( $error_msg );
			}

			if ( $is_new_customer ) {
				do_action( 'latepoint_customer_created', $customer );
			} else {
				do_action( 'latepoint_customer_updated', $customer, $old_customer_data );
			}
		} else {
			$customer = new OsCustomerModel( $customer_id );
			if ( ! $customer->id ) {
				throw new Exception( 'Customer not found.' );
			}
		}

		$booking->customer_id = $customer->id;
		$booking->set_utc_datetimes();

		if ( ! $booking->save() ) {
			$errors    = $booking->get_error_messages();
			$operation = $is_update ? 'updated' : 'created';
			$error_msg = isset( $errors[0] ) ? $errors[0] : 'Booking could not be ' . $operation . '.';
			throw new Exception( $error_msg );
		}

		if ( $is_update ) {
			do_action( 'latepoint_booking_updated', $booking, $old_booking );
		} else {
			do_action( 'latepoint_booking_created', $booking );
		}
		$return_data                    = $booking->get_data_vars();
		$return_data['total_attendies'] = $total_attendies;
		return $return_data;
	}

	/**
	 * Find object by email.
	 *
	 * @param array  $selected_options selected options.
	 * @param string $object model name.
	 * @return array
	 * @throws Exception Exception.
	 */
	public static function find_object_by_email( $selected_options, $object ) {

		if ( ! class_exists( 'OsAgentModel' ) || ! class_exists( 'OsCustomerModel' ) ) {
			throw new Exception( 'LatePoint plugin not installed.' );
		}

		$email = isset( $selected_options['email'] ) ? trim( $selected_options['email'] ) : '';

		if ( empty( $email ) ) {
			throw new Exception( $object . ' Email Address not provided.' );
		}

		$model = 'Agent' === $object ? new OsAgentModel() : new OsCustomerModel();
		$model = $model->where( [ 'email' => $email ] )->set_limit( 1 )->get_results( ARRAY_A );

		$model_data          = [];
		$model_data['found'] = 'no';
		if ( 'Customer' == $object ) {
			global $wpdb;
			$customer_fields = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key,meta_value FROM {$wpdb->prefix}latepoint_customer_meta WHERE object_id= %s", intval( $model['id'] ) ) );
			if ( ! empty( $customer_fields ) ) {
				foreach ( $customer_fields as $field ) {
					$model[ $field->meta_key ] = $field->meta_value;
				}
			}       
		}
		

		if ( $model ) {
			unset( $model['password'] );
			$model_data          = $model;
			$model_data['found'] = 'yes';
		}

		return $model_data;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'LatePoint' );
	}
}

IntegrationsController::register( LatePoint::class );
