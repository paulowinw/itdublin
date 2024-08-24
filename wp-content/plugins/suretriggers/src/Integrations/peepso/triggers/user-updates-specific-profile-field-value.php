<?php
/**
 * UserUpdatesSpecificProfileFieldValue.
 * php version 5.6
 *
 * @category UserUpdatesSpecificProfileFieldValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\PeepSo\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;
use PeepSoUser;

/**
 * UserUpdatesSpecificProfileFieldValue
 *
 * @category UserUpdatesSpecificProfileFieldValue
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class UserUpdatesSpecificProfileFieldValue {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'PeepSo';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'peepso_user_updates_specific_profile_field_value';

	use SingletonLoader;

	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User Updates Specific Profile Field Value', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'peepso_ajax_start',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;
	}

	/**
	 * Trigger listener
	 *
	 * @param array $data Data.
	 *
	 * @return void
	 */
	public function trigger_listener( $data ) {

		$post_data = $_POST; // @codingStandardsIgnoreLine

		if ( ! class_exists( 'PeepSoUser' ) ) {
			return;
		}
		
		$ajax_actions = [
			'profilefieldsajax.savefield',
			'profilefieldsajax.save_acc',
			'profilepreferencesajax.savepreference',
		];

		if ( ! in_array( $data, $ajax_actions ) ) {
			return;
		}

		if ( ! isset( $post_data['id'] ) || ! isset( $post_data['value'] ) ) {
			return;
		}

		if ( 'profilefieldsajax.savefield' === $data ) {
			$context['user_profile_field_id']    = sanitize_key( $post_data['id'] );
			$context['user_profile_field_value'] = sanitize_key( $post_data['value'] );
		}

		$user_id = $post_data['view_user_id'];

		$user = PeepSoUser::get_instance( $user_id );
		$user->profile_fields->load_fields();
		$user_fields = $user->profile_fields->get_fields();
		foreach ( $user_fields as $key => $value ) {
			$val = get_user_meta( $user_id, $value->key, true );
			if ( '' != $val ) {
				$context[ $value->title ] = $val;
			}
		}
		$curruser               = get_userdata( $user_id );
		$context['user_id']     = $user_id;
		$context['user_email']  = $user->get_email();
		$context['avatar_url']  = $user->get_avatar();
		$context['profile_url'] = $user->get_profileurl();
		$context['about_me']    = get_user_meta( $user_id, 'description', true );
		if ( $curruser instanceof \WP_User ) {
			$context['website'] = $curruser->user_url;
		}
		$context['role'] = $user->get_user_role();

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}

}

UserUpdatesSpecificProfileFieldValue::get_instance();
