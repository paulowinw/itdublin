<?php
/**
 * JetpackCRM core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\JetpackCRM;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\JetpackCRM
 */
class JetpackCRM extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'JetpackCRM';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'JetpackCRM', 'suretriggers' );
		$this->description = __( 'JetpackCRM is a WordPress Customer Support plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/jetpackcrm.svg';

		parent::__construct();
	}

	/**
	 * Get Company context data.
	 *
	 * @param int|string $company_id Company ID.
	 *
	 * @return array
	 */
	public static function get_company_context( $company_id ) {

		if ( ! function_exists( 'zeroBS_getCompany' ) ) {
			return [];
		}

		$company = zeroBS_getCompany( $company_id );

		if ( ! $company ) {
			return [];
		}

		$context['company_id']                 = $company['id'];
		$context['company_status']             = $company['status'];
		$context['company_name']               = $company['name'];
		$context['company_email']              = $company['email'];
		$context['main_address_line_1']        = $company['addr1'];
		$context['main_address_line_2']        = $company['addr2'];
		$context['main_address_city']          = $company['city'];
		$context['main_address_state']         = $company['county'];
		$context['main_address_postal_code']   = $company['postcode'];
		$context['main_address_country']       = $company['country'];
		$context['second_address_line_1']      = $company['secaddr1'];
		$context['second_address_line_2']      = $company['secaddr2'];
		$context['second_address_city']        = $company['seccity'];
		$context['second_address_state']       = $company['seccounty'];
		$context['second_address_postal_code'] = $company['secpostcode'];
		$context['second_address_country']     = $company['seccountry'];
		$context['main_telephone']             = $company['maintel'];
		$context['secondary_telephone']        = $company['sectel'];

		return $context;
	}

	/**
	 * Get Contact context data.
	 *
	 * @param int|string $contact_id Contact ID.
	 * @return array
	 */
	public static function get_contact_context( $contact_id ) {
		
		if ( ! function_exists( 'zeroBS_getCustomer' ) ) {
			return [];
		}

		$contact = zeroBS_getCustomer( $contact_id );

		if ( ! $contact ) {
			return [];
		}

		$context['contact_id']                 = $contact['id'];
		$context['status']                     = $contact['status'];
		$context['prefix']                     = $contact['prefix'];
		$context['full_name']                  = $contact['fullname'];
		$context['first_name']                 = $contact['fname'];
		$context['last_name']                  = $contact['lname'];
		$context['email']                      = $contact['email'];
		$context['main_address_line_1']        = $contact['addr1'];
		$context['main_address_line_2']        = $contact['addr2'];
		$context['main_address_city']          = $contact['city'];
		$context['main_address_state']         = $contact['county'];
		$context['main_address_postal_code']   = $contact['postcode'];
		$context['main_address_country']       = $contact['country'];
		$context['second_address_line_1']      = $contact['secaddr_addr1'];
		$context['second_address_line_2']      = $contact['secaddr_addr2'];
		$context['second_address_city']        = $contact['secaddr_city'];
		$context['second_address_state']       = $contact['secaddr_county'];
		$context['second_address_postal_code'] = $contact['secaddr_postcode'];
		$context['second_address_country']     = $contact['secaddr_country'];
		$context['home_telephone']             = $contact['hometel'];
		$context['work_telephone']             = $contact['worktel'];
		$context['mobile_telephone']           = $contact['mobtel'];

		return $context;
	}

	/**
	 * Get Quote context data.
	 *
	 * @param int|string $quote_id Quote ID.
	 * @return array
	 */
	public static function get_quote_context( $quote_id ) {

		if ( ! function_exists( 'zeroBS_getQuote' ) || ! function_exists( 'zeroBS_getQuoteStatus' ) ) {
			return [];
		}

		$quote = zeroBS_getQuote( $quote_id );

		if ( ! $quote ) {
			return [];
		}

		$context['quote_id']      = $quote['id'];
		$context['contact_id']    = isset( $quote['contact'][0]['id'] ) ? $quote['contact'][0]['id'] : '';
		$context['contact_email'] = isset( $quote['contact'][0]['email'] ) ? $quote['contact'][0]['email'] : '';
		$context['contact_name']  = isset( $quote['contact'][0]['fullname'] ) ? $quote['contact'][0]['fullname'] : '';
		$context['status']        = zeroBS_getQuoteStatus( $quote );
		$context['title']         = $quote['title'];
		$context['value']         = $quote['value'];
		$context['date']          = $quote['date_date'];
		$context['content']       = $quote['content'];
		$context['notes']         = $quote['notes'];

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'ZeroBSCRM' );
	}

}

IntegrationsController::register( JetpackCRM::class );
