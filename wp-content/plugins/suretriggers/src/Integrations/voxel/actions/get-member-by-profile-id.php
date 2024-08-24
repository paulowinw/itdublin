<?php
/**
 * GetMemberByProfileID.
 * php version 5.6
 *
 * @category GetMemberByProfileID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Voxel\Actions;

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use Exception;
use SureTriggers\Integrations\Voxel\Voxel;

/**
 * GetMemberByProfileID
 *
 * @category GetMemberByProfileID
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class GetMemberByProfileID extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'Voxel';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'voxel_get_member_by_profile_id';

	use SingletonLoader;

	/**
	 * Register action.
	 *
	 * @param array $actions action data.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Get Member By Profile ID', 'suretriggers' ),
			'action'   => 'voxel_get_member_by_profile_id',
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * 
	 * @throws Exception Exception.
	 * 
	 * @return bool|array
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		$member_profile_id = (int) $selected_options['member_profile_id'];
		if ( ! class_exists( 'Voxel\User' ) || ! class_exists( 'Voxel\Post' ) ) {
			return false;
		}

		$member = \Voxel\User::get_by_profile_id( $member_profile_id );

		if ( ! $member ) {
			throw new Exception( 'Member not found' );
		}
		$member_id = $member->get_id();
		$member    = \Voxel\User::get( $member_id );

		if ( ! $member ) {
			throw new Exception( 'Member not found' );
		}

		$profile_id = $member->get_profile_id();

		// Get the membership details.
		$membership = $member->get_membership();
		$membership = $membership ? $membership->get_details_for_app_event() : [];

		// Get the member fields.
		$member_fields = [];

		// Get user.
		$user = get_userdata( $member_id );
		if ( $user ) {
			$user_data                          = (array) $user->data;
			$member_fields['user_display_name'] = $user_data['display_name'];
			$member_fields['user_name']         = $user_data['user_nicename'];
			$member_fields['user_email']        = $user_data['user_email'];
		}

		foreach ( (array) $membership as $key => $value ) {
			// Prepend the membership_ to the key.
			$key                   = 'membership_' . $key;
			$member_fields[ $key ] = $value;
		}

		// Add the profile ID.
		$member_fields['profile_id'] = $profile_id;

		// Get the post fields.
		$wp_post = \Voxel\Post::force_get( $profile_id );

		// If WP post is available, then get the fields.
		if ( $wp_post ) {
			// Loop through each field and add to the simple entry.
			foreach ( $wp_post->get_fields() as $field ) {
				$key = $field->get_key();

				if ( $field->get_type() === 'taxonomy' ) {
					$content = join(
						', ',
						array_map(
							function( $term ) {
								return $term->get_label();
							},
							$field->get_value()
						)
					);
				} elseif ( $field->get_type() === 'location' ) {
					$content = isset( $field->get_value()['address'] ) ? $field->get_value()['address'] : null;
				} else {
					$content = $field->get_value();
				}

				$member_fields[ $key ] = $content;
			}
		}

		return $member_fields;
	}

}

GetMemberByProfileID::get_instance();
