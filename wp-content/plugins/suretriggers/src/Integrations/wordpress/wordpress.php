<?php
/**
 * WordPress core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WordPress;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class WordPress
 *
 * @package SureTriggers\Integrations\Wordpress
 */
class WordPress extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WordPress';


	/**
	 * Get user context data.
	 *
	 * @param int $id ID.
	 *
	 * @return array
	 */
	public static function get_user_context( $id ) {

		$user    = get_userdata( $id );
		$context = [];
		if ( ! $user ) {
			return $context;
		}
		$context['wp_user_id']      = $user->ID;
		$context['user_login']      = $user->user_login;
		$context['display_name']    = $user->display_name;
		$context['user_firstname']  = $user->user_firstname;
		$context['user_lastname']   = $user->user_lastname;
		$context['user_email']      = $user->user_email;
		$context['user_registered'] = $user->user_registered;
		$context['user_role']       = $user->roles;
		return $context;
	}

	/**
	 * Get sample user context data.
	 *
	 * @return string[]
	 */
	public static function get_sample_user_context() {
		return [
			'wp_user_id'      => '1',
			'user_login'      => 'john_doe',
			'display_name'    => 'John Doe',
			'user_firstname'  => 'John',
			'user_lastname'   => 'Doe',
			'user_email'      => 'johnd@gmail.com',
			'user_registered' => '2024-06-18 09:47:58',
			'user_role'       => 'active',
		];
	}

	/**
	 * Get post context data.
	 *
	 * @param int $id ID.
	 *
	 * @return array
	 */
	public static function get_post_context( $id ) {
		return (array) get_post( $id );
	}

	/**
	 * Gets the post meta
	 *
	 * @param int $id ID.
	 *
	 * @return mixed
	 */
	public static function get_post_meta( $id ) {
		return get_post_meta( $id );
	}

	/**
	 * Validating the Email
	 *
	 * @param string $email email.
	 * @return object
	 */
	public static function validate_email( $email ) {
		$result = [
			'valid'    => true,
			'multiple' => false,
		];

		if ( str_contains( $email, ',' ) ) {
			$email_list = explode( ',', $email );

			foreach ( $email_list as $single_email ) {
				if ( ! is_email( trim( $single_email ) ) ) {
					$result['valid']    = false;
					$result['multiple'] = true;

					break;
				}
			}
		} else {
			if ( ! is_email( trim( $email ) ) ) {
				$result['valid'] = false;
			}
		}

		return (object) $result;
	}

	/**
	 * Is Plugin depended plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return true;
	}

}

IntegrationsController::register( WordPress::class );
