<?php
/**
 * AddTagToContact.
 * php version 5.6
 *
 * @category AddTagToContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\JetpackCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddTagToContact
 *
 * @category AddTagToContact
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddTagToContact extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'JetpackCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'jetpack_crm_add_tag_to_contact';

	use SingletonLoader;

	/**
	 * Register an action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add Tag to Contact', 'suretriggers' ),
			'action'   => $this->action,
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
	 * @param array $selected_options selected_options.
	 *
	 * @return array
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! function_exists( 'zeroBS_getCustomerIDWithEmail' ) || ! function_exists( 'zeroBSCRM_getCustomerTagsByID' ) || ! function_exists( 'zeroBSCRM_site' ) || ! function_exists( 'zeroBSCRM_team' ) || ! defined( 'ZBS_TYPE_CONTACT' ) ) {
			throw new Exception( 'Seems like Jetpack CRM plugin is not installed correctly.' );
		}

		$email  = sanitize_email( $selected_options['contact_email'] );
		$tag_id = $selected_options['tag_id'];

		if ( ! is_email( $email ) ) {
			throw new Exception( 'Invalid email.' );
		}

		$customer_id = zeroBS_getCustomerIDWithEmail( $email );

		if ( ! $customer_id ) {
			throw new Exception( 'Contact not found with this email.' );
		}

		$customer_tags = zeroBSCRM_getCustomerTagsByID( $customer_id );
		$filtered_tags = array_filter(
			(array) $customer_tags,
			function ( $tag ) use ( $tag_id ) {
				return $tag['id'] == $tag_id;
			}
		);
		$filtered_tag  = reset( $filtered_tags );

		if ( ! $filtered_tag ) {
			global $wpdb;
			$wpdb->insert(
				"{$wpdb->prefix}zbs_tags_links",
				[
					'zbs_site'      => zeroBSCRM_site(),
					'zbs_team'      => zeroBSCRM_team(),
					'zbs_owner'     => 0,
					'zbstl_objtype' => ZBS_TYPE_CONTACT,
					'zbstl_objid'   => $customer_id,
					'zbstl_tagid'   => $tag_id,
				],
				[ '%d', '%d', '%d', '%d', '%d', '%d' ]
			);

			$customer_tags = zeroBSCRM_getCustomerTagsByID( $customer_id );
			$filtered_tags = array_filter(
				(array) $customer_tags,
				function ( $tag ) use ( $tag_id ) {
					return $tag['id'] == $tag_id;
				}
			);

			$filtered_tag = reset( $filtered_tags );
		}

		$context             = [];
		$context['tag_id']   = $filtered_tag['id'];
		$context['tag_name'] = $filtered_tag['name'];
		$context['tag_slug'] = $filtered_tag['slug'];

		return array_merge( $context, JetpackCRM::get_contact_context( $customer_id ) );
	}

}

AddTagToContact::get_instance();
