<?php
/**
 * AttachSubscribersCompany.
 * php version 5.6
 *
 * @category AttachSubscribersCompany
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\FluentCRM\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;
use FluentCrm\App\Services\Helper;

/**
 * AttachSubscribersCompany
 *
 * @category AttachSubscribersCompany
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AttachSubscribersCompany extends AutomateAction {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'FluentCRM';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'fluentcrm_attach_subscribers_company';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {

		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Attach Subscribers to Company', 'suretriggers' ),
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
	 * @param array $selected_options selectedOptions.
	 *
	 * @return array|void
	 *
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {

		if ( ! class_exists( 'FluentCrm\App\Services\Helper' ) || ! function_exists( 'FluentCrmApi' ) ) {
			return;
		}

		$is_company_enabled = Helper::isCompanyEnabled();

		if ( ! $is_company_enabled ) {
			throw new Exception( 'Company module disabled. You can add companies and assign contacts to companies only when it is enabled!!' );
		}
		$contact_api = FluentCrmApi( 'contacts' );
		$company_api = FluentCrmApi( 'companies' );

		$contact_ids = explode( ',', $selected_options['contact_id'] );
		$company_ids = explode( ',', $selected_options['company_id'] );

		foreach ( $contact_ids as $key => $contact_id ) {
			$contact = $contact_api->getContact( $contact_id );
			if ( is_null( $contact ) ) {
				unset( $contact_ids[ $key ] );
			}
		}

		foreach ( $company_ids as $key => $company_id ) {
			$company = $company_api->getCompany( $company_id );
			if ( is_null( $company ) ) {
				unset( $company_ids[ $key ] );
			}
		}

		$result = $company_api->attachContactsByIds( $contact_ids, $company_ids );

		if ( ! $result ) {
			throw new Exception( 'Invalid data' );
		}

		return [
			'message'     => __( 'Company has been successfully attached to the Subscribers', 'suretriggers' ),
			'companies'   => $result['companies'],
			'subscribers' => $result['subscribers'],
		];
	}

}

AttachSubscribersCompany::get_instance();
