<?php
/**
 * GlobalSearchController.
 * php version 5.6
 *
 * @category GlobalSearchController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use DOMDocument;
use FluentCrm\App\Models\CustomContactField;
use FluentCrm\App\Models\Subscriber;
use FluentCrm\App\Models\Tag;
use FluentCrm\App\Models\SubscriberMeta;
use FluentCrm\App\Models\Lists;
use memberpress\courses\lib as lib;
use memberpress\courses\models as models;
use FluentCrm\Framework\Support\Arr;
use GFCommon;
use GFFormsModel;
use Give_Payment;
use Give_Subscription;
use MeprBaseRealGateway;
use MeprOptions;
use OsAgentHelper;
use OsBookingHelper;
use OsCustomerHelper;
use OsServiceHelper;
use OsWpUserHelper;
use PrestoPlayer\Models\Video;
use RGFormsModel;
use SureTriggers\Integrations\AffiliateWP\AffiliateWP;
use SureTriggers\Integrations\EDD\EDD;
use SureTriggers\Integrations\FunnelKitAutomations\FunnelKitAutomations;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Integrations\MemberPress\MemberPress;
use SureTriggers\Integrations\MemberPressCourse\MemberPressCourse;
use SureTriggers\Integrations\ModernEventsCalendar\ModernEventsCalendar;
use SureTriggers\Integrations\PeepSo\PeepSo;
use SureTriggers\Integrations\RafflePress\RafflePress;
use SureTriggers\Integrations\RestrictContent\RestrictContent;
use SureTriggers\Integrations\TheEventCalendar\TheEventCalendar;
use SureTriggers\Integrations\WishlistMember\WishlistMember;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Integrations\WpPolls\WpPolls;
use SureTriggers\Models\Utilities;
use SureTriggers\Traits\SingletonLoader;
use Tribe__Tickets__Tickets_Handler;
use WC_Subscription;
use WC_Subscriptions_Product;
use WP_Query;
use WP_Comment_Query;
use WP_REST_Request;
use WP_REST_Response;
use WPForms_Form_Handler;
use CP_V2_Popups;
use Project_Huddle;
use FrmForm;
use Forminator_API;
use SureTriggers\Integrations\LearnPress\LearnPress;
use WC_Customer;
use WC_Booking;
use WC_Bookings_Admin;
use MeprTransaction;
use WC_Order;
use LLMS_Section;
use BP_Signup;
use WP_Post;
use AsgarosForum;
use PeepSoUser;
use PeepSoField;
use Mint\MRM\DataBase\Models\ContactModel;
use Mint\MRM\DataBase\Models\ContactGroupModel;
use SureTriggers\Integrations\Voxel\Voxel;

/**
 * GlobalSearchController- Add ajax related functions here.
 *
 * @category GlobalSearchController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class GlobalSearchController {

	use SingletonLoader;

	/**
	 * Search post by post type.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_post( $data ) {
		$result = [];
		$posts  = Utilities::find_posts_by_title( $data );

		foreach ( $posts['results'] as $post ) {
			$result[] = [
				'label' => $post['post_title'],
				'value' => $post['ID'],
			];
		}

		return [
			'options' => $result,
			'hasMore' => $posts['has_more'],
		];
	}

	/**
	 * Search Course.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_ld_course( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$courses = get_posts(
			[

				'post_type'      => 'product',
				'meta_key'       => '_related_course',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);
		$options = [];
		foreach ( $courses as $course ) {
			$options[] = [
				'label' => $course->post_title,
				'value' => $course->ID,
			];
		}

		$all_courses = get_posts(
			[
				'post_type'      => 'product',
				'meta_key'       => '_related_course',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			]
		);
		$posts_count = count( $all_courses );

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Search achievement by post type.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_achievements( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$post = get_post( $data['dynamic'] );
		$slug = $post->post_name;

		$achievements = get_posts(
			[
				'post_type'      => $slug,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);
		$options      = [];
		foreach ( $achievements as $achievement ) {
			$options[] = [
				'label' => $achievement->post_title,
				'value' => (string) $achievement->ID,
			];
		}

		$count = wp_count_posts( $slug )->publish;

		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Search Course.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_tutor_course( $data ) {
		$courses = get_posts(
			[
				'post_type'   => tutor()->course_post_type,
				'post_status' => 'publish',
				'numberposts' => '-1',
			]
		);
		$options = [];
		foreach ( $courses as $course ) {
			$options[] = [
				'label' => $course->post_title,
				'value' => $course->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Products.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_product( $data ) {
		$result = [];
		$posts  = Utilities::find_posts_by_title( $data );

		foreach ( $posts['results'] as $post ) {
			$result[] = [
				'label' => $post['post_title'],
				'value' => $post['post_title'],
			];
		}

		return [
			'options' => $result,
			'hasMore' => $posts['has_more'],
		];
	}

	/**
	 * Search Product Categories.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_product_category( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );
		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->name,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Search Product Tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_product_tags( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );

		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->name,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Global ajax search.
	 * Here you need to add the field action name to work.
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return WP_REST_Response
	 * @since 1.0.0
	 */
	public function global_search( $request ) {
		$post_type   = $request->get_param( 'post_type' );
		$dynamic     = $request->get_param( 'dynamic' );
		$search_term = $request->get_param( 'term' );
		$identifier  = $request->get_param( 'field_identifier' );
		$page        = max( 1, $request->get_param( 'page' ) );
		$taxonomy    = $request->get_param( 'taxonomy' ) ? $request->get_param( 'taxonomy' ) : [];

		$filter = $request->get_param( 'filter' ) ? json_decode( stripslashes( $request->get_param( 'filter' ) ), true ) : [];

		$data     = [
			'dynamic'     => $dynamic,
			'search_term' => $search_term,
			'page'        => $page,
			'taxonomy'    => $taxonomy,
			'filter'      => $filter,
			'post_type'   => $post_type,
		];
		$response = [
			'hasMore' => false,
			'options' => [],
		];

		$method_name = 'search_' . $identifier;

		if ( method_exists( $this, $method_name ) ) {
			$response = $this->{$method_name}( $data );
		} else {
			return RestController::error_message( 'Invalid field Identifier param.' );
		}

		return RestController::success_message( $response );
	}

	/**
	 * Search Taxonomy Terms.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_term( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );
		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->term_id,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Search users.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_user( $data ) {
		$result = [];
		$page   = $data['page'];
		$users  = Utilities::get_users( $data, $page );

		if ( is_array( $users['results'] ) ) {
			foreach ( $users['results'] as $user ) {
				$result[] = [
					'label' => $user->user_login,
					'value' => $user->ID,
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $users['has_more'],
		];

	}

	/**
	 * Search WPForm fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_pluggable_wpform_fields( $data ) {
		$result        = [];
		$page          = $data['page'];
		$form_id       = absint( $data['dynamic'] );
		$wpform_fields = Utilities::get_wpform_fields( $data['search_term'], $page, $form_id );

		if ( is_array( $wpform_fields['results'] ) ) {
			foreach ( $wpform_fields['results'] as $field ) {
				$result[] = [
					'label' => $field['label'],
					'value' => '{' . $field['id'] . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $wpform_fields['has_more'],
		];
	}

	/**
	 * Prepare variable products.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_variable_products( $data ) {
		$products = Utilities::get_variable_products( $data['search_term'], $data['page'] );
		$options  = [];

		foreach ( $products['result'] as $product ) {
			$options[] = [
				'label' => $product->get_title(),
				'value' => (string) $product->get_id(),
			];
		}

		return [
			'options' => $options,
			'hasMore' => $products['has_more'],
		];
	}

	/**
	 * Prepare variable products.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_product_variations( $data ) {
		$variations = Utilities::get_product_variations( $data['dynamic'] );
		$options    = [];

		foreach ( $variations['result'] as $product ) {
			$options[] = [
				'label' => ! empty( $product->post_excerpt ) ? $product->post_excerpt : $product->post_title,
				'value' => (string) $product->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => $variations['has_more'],
		];
	}

	/**
	 * Search WooCommerce Subscriptions.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_subscription_variation( $data ) {
		$subscription_products = Utilities::get_subscription_variation( $data['search_term'], $data['page'] );
		$result                = [];

		if ( ! function_exists( 'wc_get_products' ) ) {
			return $result;
		}

		foreach ( $subscription_products['result'] as $post ) {
			if ( $data['search_term'] ) {
				if ( false !== stripos( $post->get_title(), $data['search_term'] ) ) {
					$result[] = [
						'label' => $post->get_title(),
						'value' => (string) $post->get_id(),
					];
				}
			} else {
				$result[] = [
					'label' => $post->get_title(),
					'value' => (string) $post->get_id(),
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $subscription_products['has_more'],
		];
	}

	/**
	 * Prepare WooCommerce Payment Methods.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_payment_methods( $data ) {
		$payment_methods = WC()->payment_gateways->get_available_payment_gateways();
		$options         = [];

		if ( ! empty( $payment_methods ) ) {
			foreach ( $payment_methods as $payment ) {
				$options[] = [
					'label' => $payment->title,
					'value' => $payment->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Order Status List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_order_status_list( $data ) {
		$order_status = wc_get_order_statuses();
		$options      = [];

		if ( ! empty( $order_status ) ) {
			foreach ( $order_status as $key => $status ) {
				$options[] = [
					'label' => $status,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Country List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_country_list( $data ) {
		$countries = WC()->countries->get_countries();
		$options   = [];

		if ( ! empty( $countries ) ) {
			foreach ( $countries as $key => $country ) {
				$options[] = [
					'label' => $country,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Country States List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_country_state_list( $data ) {
		if ( ! empty( $data['dynamic']['shipping_country'] ) ) {
			$cc = $data['dynamic']['shipping_country'];
		} else {
			$cc = $data['dynamic'];
		}

		$states = WC()->countries->get_states( $cc );

		$options = [];
		if ( ! empty( $states ) ) {
			foreach ( $states as $key => $state ) {
				$options[] = [
					'label' => $state,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress gatways (payment methods) for  subscription.
	 *
	 * @param array $data QueryParams.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_memberpress_gayways( $data ) {
		$mp_options = MeprOptions::fetch();

		$pms      = array_keys( $mp_options->integrations );
		$gateways = [];

		foreach ( $pms as $pm_id ) {
			$obj = $mp_options->payment_method( $pm_id );
			if ( $obj instanceof MeprBaseRealGateway ) {
				$gateways[] = [
					'label' => sprintf( '%1$s (%2$s)', $obj->label, $obj->name ),
					'value' => $obj->id,
				];
			}
		}

		return [
			'options' => $gateways,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare roles.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_roles( $data ) {
		$roles   = wp_roles()->roles;
		$options = [];
		foreach ( $roles as $role => $details ) {

			$options[] = [
				'label' => $details['name'],
				'value' => $role,
			];

		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare post types.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_post_types( $data ) {
		$post_types = get_post_types( [ 'public' => true ], 'object' );
		$post_types = apply_filters( 'suretriggers_post_types', $post_types );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$options = [];
		foreach ( $post_types as $post_type => $details ) {
			$options[] = [
				'label' => $details->label,
				'value' => $post_type,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get post statuses.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_post_statuses( $data ) {
		$post_statuses = get_post_stati( [], 'objects' );
		$post_statuses = apply_filters( 'suretriggers_post_types', $post_statuses );
		$options       = [];

		foreach ( $post_statuses as $post_status => $details ) {
			if ( 'woocommerce' === $details->label_count['domain'] ) {
				$options[] = [
					'label' => 'WooCommerce - ' . $details->label,
					'value' => $post_status,
				];
			} else {
				$options[] = [
					'label' => $details->label,
					'value' => $post_status,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Taxonomies.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_taxonomy_list( $data ) {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];
		$options[0] = [
			'label' => 'Any Taxonomy',
			'value' => -1,
		];

		foreach ( $taxonomies as $taxonomy => $taxonomy_obj ) {
			$options[] = [
				'label' => $taxonomy_obj->label,
				'value' => $taxonomy_obj->name,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WPForms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_forms( $data ) {
		if ( ! class_exists( 'WPForms_Form_Handler' ) ) {
			return;
		}

		$wpforms = new WPForms_Form_Handler();
		$forms   = $wpforms->get( '', [ 'orderby' => 'title' ] );
		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->post_title,
					'value' => $form->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Gravity Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_gravity_forms( $data ) {
		if ( ! class_exists( 'GFFormsModel' ) ) {
			return;
		}

		$forms   = GFFormsModel::get_forms();
		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->title,
					'value' => $form->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get tag & contact details.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_fluentcrm_contact_added_to_tags( $data ) {
		$context        = [];
		$pluggable_data = [];
		$tag_id         = $data['filter'];

		if ( ! class_exists( 'FluentCrm\App\Models\Subscriber' ) || ! class_exists( 'FluentCrm\App\Models\Tag' ) ) {
			return [];
		}

		if ( $tag_id > 0 ) {
			$tags = Tag::where( 'id', $tag_id )->first();
		} else {
			$tags = Tag::orderBy( 'id', 'DESC' )->first();
		}
		$contact = Subscriber::orderBy( 'id', 'DESC' )->first();
		if ( $contact ) {
			$pluggable_data['contact'] = $contact;
			$context['tag_id']         = $tag_id;
			$pluggable_data['tag']     = $tags;
			$context['response_type']  = 'live';
		} else {
			$pluggable_data['conatct']['full_name']      = 'Test User';
			$pluggable_data['conatct']['first_name']     = 'Test';
			$pluggable_data['conatct']['last_name']      = 'User';
			$pluggable_data['conatct']['company_id']     = 112;
			$pluggable_data['conatct']['email']          = 'testuser@gmail.com';
			$pluggable_data['conatct']['address_line_1'] = '33, Vincent Road';
			$pluggable_data['conatct']['address_line_2'] = 'Chicago Street';
			$pluggable_data['conatct']['postal_code']    = '212342';
			$pluggable_data['conatct']['city']           = 'New York City';
			$pluggable_data['conatct']['state']          = 'New York';
			$pluggable_data['conatct']['country']        = 'USA';
			$pluggable_data['conatct']['phone']          = '9992191911';
			$pluggable_data['conatct']['status']         = 'subscribed';
			$pluggable_data['conatct']['contact_type']   = 'lead';
			$pluggable_data['conatct']['source']         = '';
			$pluggable_data['conatct']['date_of_birth']  = '2022-11-09';
			$context['tag_id']                           = 1;
			$pluggable_data['tag']                       =
				[
					'id'          => '1',
					'title'       => 'new',
					'slug'        => 'new',
					'description' => null,
					'created_at'  => '2023-01-19 10:23:23',
					'updated_at'  => '2023-01-19 10:23:23',
					'pivot'       => [
						'subscriber_id' => '1',
						'object_id'     => '1',
						'object_type'   => 'FluentCrm\\App\\Models\\Tag',
						'created_at'    => '2023-01-19 10:42:55',
						'updated_at'    => '2023-01-19 10:42:55',

					],
				];
			$context['response_type'] = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Get FluentCRM contact details.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_fluentcrm_contact_added( $data ) {
		$context        = [];
		$pluggable_data = [];

		if ( ! class_exists( 'FluentCrm\App\Models\Subscriber' ) || ! class_exists( 'FluentCrm\App\Models\SubscriberMeta' ) ) {
			return [];
		}
		$contact = [];
		if ( 'status_set_to_specific_status' === $data['search_term'] ) {
			if ( '-1' === $data['filter']['status']['value'] ) {
				$contact = Subscriber::orderBy( 'id', 'DESC' )->first();
			} else {
				$contact = Subscriber::where( 'status', $data['filter']['status']['value'] )->first();
			}
		} elseif ( 'new_contact_added' === $data['search_term'] ) {
			$contact = Subscriber::orderBy( 'id', 'DESC' )->first();
		} elseif ( 'contact_updated' === $data['search_term'] ) {
			$contact = Subscriber::orderBy( 'updated_at', 'DESC' )->first();
		} elseif ( 'contact_field_updated' === $data['search_term'] ) {
			if ( '-1' === $data['filter']['field_id']['value'] ) {
				$contact = SubscriberMeta::where( 'object_type', 'custom_field' )->orderBy( 'updated_at', 'DESC' )->first();
			} else {
				$contact = SubscriberMeta::where( 'key', $data['filter']['field_id']['value'] )->orderBy( 'updated_at', 'DESC' )->first();
			}
			$contact = Subscriber::where( 'id', $contact->subscriber_id )->first();
		}

		if ( $contact ) {
			$subscriber                           = Subscriber::with( [ 'tags', 'lists' ] )->find( $contact->id );
			$customer_fields                      = $subscriber->custom_fields();
			$pluggable_data['contact']['details'] = $subscriber;
			$pluggable_data['contact']['custom']  = $customer_fields;
			$pluggable_data['field_id']           = $data['filter']['field_id']['value'];
			$context['response_type']             = 'live';
		} else {
			$pluggable_data['contact']['details']['full_name']      = 'Test User';
			$pluggable_data['contact']['details']['first_name']     = 'Test';
			$pluggable_data['contact']['details']['last_name']      = 'User';
			$pluggable_data['contact']['details']['company_id']     = 112;
			$pluggable_data['contact']['details']['email']          = 'testuser@gmail.com';
			$pluggable_data['contact']['details']['address_line_1'] = '33, Vincent Road';
			$pluggable_data['contact']['details']['address_line_2'] = 'Chicago Street';
			$pluggable_data['contact']['details']['postal_code']    = '212342';
			$pluggable_data['contact']['details']['city']           = 'New York City';
			$pluggable_data['contact']['details']['state']          = 'New York';
			$pluggable_data['contact']['details']['country']        = 'USA';
			$pluggable_data['contact']['details']['phone']          = '9992191911';
			$pluggable_data['contact']['details']['status']         = 'subscribed';
			$pluggable_data['contact']['details']['contact_type']   = 'lead';
			$pluggable_data['contact']['details']['source']         = '';
			$pluggable_data['contact']['details']['date_of_birth']  = '2022-11-09';
			$context['response_type']                               = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Get contact added to list details.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_fluentcrm_contact_added_to_lists( $data ) {
		$context        = [];
		$pluggable_data = [];
		$list_id        = $data['filter'];

		if ( ! class_exists( 'FluentCrm\App\Models\Lists' ) || ! class_exists( 'FluentCrm\App\Models\Subscriber' ) ) {
			return [];
		}

		$contact_api = FluentCrmApi( 'contacts' );
		if ( -1 === $list_id ) {
			$lists   = Lists::orderBy( 'id', 'DESC' )->first();
			$list_id = $lists->id;
		} else {
			$lists = Lists::where( 'id', $list_id )->first();
		}
		$list_ids      = [ $list_id ];
		$list_contacts = $contact_api->getInstance()
				->filterByLists( $list_ids )
				->orderBy( 'id', 'DESC' )
				->first();

		$contact = Subscriber::where( 'id', $list_contacts->id )->get();
		if ( $contact ) {
			$pluggable_data['contact'] = $contact[0];
			$pluggable_data['list_id'] = $list_id;
			$pluggable_data['list']    = $lists;
			$context['response_type']  = 'live';
		} else {
			$pluggable_data['conatct']['id']             = 6;
			$pluggable_data['conatct']['prefix']         = 'Mr';
			$pluggable_data['conatct']['full_name']      = 'John Doe';
			$pluggable_data['conatct']['first_name']     = 'John';
			$pluggable_data['conatct']['last_name']      = 'Doe';
			$pluggable_data['conatct']['company_id']     = 112;
			$pluggable_data['conatct']['email']          = 'johnde@gmail.com';
			$pluggable_data['conatct']['address_line_1'] = '33, Vincent Road';
			$pluggable_data['conatct']['address_line_2'] = 'Chicago Street';
			$pluggable_data['conatct']['postal_code']    = '212342';
			$pluggable_data['conatct']['city']           = 'New York City';
			$pluggable_data['conatct']['state']          = 'New York';
			$pluggable_data['conatct']['country']        = 'USA';
			$pluggable_data['conatct']['phone']          = '9992191911';
			$pluggable_data['conatct']['status']         = 'subscribed';
			$pluggable_data['conatct']['contact_type']   = 'lead';
			$pluggable_data['conatct']['source']         = '';
			$pluggable_data['conatct']['date_of_birth']  = '2022-11-09';
			$context['list_id']                          = 1;
			$pluggable_data['list']                      =
				[
					'id'          => '1',
					'title'       => 'new',
					'slug'        => 'new',
					'description' => null,
					'created_at'  => '2023-01-19 10:23:23',
					'updated_at'  => '2023-01-19 10:23:23',
				];
			$context['response_type']                    = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Prepare fluentcrm campaigns.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_fluentcrm_campaigns( $data ) {

		$options = [];
		global $wpdb;

		$campaigns = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fc_campaigns ORDER BY id DESC", ARRAY_A );

		if ( ! empty( $campaigns ) ) {
			foreach ( $campaigns as $campaign ) {
				$options[] = [
					'label' => $campaign['title'],
					'value' => $campaign['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Divi Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_divi_forms( $data ) {
		$form_posts = Utilities::get_divi_forms();
		$options    = [];

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$pattern_regex = '/\[et_pb_contact_form(.*?)](.+?)\[\/et_pb_contact_form]/';
			preg_match_all( $pattern_regex, $form_post['post_content'], $forms, PREG_SET_ORDER );
			if ( empty( $forms ) ) {
				continue;
			}

			$count = 0;

			foreach ( $forms as $form ) {
				$pattern_form = get_shortcode_regex( [ 'et_pb_contact_form' ] );
				preg_match_all( "/$pattern_form/", $form[0], $forms_extracted, PREG_SET_ORDER );

				if ( empty( $forms_extracted ) ) {
					continue;
				}

				foreach ( $forms_extracted as $form_extracted ) {
					$form_attrs = shortcode_parse_atts( $form_extracted[3] );
					$form_id    = isset( $form_attrs['_unique_id'] ) ? $form_attrs['_unique_id'] : '';
					if ( empty( $form_id ) ) {
						continue;
					}
					$form_id    = sprintf( '%d-%s', $form_post['ID'], $form_id );
					$form_title = isset( $form_attrs['title'] ) ? $form_attrs['title'] : '';
					$form_title = sprintf( '%s %s', $form_post['post_title'], $form_title );
					$options[]  = [
						'label' => $form_title,
						'value' => $form_id,
					];
				}
				$count++;
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Comment Pluggable data.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_wp_insert_comment( $data ) {
		$context   = [];
		$post_data = [];
		$args      = [
			'number'    => '1',
			'status'    => 'approve',
			'post_type' => $data['filter']['post_type']['value'],
		];

		if ( isset( $data['filter']['post']['value'] ) ) {
			$post_id = $data['filter']['post']['value'];
			if ( $post_id > 0 ) {
				$args['post_id'] = $post_id;
			}
		}

		$comments = get_comments( $args );
		if ( empty( $comments ) ) {
			unset( $args['post_id'] );
			$comments = get_comments( $args );
		}
		$context['context_data'] = $data;
		$context['context_args'] = $args;
		if ( ! empty( $comments ) ) {
			foreach ( $comments as $comment ) :
				if ( is_object( $comment ) ) {
					$comment = get_object_vars( $comment );
				}
				if ( is_array( $comment ) && isset( $comment['comment_post_ID'] ) ) {
					$post = get_post( absint( $comment['comment_post_ID'] ) );
					if ( is_object( $post ) ) {
						if ( property_exists( $post, 'ID' ) || property_exists( $post, 'post_author' ) || property_exists( $post, 'post_title' ) ) {
							$post_id    = $post->ID;
							$postauthor = (int) $post->post_author;
							if ( is_array( $comment ) ) {
								$context['pluggable_data'] = [
									'post'                 => $post_id,
									'post_title'           => $post->post_title,
									'post_author'          => get_the_author_meta( 'display_name', $postauthor ),
									'post_link'            => get_the_permalink( $post_id ),
									'comment_id'           => $comment['comment_ID'],
									'comment'              => $comment['comment_content'],
									'comment_author'       => $comment['comment_author'],
									'comment_author_email' => $comment['comment_author_email'],
									'comment_date'         => $comment['comment_date'],
								];
							}
						}
					}
				}
				if ( is_array( $comment ) && isset( $comment['comment_author_email'] ) ) {
					$user_email = $comment['comment_author_email'];
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$user = get_user_by( 'email', $user_email );
					if ( $user ) {
						$context['pluggable_data']['wp_user_id']     = $user->ID;
						$context['pluggable_data']['user_login']     = $user->user_login;
						$context['pluggable_data']['display_name']   = $user->display_name;
						$context['pluggable_data']['user_firstname'] = $user->user_firstname;
						$context['pluggable_data']['user_lastname']  = $user->user_lastname;
						$context['pluggable_data']['user_email']     = $user->user_email;
						$context['pluggable_data']['user_role']      = $user->roles;
					}
				}

				$context['response_type'] = 'live';
			endforeach;
		} else {
			$sample_comment                   = [
				'post'       => 100,
				'post_title' => 'Sample Post',
				'comment_id' => 101,
				'comment'    => 'Sample Comment',
			];
			$sample_comment['wp_user_id']     = 7;
			$sample_comment['user_login']     = 'testuser@gmail.com';
			$sample_comment['display_name']   = 'Test User';
			$sample_comment['user_firstname'] = 'Test';
			$sample_comment['user_lastname']  = 'User';
			$sample_comment['user_email']     = 'testuser@gmail.com';
			$sample_comment['user_role']      = [ 'Subscriber' ];

			$context['pluggable_data'] = $sample_comment;
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * User reset password.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_user_reset_password( $data ) {
		$user_context                                   = $this->search_pluggables_add_user_role( $data );
		$user_context['pluggable_data']['new_password'] = '***password***';
		return $user_context;
	}

	/**
	 * User pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_add_user_role( $data ) {
		$context = [];
		$args    = [
			'order'   => 'DESC',
			'number'  => 1,
			'orderby' => 'ID',
		];

		if ( isset( $data['filter']['role']['value'] ) ) {
			$role         = $data['filter']['role']['value'];
			$args['role'] = $role;
		}
		if ( isset( $data['filter']['new_role']['value'] ) ) {
			$role         = $data['filter']['new_role']['value'];
			$args['role'] = $role;
		}

		$users = get_users( $args );

		if ( isset( $data['filter']['meta_key']['value'] ) ) {
			$meta_key            = $data['filter']['meta_key']['value'];
			$args['st_meta_key'] = $meta_key;
		}

		if ( isset( $data['filter']['profile_field']['value'] ) ) {
			$meta_key              = $data['filter']['profile_field']['value'];
			$args['profile_field'] = $meta_key;
		}

		if ( ! empty( $users ) ) {
			$user                              = $users[0];
			$pluggable_data                    = [];
			$pluggable_data['wp_user_id']      = $user->ID;
			$pluggable_data['user_login']      = $user->user_login;
			$pluggable_data['display_name']    = $user->display_name;
			$pluggable_data['user_firstname']  = $user->user_firstname;
			$pluggable_data['user_lastname']   = $user->user_lastname;
			$pluggable_data['user_email']      = $user->user_email;
			$pluggable_data['user_registered'] = $user->user_registered;
			$pluggable_data['user_role']       = $user->roles;
			if ( isset( $args['st_meta_key'] ) ) {
				$pluggable_data['meta_key']   = $args['st_meta_key'];
				$pluggable_data['meta_value'] = get_user_meta( $user->ID, $args['st_meta_key'], true );
			}
			if ( isset( $args['profile_field'] ) ) {
				$userdata = get_userdata( $user->ID );
				$userdata = json_decode( wp_json_encode( $userdata->data ), true );

				$pluggable_data['profile_field']       = $args['profile_field'];
				$pluggable_data['profile_field_value'] = $userdata[ $args['profile_field'] ];
			}
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'      => 1,
				'user_login'      => 'admin',
				'display_name'    => 'Test User',
				'user_firstname'  => 'Test',
				'user_lastname'   => 'User',
				'user_email'      => 'testuser@gmail.com',
				'user_registered' => '2024-06-18 09:47:58',
				'user_role'       => [ $role ],
			];
			if ( isset( $args['st_meta_key'] ) ) {
				$context['pluggable_data']['meta_key']   = $args['st_meta_key'];
				$context['pluggable_data']['meta_value'] = 'test meta value';
			}
			if ( isset( $args['profile_field'] ) ) {
				$context['pluggable_data']['profile_field']       = $args['profile_field'];
				$context['pluggable_data']['profile_field_value'] = 'Profile Field Value';
			}
			$context['response_type'] = 'sample';
		}
		return $context;
	}

	/**
	 * User pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_last_user_login( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => 'st_last_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                             = $users[0];
			$pluggable_data                   = [];
			$pluggable_data['wp_user_id']     = $user->ID;
			$pluggable_data['user_login']     = $user->user_login;
			$pluggable_data['display_name']   = $user->display_name;
			$pluggable_data['user_firstname'] = $user->user_firstname;
			$pluggable_data['user_lastname']  = $user->user_lastname;
			$pluggable_data['user_email']     = $user->user_email;
			$pluggable_data['user_role']      = $user->roles;

			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'admin',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Donation pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wordpress_post( $data ) {
		$context = [];
		$args    = [
			'post_type'      => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		];

		if ( isset( $data['filter']['post_type']['value'] ) ) {
			$post_type         = $data['filter']['post_type']['value'];
			$args['post_type'] = $post_type;
		}

		if ( isset( $data['filter']['status']['value'] ) ) {
			$post_status         = $data['filter']['status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['post_status']['value'] ) ) {
			$post_status         = $data['filter']['post_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['post']['value'] ) ) {
			$post_id = $data['filter']['post']['value'];
			if ( $post_id > 0 ) {
				$args['p'] = $post_id;
				unset( $args['post_status'] );
			}
		}

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = $posts[0];
			$custom_metas              = get_post_meta( $posts[0]->ID );
			if ( property_exists( $context['pluggable_data'], 'post' ) ) {
				$context['pluggable_data']->post = $posts[0]->ID;
			}
			if ( is_object( $context['pluggable_data'] ) ) {
				$context['pluggable_data'] = get_object_vars( $context['pluggable_data'] );
			}
			if ( $posts[0] instanceof WP_Post ) {
				$taxonomies = get_object_taxonomies( get_post( $posts[0] ), 'objects' );
				if ( ! empty( $taxonomies ) ) {
					foreach ( $taxonomies as $taxonomy => $taxonomy_object ) {
						$terms = get_the_terms( $posts[0]->ID, $taxonomy );
						if ( ! empty( $terms ) && is_array( $terms ) ) {
							foreach ( $terms as $term ) {
								$context['pluggable_data'][ $taxonomy ] = $term->name;
							}
						}
					}
				}
			}
			$context['pluggable_data']                 = array_merge( $context['pluggable_data'], WordPress::get_user_context( $posts[0]->post_author ) );
			$context['pluggable_data']['post']         = $posts[0]->ID;
			$context['pluggable_data']['custom_metas'] = $custom_metas;
			$context['response_type']                  = 'live';
		} else {
			$context['pluggable_data'] = [
				'ID'                    => 557,
				'post'                  => 557,
				'post_author'           => 1,
				'post_date'             => '2022-11-18 12:18:14',
				'post_date_gmt'         => '2022-11-18 12:18:14',
				'post_content'          => 'Test Post Content',
				'post_title'            => 'Test Post',
				'post_excerpt'          => '',
				'post_status'           => $args['post_status'],
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => 'test-post',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2022-11-18 12:18:14',
				'post_modified_gmt'     => '2022-11-18 12:18:14',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'https://abc.com/test-post/',
				'menu_order'            => 0,
				'post_type'             => 'post',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			];
			$context['response_type']  = 'sample';
		}

		return $context;
	}

	/**
	 * Donation pluggable data
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_givewp_donation_via_form( $data ) {
		global $wpdb;
		$context         = [];
		$pluggable_data  = [];
		$form_id         = $data['filter']['form_id']['value'];
		$subscription_id = 1;

		if ( ! class_exists( 'Give_Payment' ) || ! class_exists( 'Give_Subscription' ) ) {
			return [];
		}

		if ( ! function_exists( 'give_get_donor_donation_comment' ) ) {
			return [];
		}
		
		if ( 'donation_specific_field_value' == $data['search_term'] ) {
			$field_id    = $data['filter']['field_id']['value'];
			$field_value = $data['filter']['field_value']['value'];

			$donation_meta = $wpdb->get_row( 
				$wpdb->prepare( 
					"SELECT * FROM {$wpdb->prefix}give_donationmeta
				WHERE meta_key LIKE %s AND 
				meta_value LIKE %s 
				ORDER BY donation_id 
				DESC LIMIT 1",
					$field_id,
					$field_value 
				) 
			);
			if ( $donation_meta ) {
				$donation_form_meta = $wpdb->get_row( 
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}give_donationmeta
					WHERE donation_id = %d AND
					meta_key LIKE %s AND 
					meta_value LIKE %d 
					ORDER BY donation_id 
					DESC LIMIT 1",
						$donation_meta->donation_id,
						'_give_payment_form_id',
						$form_id 
					) 
				);

				$payment = $wpdb->get_row(
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}posts WHERE 
					ID=%d ORDER BY ID DESC LIMIT 1", 
						$donation_form_meta->donation_id 
					) 
				);
			}
		} elseif ( 'donation_specific_amount' == $data['search_term'] ) {
			$condition_compare = $data['filter']['condition_compare']['value'];
			$donation_amount   = $data['filter']['donation_amount']['value'];

			$donation_meta = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}give_donationmeta WHERE meta_key LIKE %s AND meta_value $condition_compare %d ORDER BY donation_id DESC LIMIT 1", '_give_payment_total',$donation_amount ) ); //phpcs:ignore

			if ( $donation_meta ) {
				$donation_form_meta = $wpdb->get_row( 
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}give_donationmeta
					WHERE donation_id = %d AND
					meta_key LIKE %s AND 
					meta_value LIKE %d 
					ORDER BY donation_id 
					DESC LIMIT 1",
						$donation_meta->donation_id,
						'_give_payment_form_id',
						$form_id 
					) 
				);

				$payment = $wpdb->get_row(
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}posts WHERE 
					ID=%d ORDER BY ID DESC LIMIT 1", 
						$donation_form_meta->donation_id 
					) 
				);
			}
		} elseif ( 'cancels_recurring_donation' == $data['search_term'] ) {
			$donation_meta = $wpdb->get_row( 
				$wpdb->prepare( 
					"SELECT * FROM {$wpdb->prefix}give_subscriptions
				WHERE product_id=%d AND
				status LIKE %s
				ORDER BY id 
				DESC LIMIT 1",
					$form_id,
					'cancelled' 
				) 
			);
			if ( $donation_meta ) {
				$subscription_id = $donation_meta->id;
				$payment         = $wpdb->get_row(
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}posts WHERE 
					ID=%d ORDER BY ID DESC LIMIT 1", 
						$donation_meta->parent_payment_id 
					) 
				);
			}
		} elseif ( 'continues_recurring_donation' == $data['search_term'] ) {
			$donation_meta = $wpdb->get_row( 
				$wpdb->prepare( 
					"SELECT * FROM {$wpdb->prefix}give_subscriptions
				WHERE product_id=%d AND
				status LIKE %s
				ORDER BY id 
				DESC LIMIT 1",
					$form_id,
					'active' 
				) 
			);
			if ( $donation_meta ) {
				$subscription_id = $donation_meta->id;

				$payment = $wpdb->get_row( 
					$wpdb->prepare( 
						"SELECT * FROM {$wpdb->prefix}posts
					WHERE post_parent=%d AND post_status LIKE %s 
					ORDER BY ID DESC LIMIT 1", 
						$donation_meta->parent_payment_id,
						'give_subscription' 
					) 
				);
			}
		} else {
			$payment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ORDER BY id DESC LIMIT 1", 'give_payment' ) );
		}

		if ( ! empty( $payment ) ) {
			if ( 'continues_recurring_donation' == $data['search_term'] || 'cancels_recurring_donation' == $data['search_term'] ) {
				$subscription = new Give_Subscription( $subscription_id );
				
				$pluggable_data['form_id']      = $form_id;
				$pluggable_data['subscription'] = $subscription;
			} elseif ( 'donation_specific_field_value' == $data['search_term'] ) {
				$payment_data = new Give_Payment( $payment->ID );
				$input_array  = $payment_data->payment_meta;
				unset( $input_array['user_info'] );
				$pluggable_data = $input_array;
				foreach ( $input_array as $key => $value ) {
					$pluggable_data['field_id']    = $key;
					$pluggable_data['field_value'] = $value;
				}
			} else {
				$payment      = new Give_Payment( $payment->ID );
				$address_data = $payment->address;
	
				$pluggable_data['first_name']        = $payment->first_name;
				$pluggable_data['last_name']         = $payment->last_name;
				$pluggable_data['email']             = $payment->email;
				$pluggable_data['currency']          = $payment->currency;
				$pluggable_data['donated_amount']    = $payment->subtotal;
				$pluggable_data['donation_amount']   = $payment->subtotal;
				$pluggable_data['form_id']           = (int) $payment->form_id;
				$pluggable_data['form_title']        = $payment->form_title;
				$pluggable_data['name_title_prefix'] = $payment->title_prefix;
				$pluggable_data['date']              = $payment->date;
	
				if ( is_array( $address_data ) ) {
					$pluggable_data['address_line_1'] = $address_data['line1'];
					$pluggable_data['address_line_2'] = $address_data['line2'];
					$pluggable_data['city']           = $address_data['city'];
					$pluggable_data['state']          = $address_data['state'];
					$pluggable_data['zip']            = $address_data['zip'];
					$pluggable_data['country']        = $address_data['country'];
				}
				// Payment meta.
				$payment_meta = $payment->get_meta();
				if ( is_array( $payment_meta ) && isset( $payment_meta['user_info'] ) ) {
					unset( $payment_meta['user_info'] );
				}
				$pluggable_data['payment_meta'] = $payment_meta;
				$donor_comment                  = give_get_donor_donation_comment( $payment->ID, $payment->donor_id );
				$pluggable_data['comment']      = ( is_array( $donor_comment ) && isset( $donor_comment['comment_content'] ) ) ? $donor_comment : '';
			}

			$context['response_type'] = 'live';
		} else {
			if ( 'continues_recurring_donation' == $data['search_term'] || 'cancels_recurring_donation' == $data['search_term'] ) {
				$pluggable_data['form_id']                                 = $form_id;
				$pluggable_data['subscription']['id']                      = 3;
				$pluggable_data['subscription']['donor_id']                = 8;
				$pluggable_data['subscription']['period']                  = 'month';
				$pluggable_data['subscription']['initial_amount']          = '25.0000000000';
				$pluggable_data['subscription']['recurring_amount']        = '25.0000000000';
				$pluggable_data['subscription']['recurring_fee_amount']    = '0.0000000000';
				$pluggable_data['subscription']['transaction_id']          = 'a228ec9c6357963d23079d7d6945dd61';
				$pluggable_data['subscription']['parent_payment_id']       = '7492';
				$pluggable_data['subscription']['created']                 = '2024-01-23 11:12:11';
				$pluggable_data['subscription']['expiration']              = '2024-03-23 23:59:59';
				$pluggable_data['subscription']['status']                  = 'cancelled';
				$pluggable_data['subscription']['donor']['id']             = 8;
				$pluggable_data['subscription']['donor']['purchase_count'] = '3';
				$pluggable_data['subscription']['donor']['purchase_value'] = '75.000000';
				$pluggable_data['subscription']['donor']['email']          = 'johndoee@yopmail.com';
				$pluggable_data['subscription']['donor']['name']           = 'John Doe';
				$pluggable_data['subscription']['donor']['payment_ids']    = '7487,7492,7499';
				$pluggable_data['subscription']['donor']['user_id']        = '131';
				$pluggable_data['subscription']['customer_id']             = '8';
			} elseif ( 'donation_specific_field_value' == $data['search_term'] ) {
				$pluggable_data['form_id']                        = 23;
				$pluggable_data['form_title']                     = 'Demo Donation';
				$pluggable_data['_give_donor_billing_first_name'] = 'John';
				$pluggable_data['_give_donor_billing_last_name']  = 'Doe';
				$pluggable_data['_give_payment_donor_email']      = 'johndoee@gmail.com';
				$pluggable_data['_give_payment_currency']         = 'USD';
				$pluggable_data['_give_payment_total']            = '100';
				$pluggable_data['name_title_prefix']              = 'Mr';
				$pluggable_data['date']                           = '2022-11-07 16:06:05';
				$pluggable_data['_give_donor_billing_address1']   = '33, Vincent Road';
				$pluggable_data['_give_donor_billing_address2']   = 'Chicago Street';
				$pluggable_data['_give_donor_billing_city']       = 'New York City';
				$pluggable_data['_give_donor_billing_state']      = 'New York';
				$pluggable_data['_give_donor_billing_zip']        = '223131';
				$pluggable_data['_give_donor_billing_country']    = 'USA';
				$pluggable_data['_give_donation_comment']         = 'Demo Comment';
				$pluggable_data['field_id']                       = 'last_name';
				$pluggable_data['field_value']                    = 'Doe';
			} else {
				$pluggable_data['first_name']        = 'John';
				$pluggable_data['last_name']         = 'Doe';
				$pluggable_data['email']             = 'johndoee@gmail.com';
				$pluggable_data['currency']          = 'USD';
				$pluggable_data['donated_amount']    = 100;
				$pluggable_data['donation_amount']   = 100;
				$pluggable_data['form_id']           = 23;
				$pluggable_data['form_title']        = 'Demo Donation';
				$pluggable_data['name_title_prefix'] = 'Mr';
				$pluggable_data['date']              = '2022-11-07 16:06:05';
				$pluggable_data['address_line_1']    = '33, Vincent Road';
				$pluggable_data['address_line_2']    = 'Chicago Street';
				$pluggable_data['city']              = 'New York City';
				$pluggable_data['state']             = 'New York';
				$pluggable_data['zip']               = '223131';
				$pluggable_data['country']           = 'USA';
				$pluggable_data['comment']           = 'Demo Comment';
			}
			$context['response_type'] = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search Divi Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_pluggable_divi_form_fields( $data ) {
		$result     = [];
		$form_id    = absint( $data['dynamic'] );
		$form_posts = Utilities::get_divi_forms();

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$fields = [];
		foreach ( $form_posts as $form_post ) {
			$pattern_regex = '/\[et_pb_contact_form(.*?)](.+?)\[\/et_pb_contact_form]/';
			preg_match_all( $pattern_regex, $form_post['post_content'], $forms, PREG_SET_ORDER );
			if ( empty( $forms ) ) {
				continue;
			}

			$count = 0;

			foreach ( $forms as $form ) {
				$pattern = get_shortcode_regex( [ 'et_pb_contact_field' ] );

				preg_match_all( "/$pattern/", $form[0], $contact_fields, PREG_SET_ORDER );

				if ( empty( $contact_fields ) ) {
					return $fields;
				}

				foreach ( $contact_fields as $contact_field ) {
					$contact_field_attrs = shortcode_parse_atts( $contact_field[3] );
					$field_id            = strtolower( self::array_get( $contact_field_attrs, 'field_id' ) );
					$fields[]            = [
						'field_title' => self::array_get( $contact_field_attrs, 'field_title', __( 'No title', 'suretriggers' ) ),
						'field_id'    => $field_id,
					];
				}
			}
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$result[] = [
					'label' => $field['field_title'],
					'value' => '{' . $field['field_id'] . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Pseudo function copied from Divi
	 *
	 * @param array        $array An array which contains value located at `$address`.
	 * @param string|array $address The location of the value within `$array` (dot notation).
	 * @param mixed        $default Value to return if not found. Default is an empty string.
	 *
	 * @return mixed The value, if found, otherwise $default.
	 */
	public static function array_get( $array, $address, $default = '' ) {
		$keys  = is_array( $address ) ? $address : explode( '.', $address );
		$value = $array;

		foreach ( $keys as $key ) {
			if ( ! empty( $key ) && isset( $key[0] ) && '[' === $key[0] ) {
				$index = substr( $key, 1, -1 );

				if ( is_numeric( $index ) ) {
					$key = (int) $index;
				}
			}

			if ( ! isset( $value[ $key ] ) ) {
				return $default;
			}

			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Get UAG Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_spectra_forms( $data ) {
		$form_posts = Utilities::get_uag_forms();

		$options = [];
		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$blocks = parse_blocks( $form_post['post_content'] );
			$i      = 1;
			// Get form blocks.
			$this->process_blocks( $blocks, $form_post, $options, $i );
		}
	
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Return forms in nested blocks in Spectra.
	 *
	 * @param array $blocks data.
	 * @param array $form_post Form post.
	 * @param array $options Options.
	 * @param int   $i Number.
	 *
	 * @return array
	 */
	public function process_blocks( $blocks, $form_post, &$options, &$i ) {
		foreach ( $blocks as $block ) {
			if ( 'uagb/forms' === $block['blockName'] ) {
				$options[] = [
					'label' => $form_post['post_title'] . ' (Form ' . ( $i++ ) . ')',
					'value' => $block['attrs']['block_id'],
				];
			} elseif ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$this->process_blocks( $block['innerBlocks'], $form_post, $options, $i );
			}
		}
		return $options;
	}

	/**
	 * Check array recursive.
	 *
	 * @param array  $array Array.
	 * @param string $value search params.
	 * @since 1.0.0
	 *
	 * @return array|void
	 */
	public static function get_column_by_value( $array, $value ) {

		foreach ( $array as $key => $sub_array ) {
			
			if ( is_array( $sub_array ) ) {
				$result = self::get_column_by_value( $sub_array, $value );
				if ( null !== $result ) {
					return $key;
				}
			} else {
				return $key;
			}
		}
			return null;
			
	}


	/**
	 * Search UAG Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_spectraform_fields( $data ) {
		$result     = [];
		$form_id    = absint( $data['dynamic'] );
		$form_posts = Utilities::get_uag_forms();

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$blocks = parse_blocks( $form_post['post_content'] );

			foreach ( $blocks as $block ) {
				if ( (int) $block['attrs']['block_id'] === $form_id ) {
					$doc            = new DOMDocument();
					$rendered_block = render_block( $block );
					$doc->loadHTML( $rendered_block );
					$child_node_list = $doc->getElementsByTagName( 'div' );
					for ( $i = 0; $i < $child_node_list->length; $i++ ) {
						$temp = $child_node_list->item( $i );
						if ( $temp && stripos( $temp->getAttribute( 'class' ), 'uagb-forms-input-label' ) !== false ) {
							$nodes[] = $temp;
						}
					}

					foreach ( $nodes as $node ) {
						$result[] = [
                            'label' => $node->textContent, //phpcs:ignore
                            'value' => $node->textContent, //phpcs:ignore
						];
					}
				}
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of MetForms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_met_forms( $data ) {
		$args = [
			'post_type'   => 'metform-form',
			'post_status' => 'publish',
			'numberposts' => -1,
		];

		$forms = get_posts( $args );

		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->post_title,
					'value' => $form->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of Ninja Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_ninja_forms( $data ) {
		$options = [];

		if ( function_exists( 'Ninja_Forms' ) ) {
			foreach ( Ninja_Forms()->form()->get_forms() as $form ) {
				$options[] = [
					'label' => $form->get_setting( 'title' ),
					'value' => $form->get_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of Pie Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pie_forms( $data ) {
		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pf_forms' ) ) ) {

			$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'pf_forms WHERE post_status = "published"' );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->form_title,
						'value' => $result->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Fluent Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_forms( $data ) {

		if ( ! function_exists( 'wpFluent' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		$forms = wpFluent()->table( 'fluentform_forms' )
			->select( [ 'id', 'title' ] )
			->orderBy( 'id', 'DESC' )
			->get();

		$options = [];
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->title,
					'value' => $form->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get Fluent Forms Fields.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_forms_form_fields( $data ) {

		$options = [];

		if ( ! function_exists( 'wpFluent' ) ) {
			return [];
		}

		$form       = wpFluent()->table( 'fluentform_forms' )->find( $data['dynamic'] );
		$field_data = json_decode( $form->form_fields, true );
		if ( is_array( $field_data ) && ! empty( $field_data['fields'] ) ) {
			foreach ( $field_data['fields'] as $field ) {
				if ( isset( $field['fields'] ) ) {
					foreach ( $field['fields'] as $field_key => $sub_field ) {
						if (
							isset( $sub_field['settings'] )
							&& isset( $sub_field['settings']['label'] )
							&& isset( $sub_field['settings']['visible'] )
							&& true === $sub_field['settings']['visible']
						) {
							$options[] = [
								'label' => $sub_field['settings']['label'],
								'value' => $field_key,
							];
						}
					}
				} elseif ( isset( $field['element'] ) && 
				'container' === (string) $field['element'] && 
				isset( $field['columns'] ) && is_array( $field['columns'] ) ) {
					$container_fields = $field['columns'];
					foreach ( $container_fields as $c_fields ) {
						foreach ( $c_fields['fields'] as $field_key => $sub_field ) {
							if ( isset( $sub_field['settings'] ) ) {
								$options[] = [
									'label' => ( '' !== $sub_field['settings']['label'] ) ? 
									$sub_field['settings']['label'] : 
									$sub_field['attributes']['name'],
									'value' => isset( $sub_field['attributes']['name'] ) ? 
									$sub_field['attributes']['name'] : 
									strtolower( $sub_field['settings']['label'] ),
								];
							}
						}
					}
				} elseif ( isset( $field['attributes'] ) && isset( $field['attributes']['name'] ) ) {
					if ( isset( $field['attributes']['placeholder'] ) && ! empty( $field['attributes']['placeholder'] ) ) {
						$options[] = [
							'label' => $field['attributes']['placeholder'],
							'value' => $field['attributes']['name'],
						];
					} elseif ( isset( $field['settings'] ) && 
					isset( $field['settings']['label'] ) && 
					! empty( $field['settings']['label'] ) ) {
						$options[] = [
							'label' => $field['settings']['label'],
							'value' => $field['attributes']['name'],
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get Fluent Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_bricks_builder_forms( $data ) {
		$bricks_theme = wp_get_theme( 'bricks' );
		if ( ! $bricks_theme->exists() ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		$bricks_settings = (array) get_option( BRICKS_DB_GLOBAL_SETTINGS );
		if ( array_key_exists( 'postTypes', $bricks_settings ) ) {
			$bricks_posts = $bricks_settings['postTypes'];
		} else {
			$bricks_posts = [];
		}
		$bricks_posts[] = 'bricks_template';

		$args = [
			'post_type'      => $bricks_posts,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		];

		$templates = get_posts( $args );

		$options = [];
		if ( ! empty( $templates ) ) {
			foreach ( $templates as $template ) {
				$fetch_content = get_post_meta( $template->ID, BRICKS_DB_PAGE_CONTENT, true );
				if ( is_array( $fetch_content ) ) {
					foreach ( $fetch_content as $content ) {
						if ( 'form' === $content['name'] ) {
							$options[] = [
								'label' => $template->post_title . ' - ' . ( isset( $content['label'] ) ? $content['label'] : 'Form' ),
								'value' => $content['id'],
							];
						}
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Bricks builder form fields.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggable_bricks_builder_form_fields( $data ) {
		$result        = [];
		$fields        = [];
		$form_id_str   = $data['dynamic'];
		$ids           = explode( '_', $form_id_str );
		$post_id       = $ids[0];
		$form_id       = $ids[1];
		$fetch_content = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );
		if ( is_array( $fetch_content ) ) {
			foreach ( $fetch_content as $content ) {
				if ( 'form' === $content['name'] && $form_id === $content['id'] ) {
					$fields = $content['settings']['fields'];
					break;
				}
			}
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$result[] = [
					'label' => $field['label'],
					'value' => '{' . strtolower( $field['label'] ) . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Get fluent form fields
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	public function search_pluggable_fluent_form_fields( $data ) {
		$result  = [];
		$form_id = absint( $data['dynamic'] );

		$fluentform_fields = Utilities::get_fluentform_fields( $data['search_term'], -1, $form_id );

		if ( is_array( $fluentform_fields['results'] ) ) {
			foreach ( $fluentform_fields['results'] as $field ) {
				$result[] = [
					'label' => $field['text'],
					'value' => "{{$field['value']}}",
				];
			}
		}

		$result[] = [
			'value' => '{form_id}',
			'label' => 'Form ID',
		];

		$result[] = [
			'value' => '{form_title}',
			'label' => 'Form Title',
		];
		$result[] = [
			'value' => '{entry_id}',
			'label' => 'Entry ID',
		];

		$result[] = [
			'value' => '{entry_source_url}',
			'label' => 'Entry Source URL',
		];

		$result[] = [
			'value' => '{submission_date}',
			'label' => 'Submission Date',
		];

		$result[] = [
			'value' => '{user_ip}',
			'label' => 'User IP',
		];

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Search Gravity Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_gform_fields( $data ) {
		if ( ! class_exists( 'RGFormsModel' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$result  = [];
		$page    = $data['page'];
		$form_id = absint( $data['dynamic'] );

		$form = RGFormsModel::get_form_meta( $form_id );

		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $input ) {
						if ( ! isset( $input['isHidden'] ) || ( isset( $input['isHidden'] ) && ! $input['isHidden'] ) ) {
							$result[] = [
								'value' => $input['id'],
								'label' => GFCommon::get_label( $field, $input['id'] ),
							];
						}
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$result[] = [
						'value' => (string) $field['id'],
						'label' => GFCommon::get_label( $field ),
					];
				}
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];

	}

	/**
	 * Search Gravity Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_pluggable_gravity_form_fields( $data ) {
		if ( ! class_exists( 'RGFormsModel' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$result  = [];
		$form_id = absint( $data['dynamic'] );

		$form = RGFormsModel::get_form_meta( $form_id );

		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $input ) {
						if ( ! isset( $input['isHidden'] ) || ( isset( $input['isHidden'] ) && ! $input['isHidden'] ) ) {
							$result[] = [
								'value' => '{' . $input['id'] . '}',
								'label' => GFCommon::get_label( $field, $input['id'] ),
							];
						}
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$result[] = [
						'value' => '{' . $field['id'] . '}',
						'label' => GFCommon::get_label( $field ),
					];
				}
			}
		}

		$result[] = [
			'value' => '{gravity_form}',
			'label' => 'Form ID',
		];
		$result[] = [
			'value' => '{form_title}',
			'label' => 'Form Title',
		];
		$result[] = [
			'value' => '{entry_id}',
			'label' => 'Entry ID',
		];
		$result[] = [
			'value' => '{user_ip}',
			'label' => 'User IP',
		];
		$result[] = [
			'value' => '{entry_source_url}',
			'label' => 'Entry Source URL',
		];
		$result[] = [
			'value' => '{entry_submission_date}',
			'label' => 'Entry Submission Date',
		];

		return [
			'options' => $result,
			'hasMore' => false,
		];

	}

	/**
	 * Search Gravity Form fields for action.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_gravity_form_custom_fields( $data ) {
		$context = [];
		if ( ! class_exists( 'RGFormsModel' ) || ! class_exists( 'GFCommon' ) ) {
			return [];
		}
		$form_id       = $data['filter'];
		$form          = RGFormsModel::get_form_meta( $form_id );
		$custom_fields = [];
		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $input ) {
						if ( ! $input['isHidden'] ) {
							$custom_fields[] = [
								'label' => GFCommon::get_label( $field, $input['id'] ),
								'value' => $input['id'],
								'type'  => 'text',
							];
						}
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$custom_fields[] = [
						'label' => GFCommon::get_label( $field ),
						'value' => $field['id'],
						'type'  => 'text',
					];
				}
			}
		}
		$context['fields'] = $custom_fields;
		return $context;
	}

	/**
	 * Get user register details via gravity forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_gravity_forms_user_register( $data ) {
		global $wpdb;
		$context        = [];
		$pluggable_data = [];

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, form_id, date_created, ip, source_url, created_by FROM {$wpdb->prefix}gf_entry
			WHERE form_id = %d AND
			status = %s
			ORDER BY id 
			DESC LIMIT 1",
				$data['filter']['gravity_form']['value'],
				'active'
			)
		);

		if ( $results ) {
			$pluggable_data['gravity_form']          = (int) $results[0]->form_id;
			$pluggable_data['entry_id']              = $results[0]->id;
			$pluggable_data['user_ip']               = $results[0]->ip;
			$pluggable_data['entry_source_url']      = $results[0]->source_url;
			$pluggable_data['entry_submission_date'] = $results[0]->date_created;
			$pluggable_data['user']                  = WordPress::get_user_context( $results[0]->created_by );
			$context['response_type']                = 'live';
		} else {
			$pluggable_data['gravity_form']           = '3';
			$pluggable_data['entry_id']               = '13';
			$pluggable_data['user_ip']                = '127.0.0.0';
			$pluggable_data['entry_source_url']       = 'https://example.com';
			$pluggable_data['entry_submission_date']  = '2024-02-05 09:41:59';
			$pluggable_data['user']['wp_user_id']     = '123';
			$pluggable_data['user']['user_login']     = 'johnd';
			$pluggable_data['user']['display_name']   = 'johnd';
			$pluggable_data['user']['user_firstname'] = 'John';
			$pluggable_data['user']['user_lastname']  = 'Doe';
			$pluggable_data['user']['user_email']     = 'johnd@yopmail.com';
			$pluggable_data['user']['user_role']      = [ 'subscriber' ];
			$context['response_type']                 = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Get payment form details for gravity forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_gravity_forms_payment( $data ) {
		global $wpdb;
		$context        = [];
		$pluggable_data = [];

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}gf_entry
			WHERE form_id = %d AND
			status = %s AND
			payment_status LIKE %s
			ORDER BY id 
			DESC LIMIT 1",
				$data['filter']['gravity_form']['value'],
				'active',
				'Paid'
			)
		);

		if ( $results ) {
			$pluggable_data['gravity_form']          = (int) $results[0]->form_id;
			$pluggable_data['entry_id']              = $results[0]->id;
			$pluggable_data['user_ip']               = $results[0]->ip;
			$pluggable_data['entry_source_url']      = $results[0]->source_url;
			$pluggable_data['entry_submission_date'] = $results[0]->date_created;
			$pluggable_data['payment_status']        = $results[0]->payment_status;
			$pluggable_data['payment_amount']        = $results[0]->payment_amount;
			$pluggable_data['currency']              = $results[0]->currency;
			$pluggable_data['payment_method']        = $results[0]->payment_method;
			$pluggable_data['transaction_id']        = $results[0]->transaction_id;
			$pluggable_data['user']                  = WordPress::get_user_context( $results[0]->created_by );
			$context['response_type']                = 'live';
		} else {
			$pluggable_data['gravity_form']           = '3';
			$pluggable_data['entry_id']               = '13';
			$pluggable_data['user_ip']                = '127.0.0.0';
			$pluggable_data['entry_source_url']       = 'https://example.com';
			$pluggable_data['entry_submission_date']  = '2024-02-05 09:41:59';
			$pluggable_data['payment_status']         = 'Paid';
			$pluggable_data['payment_amount']         = '10.00';
			$pluggable_data['currency']               = 'USD';
			$pluggable_data['payment_method']         = 'visa';
			$pluggable_data['transaction_id']         = 'st_ooi98';
			$pluggable_data['user']['wp_user_id']     = '123';
			$pluggable_data['user']['user_login']     = 'johnd';
			$pluggable_data['user']['display_name']   = 'johnd';
			$pluggable_data['user']['user_firstname'] = 'John';
			$pluggable_data['user']['user_lastname']  = 'Doe';
			$pluggable_data['user']['user_email']     = 'johnd@yopmail.com';
			$pluggable_data['user']['user_role']      = [ 'subscriber' ];
			$context['response_type']                 = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Prepare fluentcrm lists.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_lists( $data ) {

		$list_api  = FluentCrmApi( 'lists' );
		$all_lists = $list_api->all();
		$options   = [];

		if ( ! empty( $all_lists ) ) {
			foreach ( $all_lists as $list ) {
				$options[] = [
					'label' => $list->title,
					'value' => $list->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_contact_status( $data ) {

		$options = [
			[
				'label' => __( 'Subscribed', 'suretriggers' ),
				'value' => 'subscribed',
			],
			[
				'label' => __( 'Pending', 'suretriggers' ),
				'value' => 'pending',
			],
			[
				'label' => __( 'Unsubscribed', 'suretriggers' ),
				'value' => 'unsubscribed',
			],
			[
				'label' => __( 'Bounced', 'suretriggers' ),
				'value' => 'bounced',
			],
			[
				'label' => __( 'Complained', 'suretriggers' ),
				'value' => 'complained',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_fetch_custom_fields( $data ) {

		$options = [
			[
				'label' => __( 'Yes', 'suretriggers' ),
				'value' => 'true',
			],
			[
				'label' => __( 'No', 'suretriggers' ),
				'value' => 'false',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_tags( $data ) {

		if ( ! function_exists( 'FluentCrmApi' ) ) {
			return [];
		}

		$tag_api  = FluentCrmApi( 'tags' );
		$all_tags = $tag_api->all();
		$options  = [];

		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag->title,
					'value' => $tag->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Contact tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_contact_tags( $data ) {

		if ( ! function_exists( 'zeroBSCRM_getCustomerTags' ) ) {
			return [];
		}

		$all_tags = zeroBSCRM_getCustomerTags();
		$options  = [];

		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag['name'],
					'value' => $tag['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Company tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_company_tags( $data ) {

		if ( ! defined( 'ZBS_TYPE_COMPANY' ) ) {
			return [];
		}

		global $wpdb;
		$all_tags = $wpdb->get_results( $wpdb->prepare( "SELECT `ID`,`zbstag_name` FROM `{$wpdb->prefix}zbs_tags` WHERE zbstag_objtype = %d", ZBS_TYPE_COMPANY ) );

		$options = [];
		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag->zbstag_name,
					'value' => $tag->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Companies list.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_companies_list( $data ) {

		if ( ! function_exists( 'zeroBS_getCompanies' ) ) {
			return [];
		}

		$all_companies = zeroBS_getCompanies();
		$options       = [];

		if ( ! empty( $all_companies ) ) {
			foreach ( $all_companies as $company ) {
				$options[] = [
					'label' => $company['name'],
					'value' => $company['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_contact_statuses( $data ) {

		$options = [
			[
				'label' => __( 'Lead', 'suretriggers' ),
				'value' => 'Lead',
			],
			[
				'label' => __( 'Customer', 'suretriggers' ),
				'value' => 'Customer',
			],
			[
				'label' => __( 'Refused', 'suretriggers' ),
				'value' => 'Refused',
			],
			[
				'label' => __( 'Blacklisted', 'suretriggers' ),
				'value' => 'Blacklisted',
			],
			[
				'label' => __( 'Cancelled by Customer', 'suretriggers' ),
				'value' => 'Cancelled by Customer',
			],
			[
				'label' => __( 'Cancelled by Us (Pre-Quote)', 'suretriggers' ),
				'value' => 'Cancelled by Us (Pre-Quote)',
			],
			[
				'label' => __( 'Cancelled by Us (Post-Quote)', 'suretriggers' ),
				'value' => 'Cancelled by Us (Post-Quote)',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare FunnelKit Automations' lists.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_funnel_kit_automations_lists( $data ) {

		if ( ! class_exists( 'BWFCRM_Lists' ) ) {
			return [];
		}

		$bwfcrm_lists = \BWFCRM_Lists::get_lists();

		$options = [];

		foreach ( $bwfcrm_lists as $list ) {
			$options[] = [
				'label' => $list['name'],
				'value' => $list['ID'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare FunnelKit Automations' tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_funnel_kit_automations_tags( $data ) {

		if ( ! class_exists( 'BWFCRM_Tag' ) ) {
			return [];
		}

		$bwfcrm_tags = \BWFCRM_Tag::get_tags();

		$options = [];

		foreach ( $bwfcrm_tags as $tag ) {
			$options[] = [
				'label' => $tag['name'],
				'value' => $tag['ID'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Wishlist Memberlists level.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_wishlistmember_lists( $data ) {

		$wlm_levels = wlmapi_get_levels();
		$options    = [];

		if ( ! empty( $wlm_levels ) ) {
			foreach ( $wlm_levels['levels']['level'] as $list ) {
				if ( isset( $list['name'] ) && ! empty( $list['name'] ) ) {
					$options[] = [
						'label' => $list['name'],
						'value' => (string) $list['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}
	
	/**
	 * Prepare Wishlist Memberlists members.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array<string, array<int<0, max>, array<string, mixed>>|false>
	 */
	public function search_wishlistmember_members( $data ) {

		if ( ! function_exists( 'wlmapi_get_members' ) ) {
			return [];
		}

		$wlm_members = wlmapi_get_members();
		$options     = [];

		if ( ! empty( $wlm_members ) ) {
			foreach ( $wlm_members['members']['member'] as $list ) {
				if ( isset( $list['user_email'] ) && ! empty( $list['user_email'] ) ) {
					$options[] = [
						'label' => $list['user_email'],
						'value' => (string) $list['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor popups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_elementor_popups( $data ) {

		$posts = get_posts(
			[
				'post_type'   => 'elementor_library',
				'orderby'     => 'title',
				'order'       => 'ASC',
				'post_status' => 'publish',
				'meta_query'  => [
					[
						'key'     => '_elementor_template_type',
						'value'   => 'popup',
						'compare' => '=',
					],
				],
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare givewp forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_givewp_forms( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'give_forms',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		$count = wp_count_posts( 'give_forms' )->publish;

		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Prepare givewp recurring forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_givewp_recurring_forms( $data ) {

		global $wpdb;
		$recurring = $wpdb->get_results( $wpdb->prepare( "SELECT form_id FROM {$wpdb->prefix}give_formmeta WHERE meta_key LIKE %s AND meta_value NOT LIKE %s", '_give_recurring', 'no' ), ARRAY_A );
		
		$options = [];
		if ( ! empty( $recurring ) ) {
			foreach ( $recurring as $form_id ) {
				$post_status = get_post_status( $form_id['form_id'] );
				if ( 'trash' !== $post_status ) {
					$options[] = [
						'label' => get_the_title( $form_id['form_id'] ),
						'value' => $form_id['form_id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare givewp forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_givewp_form_fields( $data ) {
		$options = [
			[
				'label' => 'First Name',
				'value' => '_give_donor_billing_first_name',
			],
			[
				'label' => 'Last Name',
				'value' => '_give_donor_billing_last_name',
			],
			[
				'label' => 'Email',
				'value' => '_give_payment_donor_email',
			],
			[
				'label' => 'Donation Amount',
				'value' => '_give_payment_total',
			],
			[
				'label' => 'Currency',
				'value' => '_give_payment_currency',
			],
			[
				'label' => 'Comment',
				'value' => '_give_donation_comment',
			],
			[
				'label' => 'Zip',
				'value' => '_give_donor_billing_zip',
			],
			[
				'label' => 'Country',
				'value' => '_give_donor_billing_country',
			],
			[
				'label' => 'Address 1',
				'value' => '_give_donor_billing_address1',
			],
			[
				'label' => 'Address 2',
				'value' => '_give_donor_billing_address2',
			],
			[
				'label' => 'City',
				'value' => '_give_donor_billing_city',
			],
			[
				'label' => 'State',
				'value' => '_give_donor_billing_state',
			],
		];

		if ( class_exists( '\Give_FFM_Render_Form' ) ) {
			$form_id            = $data['dynamic'];
			$custom_form_fields = [];
			if ( class_exists( '\GiveFormFieldManager\Helpers\Form' ) ) {
				$custom_form_fields = \GiveFormFieldManager\Helpers\Form::get_input_fields( $form_id );
			}

			if ( ! empty( $custom_form_fields ) ) {
				if ( ! empty( $custom_form_fields[2] ) && is_array( $custom_form_fields[2] ) ) {
					foreach ( $custom_form_fields[2] as $custom_form_field ) {
						$custom_form_field['required'] = ( 'no' === $custom_form_field['required'] ) ? false : true;
						$options[]                     = [
							'label' => $custom_form_field['label'],
							'value' => $custom_form_field['name'],
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss group users.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_bb_group_users( $data ) {
		$options = [];

		$group_id = $data['dynamic'];
		$admins   = groups_get_group_admins( $group_id );

		if ( ! empty( $admins ) ) {
			foreach ( $admins as $admin ) {
				$admin_user = get_user_by( 'id', $admin->user_id );
				$options[]  = [
					'label' => $admin_user->display_name,
					'value' => $admin_user->ID,
				];
			}
		}

		$members = groups_get_group_members( [ 'group_id' => $group_id ] );

		if ( isset( $members['members'] ) && ! empty( $members['members'] ) ) {
			foreach ( $members['members'] as $member ) {
				$options[] = [
					'label' => $member->display_name,
					'value' => $member->ID,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss groups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_buddyboss_groups( $data ) {
		global $wpdb;

		$options = [];
		$groups  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups" );
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss public groups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_buddyboss_public_groups( $data ) {
		$options = [];
		$groups  = groups_get_groups();
		if ( isset( $groups['groups'] ) && ! empty( $groups['groups'] ) ) {
			foreach ( $groups['groups'] as $group ) {
				if ( 'public' === $group->status ) {
					$options[] = [
						'label' => $group->name,
						'value' => $group->id,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss profile types list.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_bb_profile_type_list( $data ) {
		$options = [];

		if ( function_exists( 'bp_get_active_member_types' ) ) {
			$types = bp_get_active_member_types(
				[
					'fields' => '*',
				]
			);
			if ( $types ) {
				foreach ( $types as $type ) {
					$options[] = [
						'label' => $type->post_title,
						'value' => $type->ID,
					];
				}
			}
		}

		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss public groups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_buddyboss_group_type( $data ) {
		$options = [];

		if ( ! function_exists( 'bp_groups_get_group_types' ) ) {
			return [];
		}

		$registered_types = bp_groups_get_group_types();
		if ( ! empty( $registered_types ) ) {
			foreach ( $registered_types as $key => $type ) {
				$options[] = [
					'label' => $type,
					'value' => $key,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyBoss Forum Topic Status.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bb_forum_topic_status( $data ) {
		$options = [];

		if ( ! function_exists( 'bbp_get_topic_post_type' ) ) {
			return [];
		}

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forum_args = [
			'post_type'      => bbp_get_topic_post_type(),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
			'posts_per_page' => $limit,
			'offset'         => $offset,
		];

		$forums = get_posts( $forum_args );
		if ( ! empty( $forums ) ) {
			foreach ( $forums as $key => $forum ) {
				$options[] = [
					'label' => $forum,
					'value' => $key,
				];
			}
		}
		$count = count( $options );
		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get BuddyBoss Forums List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bb_forums_list( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		if ( ! function_exists( 'bbp_get_forum_post_type' ) ) {
			return [];
		}

		$forum_args = [
			'post_type'      => bbp_get_forum_post_type(),
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
			'posts_per_page' => $limit,
			'offset'         => $offset,
		];

		$forums = get_posts( $forum_args );
		if ( ! empty( $forums ) ) {
			foreach ( $forums as $key => $forum ) {
				$options[] = [
					'label' => $forum->post_title,
					'value' => $forum->ID,
				];
			}
		}
		$count = count( $options );
		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get BuddyBoss Forums List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bb_topics_list( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		if ( ! function_exists( 'bbp_get_topic_post_type' ) ) {
			return [];
		}

		$topic_args = [
			'post_type'      => bbp_get_topic_post_type(),
			'post_parent'    => $data['dynamic'],
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
			'posts_per_page' => $limit,
			'offset'         => $offset,
		];

		$topics = get_posts( $topic_args );
		if ( ! empty( $topics ) ) {
			foreach ( $topics as $key => $topic ) {
				$options[] = [
					'label' => $topic->post_title,
					'value' => $topic->ID,
				];
			}
		}
		$count = count( $options );
		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Prepare elementor forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_elementor_forms( $data ) {

		$elementor_forms = Utilities::get_elementor_forms();

		$options = [];
		if ( ! empty( $elementor_forms ) ) {
			foreach ( $elementor_forms as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_new_elementor_forms( $data ) {

		global $wpdb;
		$elementor_forms = [];
		$post_metas      = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value
		FROM $wpdb->postmeta pm
			LEFT JOIN $wpdb->posts p
				ON p.ID = pm.post_id
		WHERE p.post_type IS NOT NULL
		AND p.post_status = %s
		AND pm.meta_key = %s
		AND pm.`meta_value` LIKE %s",
				'publish',
				'_elementor_data',
				'%%form_fields%%'
			)
		);

		if ( ! empty( $post_metas ) ) {
			foreach ( $post_metas as $post_meta ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$inner_forms = Utilities::search_elementor_forms( json_decode( $post_meta->meta_value ) );
				if ( ! empty( $inner_forms ) ) {
					foreach ( $inner_forms as $form ) {
						/**
						 *
						 * Ignore line
						 *
						 * @phpstan-ignore-next-line
						 */
						$elementor_forms[ $post_meta->post_id . '_' . $form->id ] = $form->settings->form_name . ' (' . $form->id . ')';
					}
				}
			}
		}

		$options = [];
		if ( ! empty( $elementor_forms ) ) {
			foreach ( $elementor_forms as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => $key,
				];
			}
		}

		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_pluggable_elementor_form_fields( $data ) {
		$result                = [];
		$form_id               = absint( $data['dynamic'] );
		$elementor_form_fields = ( new Utilities() )->get_elementor_form_fields( $data );
		$options               = [];
		if ( ! empty( $elementor_form_fields ) ) {
			foreach ( $elementor_form_fields as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => '{' . $key . '}',
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get all events
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	public function search_event_calendar_event( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'tribe_events',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		$count = wp_count_posts( 'tribe_events' )->publish;

		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get all events tickets
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	public function search_event_calendar_event_tickets( $data ) {
		$event_id = $data['dynamic'];
		$options  = [];

		if ( ! class_exists( 'Tribe__Tickets_Plus__Commerce__WooCommerce__Main' ) ) {
			return [];
		}
		
		$tickets = \Tribe__Tickets_Plus__Commerce__WooCommerce__Main::get_instance()->get_all_event_tickets( $event_id );
		if ( ! empty( $tickets ) ) {
			foreach ( $tickets as $ticket_object ) {
				$options[] = [
					'label' => $ticket_object->name,
					'value' => $ticket_object->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare rsvp event calendar events.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_event_calendar_rsvp_event( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'tribe_events',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			$ticket_handler = new Tribe__Tickets__Tickets_Handler();
			foreach ( $posts as $post ) {

				$get_rsvp_ticket = $ticket_handler->get_event_rsvp_tickets( $post->ID );

				if ( ! empty( $get_rsvp_ticket ) ) {
					$options[] = [
						'label' => $post->post_title,
						'value' => $post->ID,
					];
				}
			}
		}
		$count = wp_count_posts( 'tribe_events' )->publish;

		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Prepare Restrict Content Membership Level.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_restrictcontent_membership_level( $data ) {

		$rcp_memberships = rcp_get_membership_levels();
		$options         = [];

		if ( ! empty( $rcp_memberships ) ) {
			foreach ( $rcp_memberships as $list ) {
				$options[] = [
					'label' => ucfirst( $list->name ),
					'value' => $list->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Restrict Content Customer.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_restrictcontent_customer( $data ) {

		$rcp_users = rcp_get_memberships();
		$options   = [];

		if ( ! empty( $rcp_users ) ) {
			foreach ( $rcp_users as $list ) {
				$user       = get_user_by( 'ID', $list->get_user_id() );
				$user_label = $user->user_email;

				if ( $user->display_name !== $user->user_email ) {
					$user_label .= ' (' . $user->display_name . ')';
				}

				$options[] = [
					'label' => $user_label,
					'value' => $list->get_customer_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}


	/**
	 * Fetch the Presto Player video List.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_ap_presto_player_video_list( $data ) {

		$videos  = ( new Video() )->all();
		$options = [];
		if ( ! empty( $videos ) ) {
			foreach ( $videos as $video ) {
				$options[] = [
					'label' => $video->__get( 'title' ),
					'value' => (string) $video->__get( 'id' ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Presto Player Video percentage.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_prestoplayer_video_percent( $data ) {

		$percents = [ 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 ];
		$options  = [];

		foreach ( $percents as $percent ) {
			$options[] = [
				'label' => $percent . '%',
				'value' => (string) $percent,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get user profile field options.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_user_field_options() {

		$options = apply_filters(
			'sure_trigger_get_user_field_options',
			[
				[
					'label' => __( 'User Name', 'suretriggers' ),
					'value' => 'user_login',
				],
				[
					'label' => __( 'User Email', 'suretriggers' ),
					'value' => 'user_email',
				],
				[
					'label' => __( 'Display Name', 'suretriggers' ),
					'value' => 'display_name',
				],
				[
					'label' => __( 'User Password', 'suretriggers' ),
					'value' => 'user_pass',
				],
				[
					'label' => __( 'Website', 'suretriggers' ),
					'value' => 'user_url',
				],
			]
		);

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get user post field options.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_post_field_options() {

		return [
			'options' => [
				[
					'label'         => __( 'Type', 'suretriggers' ),
					'value'         => 'post_type',
					'dynamic_field' => [
						'type'           => 'select-creatable',
						'ajaxIdentifier' => 'post_types',
					],
				],
				[
					'label'         => __( 'Status', 'suretriggers' ),
					'value'         => 'post_status',
					'dynamic_field' => [
						'type'           => 'select-async',
						'ajaxIdentifier' => 'post_statuses',
					],
				],
				[
					'label'         => __( 'Author', 'suretriggers' ),
					'value'         => 'post_author',
					'dynamic_field' => [
						'type'           => 'select-async',
						'ajaxIdentifier' => 'user',
					],
				],
				[
					'label'         => __( 'Title', 'suretriggers' ),
					'value'         => 'post_title',
					'dynamic_field' => [
						'type' => 'select-creatable',
					],
				],
				[
					'label'         => __( 'Slug', 'suretriggers' ),
					'value'         => 'post_slug',
					'dynamic_field' => [
						'type' => 'select-creatable',
					],
				],
				[
					'label'         => __( 'Content', 'suretriggers' ),
					'value'         => 'post_content',
					'dynamic_field' => [
						'type' => 'html-editor',
					],
				],
			],
			'hasMore' => false,
		];
	}

	/**
	 * Bricksbuilder grouped data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_bb_groups( $data ) {

		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'bp_groups' ) ) ) {

			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %s', $wpdb->prefix . 'bp_groups' ) );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->name,
						'value' => $result->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms.
	 *
	 * @return array
	 */
	public function search_bb_forums() {
		$options        = [];
		$allowed_atatus = [ 'publish', 'private' ];
		$forum_args     = [
			'post_type'      => bbp_get_forum_post_type(),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'any',
		];
		$forums         = get_posts( $forum_args );

		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum ) {
				if ( in_array( $forum->post_status, $allowed_atatus, true ) ) {
					$options[] = [
						'label' => $forum->post_title,
						'value' => $forum->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Affiliate WP Referral Type.
	 *
	 * @return array
	 */
	public function search_affwp_referral_type() {
		$options = [];

		if ( ! function_exists( 'affiliate_wp' ) ) {
			return [];
		}

		$types = affiliate_wp()->referrals->types_registry->get_types();
		if ( ! empty( $types ) ) {
			foreach ( $types as $type_id => $type ) {
				$options[] = [
					'label' => $type['label'],
					'value' => $type_id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Affiliate WP Referral Status.
	 *
	 * @return array
	 */
	public function search_affwp_referral_status() {
		$options = [];

		if ( ! function_exists( 'affwp_get_affiliate_statuses' ) ) {
			return [];
		}

		$statuses = affwp_get_affiliate_statuses();
		if ( ! empty( $statuses ) ) {
			foreach ( $statuses as $key => $status ) {
				$options[] = [
					'label' => $status,
					'value' => $key,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Affiliate WP Affiliates list.
	 *
	 * @return array
	 */
	public function search_affwp_affiliates_list() {
		$options = [];

		global $wpdb;
		$affiliates = $wpdb->get_results( "SELECT affiliate_id FROM {$wpdb->prefix}affiliate_wp_affiliates" );

		if ( ! function_exists( 'affwp_get_affiliate_name' ) ) {
			return [];
		}
		
		if ( ! empty( $affiliates ) ) {
			foreach ( $affiliates as $affiliate ) {
				$options[] = [
					'label' => affwp_get_affiliate_name( $affiliate->affiliate_id ),
					'value' => $affiliate->affiliate_id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Slice WP Affiliates list.
	 *
	 * @return array
	 */
	public function search_slicewp_affiliates_list() {
		$options = [];

		global $wpdb;
		$affiliates = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}slicewp_affiliates" );

		if ( ! function_exists( 'slicewp_get_affiliate_name' ) ) {
			return [];
		}
		
		if ( ! empty( $affiliates ) ) {
			foreach ( $affiliates as $affiliate ) {
				$options[] = [
					'label' => slicewp_get_affiliate_name( $affiliate->id ),
					'value' => $affiliate->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search for commissions list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_slicewp_commissions_list( $data ) {
		$options = [];
		global $wpdb;
		$affiliate_id = $data['dynamic']['affiliate_id'];
		$commissions  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT *
		FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id=%d ORDER BY id DESC ", 
				$affiliate_id
			) 
		);
	
		
		if ( ! empty( $commissions ) ) {
			foreach ( $commissions as $commission ) {
				$options[] = [
					'label' => $commission->reference . '(' . $commission->type . ')',
					'value' => $commission->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}


	/**
	 * Search Affiliate WP Affiliates list.
	 *
	 * @return array
	 */
	public function search_affwp_affiliates_rate_type() {
		$options = [];

		if ( ! function_exists( 'affwp_get_affiliate_rate_types' ) ) {
			return [];
		}

		$rate_types = affwp_get_affiliate_rate_types();
		
		if ( ! empty( $rate_types ) ) {
			foreach ( $rate_types as $key => $rate_type ) {
				$options[] = [
					'label' => $rate_type,
					'value' => $key,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_affiliate_wp_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$affiliate_data = [
			'affiliate_id'    => 1,
			'rest_id'         => '',
			'user_id'         => 1,
			'rate'            => '',
			'rate_type'       => '',
			'flat_rate_basis' => '',
			'payment_email'   => 'admin@gmail.com',
			'status'          => 'active',
			'earnings'        => 0,
			'unpaid_earnings' => 0,
			'referrals'       => 0,
			'visits'          => 0,
			'date_registered' => '2023-01-18 13:35:22',
			'dynamic_coupon'  => 'KDJSKS',
		];

		$referral_data = [
			'referral_id'  => 1,
			'affiliate_id' => 1,
			'visit_id'     => 0,
			'rest_id'      => '',
			'customer_id'  => 0,
			'parent_id'    => 0,
			'description'  => 'Testing',
			'status'       => 'unpaid',
			'amount'       => '12.00',
			'currency'     => '',
			'custom'       => 'custom',
			'context'      => '',
			'campaign'     => '',
			'reference'    => 'Reference',
			'products'     => '',
			'date'         => '2023-01-18 16:36:59',
			'type'         => 'opt-in',
			'payout_id'    => 0,
		];

		if ( ! function_exists( 'affwp_get_dynamic_affiliate_coupons' ) || ! function_exists( 'affwp_get_affiliate' ) || ! function_exists( 'affwp_get_referral' ) ) {
			return [];
		}

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( in_array( $term, [ 'affiliate_approved', 'affiliate_awaiting_approval' ], true ) ) {
			$status    = 'affiliate_approved' === $term ? 'active' : 'pending';
			$affiliate = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_affiliates WHERE affiliate_id = ( SELECT max(affiliate_id) FROM {$wpdb->prefix}affiliate_wp_affiliates )" );

			if ( ! empty( $affiliate ) ) {
				$affiliate                = affwp_get_affiliate( $affiliate->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$context['response_type'] = 'live';
			}
			$affiliate_data['status']  = $status;
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data );

		} elseif ( 'affiliate_makes_referral' == $term ) {
			$type     = isset( $data['dynamic'] ) ? $data['dynamic'] : '';
			$referral = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE referral_id = ( SELECT max(referral_id) FROM {$wpdb->prefix}affiliate_wp_referrals ) AND sale = %s", $type ) );

			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
		} elseif ( 'affiliate_wc_product_purchased' == $term ) {
			$product  = isset( $data['dynamic'] ) ? $data['dynamic'] : '';
			$referral = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE context = 'woocommerce' ORDER BY referral_id DESC LIMIT 1" );

			if ( ! empty( $referral ) ) {
				$referral       = affwp_get_referral( $referral->referral_id );
				$affiliate      = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data = get_object_vars( $affiliate );
				if ( ! empty( $referral ) && function_exists( 'wc_get_order' ) ) {
					$order_id = $referral->reference;
					$order    = wc_get_order( $order_id );
					if ( $order instanceof \WC_Order ) {
						$user_data = WordPress::get_user_context( $order->get_customer_id() );
					}
				}
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$dynamic_coupons           = affwp_get_dynamic_affiliate_coupons( $referral->affiliate_id, false );
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data, $dynamic_coupons );
			if ( ! empty( $referral ) && function_exists( 'wc_get_order' ) ) {
				$order_id = $referral->reference;
				$order    = wc_get_order( $order_id );
				$items    = $order->get_items();
				foreach ( $items as $item ) {
					$context['pluggable_data']['product'] = $item['product_id'];
				}
			} else {
				$context['pluggable_data']['product'] = 1;
			}
		} elseif ( 'affiliate_edd_product_purchased' == $term ) {
			$product  = isset( $data['dynamic'] ) ? $data['dynamic'] : '';
			$referral = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE context = 'edd' ORDER BY referral_id DESC LIMIT 1" );

			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
			if ( ! empty( $referral ) && function_exists( 'edd_get_payment' ) ) {
				$dynamic_coupons           = affwp_get_dynamic_affiliate_coupons( $referral->affiliate_id, false );
				$edd_payment_id            = $referral->reference;
				$payment                   = edd_get_payment( $edd_payment_id );
				$cart_details              = $payment->cart_details;
				$payment                   = get_object_vars( $payment );
				$context['pluggable_data'] = array_merge( $context['pluggable_data'], $dynamic_coupons, $payment );
				foreach ( $cart_details as $detail ) {
					$context['pluggable_data']['product'] = $detail['id'];
				}
			} else {
				$context['pluggable_data']['product'] = 1;
			}
		} elseif ( 'affiliate_mb_product_purchased' == $term ) {
			$product  = isset( $data['dynamic'] ) ? $data['dynamic'] : '';
			$referral = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE context = 'memberpress' ORDER BY referral_id DESC LIMIT 1" );

			if ( ! empty( $referral ) && class_exists( '\MeprTransaction' ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$reference_id             = $referral->reference;
				$transaction              = new MeprTransaction( $reference_id );
				$user_id                  = $transaction->user_id;
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$dynamic_coupons           = affwp_get_dynamic_affiliate_coupons( $referral->affiliate_id, false );
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data, $dynamic_coupons );
			if ( ! empty( $referral ) && class_exists( '\MeprTransaction' ) ) {
				$membership_id                             = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT product_id FROM 
				{$wpdb->prefix}mepr_transactions WHERE id = %d",
						$referral->reference
					) 
				);
				$context['pluggable_data']['product']      = $membership_id;
				$context['pluggable_data']['product_name'] = get_the_title( $membership_id );
			} else {
				$context['pluggable_data']['product']      = 1;
				$context['pluggable_data']['product_name'] = 'membership1';
			}
		} elseif ( 'affiliate_referral_paid' == $term ) {
			$type     = isset( $data['filter']['type']['value'] ) ? $data['filter']['type']['value'] : '';
			$referral = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE type = %s AND status = 'paid'", $type ) );

			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
		} elseif ( 'affiliate_referral_rejected' == $term ) {
			$type     = isset( $data['filter']['type']['value'] ) ? $data['filter']['type']['value'] : '';
			$referral = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE type = %s AND status = 'rejected'", $type ) );
			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
		} else {
			$referral = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE referral_id = ( SELECT max(referral_id) FROM {$wpdb->prefix}affiliate_wp_referrals )" );

			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_jetpack_crm_triggers_last_data( $data ) {
		
		if ( ! function_exists( 'zeroBS_getCompanyCount' ) || ! function_exists( 'zeroBS_getCustomerCount' ) || ! function_exists( 'zeroBS_getQuoteCount' ) ) {
			return [];
		}

		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$company_id     = [ 'company_id' => 1 ];
		$contact_id     = [ 'contact_id' => 1 ];
		$quote_id       = [ 'quote_id' => 1 ];
		$event_id       = [ 'event_id' => 1 ];
		$invoice_id     = [ 'invoice_id' => 1 ];
		$transaction_id = [ 'transaction_id' => 1 ];

		$company = [
			'company_id'                 => 1,
			'company_status'             => 'Lead',
			'company_name'               => 'Example Company',
			'company_email'              => 'info@example.com',
			'main_address_line_1'        => '123 Main Street',
			'main_address_line_2'        => 'Suite 456',
			'main_address_city'          => 'New York',
			'main_address_state'         => 'NY',
			'main_address_postal_code'   => '10001',
			'main_address_country'       => 'United States',
			'second_address_line_1'      => '789 Second Avenue',
			'second_address_line_2'      => 'Floor 10',
			'second_address_city'        => 'Los Angeles',
			'second_address_state'       => 'CA',
			'second_address_postal_code' => '90001',
			'second_address_country'     => 'United States',
			'main_telephone'             => '+1 123-456-7890',
			'secondary_telephone'        => '+1 987-654-3210',
		];

		$contact = [
			'contact_id'                 => 1,
			'status'                     => 'Lead',
			'prefix'                     => 'Mr.',
			'full_name'                  => 'John Doe',
			'first_name'                 => 'John',
			'last_name'                  => 'Doe',
			'email'                      => 'johndoe@example.com',
			'main_address_line_1'        => '123 Main Street',
			'main_address_line_2'        => 'Suite 456',
			'main_address_city'          => 'New York',
			'main_address_state'         => 'NY',
			'main_address_postal_code'   => '10001',
			'main_address_country'       => 'United States',
			'second_address_line_1'      => '789 Second Avenue',
			'second_address_line_2'      => 'Floor 10',
			'second_address_city'        => 'Los Angeles',
			'second_address_state'       => 'CA',
			'second_address_postal_code' => '90001',
			'second_address_country'     => 'United States',
			'home_telephone'             => '+1 123-456-7890',
			'work_telephone'             => '+1 987-654-3210',
			'mobile_telephone'           => '+1 555-555-5555',
		];

		$quote = [
			'quote_id'      => 1,
			'contact_id'    => 2,
			'contact_email' => 'john@example.com',
			'contact_name'  => 'John Doe',
			'status'        => '<strong>Created, not yet accepted</strong>',
			'title'         => 'Sample Quote',
			'value'         => 1000,
			'date'          => '2023-05-23',
			'content'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			'notes'         => 'Additional notes about the quote.',
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		switch ( $term ) {
			case 'company_created':
				if ( zeroBS_getCompanyCount() > 0 ) {
					$company_data             = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_companies WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_companies )" );
					$company                  = JetpackCRM::get_company_context( $company_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $company;
				break;
			case 'company_deleted':
				$context['pluggable_data'] = $company_id;
				break;
			case 'contact_created':
				if ( zeroBS_getCustomerCount() > 0 ) {
					$contact_data             = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_contacts WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_contacts )" );
					$contact                  = JetpackCRM::get_contact_context( $contact_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $contact;
				break;
			case 'contact_deleted':
				$context['pluggable_data'] = $contact_id;
				break;
			case 'event_deleted':
				$context['pluggable_data'] = $event_id;
				break;
			case 'invoice_deleted':
				$context['pluggable_data'] = $invoice_id;
				break;
			case 'quote_accepted':
			case 'quote_created':
				if ( zeroBS_getQuoteCount() > 0 ) {
					$quote_data               = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_quotes WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_quotes )" );
					$quote                    = JetpackCRM::get_quote_context( $quote_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $quote;
				break;
			case 'quote_deleted':
				$context['pluggable_data'] = $quote_id;
				break;
			case 'transaction_deleted':
				$context['pluggable_data'] = $transaction_id;
				break;
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_funnel_kit_automations_triggers_last_data( $data ) {

		if ( ! class_exists( 'BWFCRM_Contact' ) || ! class_exists( 'BWFCRM_Lists' ) || ! class_exists( 'BWFCRM_Tag' ) ) {
			return [];
		}

		$context                  = [];
		$context['response_type'] = 'sample';

		$contact = [
			'contact_id'    => '1',
			'wpid'          => '0',
			'uid'           => '9e74246335fd81b1c4a9123842c12549',
			'email'         => 'johndoe@example.com',
			'first_name'    => 'John',
			'last_name'     => 'Doe',
			'contact_no'    => '+1 555-555-5555',
			'state'         => 'NY',
			'country'       => 'United States',
			'timezone'      => 'New York',
			'creation_date' => '2023-05-29 15:26:03',
			'last_modified' => '2023-05-29 17:08:30',
			'source'        => '',
			'type'          => 'Los Angeles',
			'status'        => '0',
			'tags'          => '["1"]',
			'lists'         => '["2","1"]',
		];

		$list = [
			'list_id'   => 1,
			'list_name' => 'Sample List',
		];

		$tag = [
			'tag_id'   => 1,
			'tag_name' => 'Sample Tag',
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		$recent_contacts = \BWFCRM_Contact::get_recent_contacts( 0, 1, [] );
		$contact_email   = count( $recent_contacts['contacts'] ) > 0 && isset( $recent_contacts['contacts'][0]['email'] ) ? $recent_contacts['contacts'][0]['email'] : '';

		$real_contact = false;
		if ( ! empty( $contact_email ) ) {
			$contact_obj = \BWFCRM_Contact::get_contacts( $contact_email, 0, 1, [], [], OBJECT );

			if ( isset( $contact_obj['contacts'][0] ) ) {
				$contact      = FunnelKitAutomations::get_contact_context( $contact_obj['contacts'][0]->contact );
				$real_contact = true;
			}
		}

		if ( 'contact_added_to_list' === $term || 'contact_removed_from_list' === $term ) {
			$list_id = (int) ( isset( $data['filter']['list_id']['value'] ) ? $data['filter']['list_id']['value'] : '-1' );

			if ( -1 === $list_id ) {
				$lists   = \BWFCRM_Lists::get_lists( [], '', 0, 1 );
				$list_id = count( $lists ) > 0 ? $lists[0]['ID'] : -1;
			}


			if ( -1 !== $list_id ) {
				$list                     = FunnelKitAutomations::get_list_context( $list_id );
				$context['response_type'] = $real_contact ? 'live' : 'sample';
			}

			$context['pluggable_data'] = array_merge( $list, $contact );
		} else {
			$tag_id = (int) ( isset( $data['filter']['tag_id']['value'] ) ? $data['filter']['tag_id']['value'] : '-1' );

			if ( -1 === $tag_id ) {
				$tags   = \BWFCRM_Tag::get_tags( [], '', 0, 1 );
				$tag_id = count( $tags ) > 0 ? $tags[0]['ID'] : -1;
			}


			if ( -1 !== $tag_id ) {
				$tag                      = FunnelKitAutomations::get_tag_context( $tag_id );
				$context['response_type'] = $real_contact ? 'live' : 'sample';
			}

			$context['pluggable_data'] = array_merge( $tag, $contact );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_edd_triggers_last_data( $data ) {
		global $wpdb;
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];
		$order_data                = [
			'order_id'                => 187,
			'customer_email'          => 'john_doe@gmail.com',
			'customer_id'             => 2,
			'user_id'                 => 1,
			'customer_first_name'     => 'Sure',
			'customer_last_name'      => 'Dev',
			'ordered_items'           => 'Price with license — Price one',
			'currency'                => 'USD',
			'status'                  => 'complete',
			'discount_codes'          => '',
			'order_discounts'         => 0.00,
			'order_subtotal'          => 12.00,
			'order_tax'               => 0.00,
			'order_total'             => 12.00,
			'payment_method'          => 'manual',
			'purchase_key'            => 'd797b9576a3895e7424bae2417ed87df',
			'ordered_items_ids'       => 17250,
			'download_id'             => 17250,
			'license_key'             => 'f7736093411cfaed18b56ec60227117b',
			'license_key_expire_date' => '1697524076',
			'license_key_status'      => 'inactive',
		];

		$term        = isset( $data['search_term'] ) ? $data['search_term'] : '';
		$download_id = isset( $data['filter']['download_id']['value'] ) ? $data['filter']['download_id']['value'] : 0;
		if ( 'order_created' === $term || 'order_one_product' === $term ) {
			$order_data['purchase_key'] = '06d3b7d923ca922dc889354f9bc33fbb';

			$args = [
				'number' => 1,
				'status' => [ 'complete', 'refunded', 'partially_refunded', 'renewal' ],
			];
			if ( $download_id > 0 ) {
				$args['download'] = $download_id;
			}
			$payments = edd_get_payments( $args );
			if ( count( $payments ) > 0 ) {
				$order_data = EDD::get_product_purchase_context( $payments[0], $term, $download_id );
			
				$context['response_type'] = 'live';
			} else {
				if ( 'order_one_product' === $term ) {
					$order_data['price_id'] = 1;
				}
			}
		} elseif ( 'stripe_payment_refunded' === $term ) {
			$args     = [
				'number' => 1,
				'status' => 'complete',
				'type'   => 'refund',
			];
			$payments = edd_get_payments( $args );

			if ( count( $payments ) > 0 ) {
				$order_data               = EDD::get_purchase_refund_context( $payments[0] );
				$context['response_type'] = 'live';
			}
		} else {    
			$status = isset( $data['post_type'] ) ? $data['post_type'] : '';
			if ( ! empty( $status ) ) {
				if ( $download_id > 0 ) {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE status= %s AND download_id=%d ORDER BY id DESC", $status, $download_id ) );
				} else {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE status= %s ORDER BY id DESC", $status ) );
				}           
			} else {
				if ( $download_id > 0 ) {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE download_id=%d ORDER BY id DESC", $download_id ) );
				} else {
					$licesnses = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}edd_licenses ORDER BY id DESC" );
				}           
			}
			if ( ! empty( $licesnses ) ) {
				$order_data               = EDD::edd_get_license_data( $licesnses->id, $licesnses->download_id, $licesnses->payment_id );
				$context['response_type'] = 'live';
			} else {
				$order_data = [
					'ID'               => 1,
					'key'              => '23232323232',
					'customer_email'   => 'suretest@example.com',
					'customer_name'    => 'Sure Test',
					'product_id'       => 1,
					'download_id'      => 1,
					'product_name'     => 'Test',
					'activation_limit' => 2,
					'activation_count' => 1,
					'activated_urls'   => 'https://example.com',
					'expiration'       => '1686297914',
					'is_lifetime'      => '0',
					'status'           => $status,
				];
			
			}
		}

		$context['pluggable_data'] = $order_data;
		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_presto_player_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$video_data = [
			'pp_video'            => '1',
			'pp_video_percentage' => '100',
		];

		$video_data['video'] = [
			'id'            => '1',
			'title'         => 'SureTriggers Is Here 🎉 The Easiest Automation Builder WordPress Websites & Apps',
			'type'          => 'youtube',
			'external_id'   => '-cYbNYgylLs',
			'attachment_id' => '0',
			'post_id'       => '127',
			'src'           => 'https://www.youtube.com/watch?v=-cYbNYgylLs',
			'created_by'    => '1',
			'created_at'    => '2022-11-10 00:28:25',
			'updated_at'    => '2022-11-10 00:34:40',
			'deleted_at'    => '',
		];

		$video_data['media']['tag'] = [
			'Demo',
			'Demo1',
		];

		$mediahub_data = [
			'post_id'      => '1',
			'activity_id'  => '2',
			'post_author'  => '1',
			'post_content' => 'New sample post...!',
			'post_title'   => 'Sample Post',
			'post_excerpt' => 'sample',
			'post_status'  => 'publish',
			'post_type'    => 'pp_video_block',
		];

		$videos = ( new Video() )->all();

		if ( count( $videos ) > 0 ) {
			if ( ! empty( $data['filter'] ) ) {
				$video_id = $data['filter']['pp_video']['value'];
				if ( -1 === $video_id ) {
					global $wpdb;
					$args                 = [
						'numberposts' => 1,
						'orderby'     => 'rand',
						'order'       => 'ASC',
						'post_type'   => 'pp_video_block',
					];
					$posts                = get_posts( $args );
					$presto_post_video_id = $posts[0]->ID;
					$presto_video_id      = $wpdb->get_var( $wpdb->prepare( 'SELECT * FROM wp_presto_player_videos WHERE post_id = %d', $presto_post_video_id ) );
					$video_id             = $presto_video_id;
				}
				$pp_videos = ( new Video( $video_id ) )->toArray();
				if ( ! empty( $pp_videos ) ) {
					$video_data          = [];
					$video_data['video'] = $pp_videos;
					if ( is_array( $video_data ) && array_key_exists( 'post_id', $video_data['video'] ) ) {
						$mediahub_data = WordPress::get_post_context( (int) $video_data['video']['post_id'] );
						$media_tags    = get_the_terms( (int) $video_data['video']['post_id'], 'pp_video_tag' );
						if ( ! empty( $media_tags ) && is_array( $media_tags ) && isset( $media_tags[0] ) ) {
							$tag_name = [];
							foreach ( $media_tags as $tag ) {
								$tag_name[] = $tag->name;
							}
							$video_data['media']['tag'] = $tag_name;
						}
					}
					$video_data['pp_video']            = $video_id;
					$video_data['pp_video_percentage'] = isset( $data['filter']['pp_video_percentage']['value'] ) ? $data['filter']['pp_video_percentage']['value'] : '100';
					$user_data                         = WordPress::get_user_context( (int) $video_data['video']['created_by'] );
	
					$context['response_type'] = 'live';
				}
			}
		}

		$context['pluggable_data'] = array_merge( $user_data, $video_data, $mediahub_data );

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_member_press_triggers_last_data( $data ) {
		global $wpdb;

		$term = $data['search_term'] ? $data['search_term'] : '';
	
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_id'                 => '190',
			'membership_title'              => 'Sample Membership',
			'amount'                        => '12.00',
			'total'                         => '12.00',
			'tax_amount'                    => '0.00',
			'tax_rate'                      => '0.00',
			'trans_num'                     => 't_63a03f3334f44',
			'status'                        => 'complete',
			'subscription_id'               => '0',
			'membership_url'                => site_url() . '/register/premium/',
			'membership_featured_image_id'  => '521',
			'membership_featured_image_url' => SURE_TRIGGERS_URL . 'assets/images/sample.svg',
		];

		$membership_id = (int) ( isset( $data['filter']['membership_id']['value'] ) ? $data['filter']['membership_id']['value'] : '-1' );

		if ( in_array( $term, [ 'mepr-event-transaction-expired', 'mepr_subscription_transition_status', 'mepr-event-transaction-paused' ] ) ) {
			$status = 'cancelled';
			if ( 'mepr-event-transaction-paused' === $term ) {
				$status = 'suspended';
			} 
			if ( $membership_id > 0 ) {
				$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_subscriptions WHERE product_id= %s AND status=%s ORDER BY id DESC LIMIT 1", $membership_id, $status ) );
			} else {
				$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_subscriptions WHERE status=%s ORDER BY id DESC LIMIT 1", $status ) );
				
			}
			if ( ! empty( $subscription ) ) {
	
				$membership_data = MemberPress::get_subscription_context( $subscription );
				$user_data       = WordPress::get_user_context( $subscription->user_id );
				
			
				$context['response_type'] = 'live';
			}
		} elseif ( 'mepr-coupon-code-redeemed' === $term ) {
			
			$coupon_id = (int) ( isset( $data['filter']['coupon_id']['value'] ) ? $data['filter']['coupon_id']['value'] : '-1' );
			
			if ( $coupon_id > 0 ) {
				$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_transactions WHERE coupon_id= %s ORDER BY id DESC LIMIT 1", $coupon_id ) );
			} else {
				$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_transactions WHERE coupon_id!= %s ORDER BY id DESC LIMIT 1", '0' ) );
			}
		
			
			if ( ! empty( $subscription ) ) {

				$membership_data              = MemberPress::get_membership_context( $subscription );                
				$user_data                    = WordPress::get_user_context( $subscription->user_id );
				$membership_data['coupon_id'] = $subscription->coupon_id; 
				$membership_data['coupon']    = get_post( $subscription->coupon_id ); 
				$context['response_type']     = 'live';
			}
		} else {

			if ( $membership_id > 0 ) {

				$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_transactions WHERE product_id= %s ORDER BY id DESC LIMIT 1", $membership_id ) );
			} else {
				$subscription = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mepr_transactions ORDER BY id DESC LIMIT 1" );
			}
		
			
			if ( ! empty( $subscription ) ) {

				$membership_data = MemberPress::get_membership_context( $subscription );
				$user_data       = WordPress::get_user_context( $subscription->user_id );
				
			
				$context['response_type'] = 'live';
			}
		}
		

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );
		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wishlist_member_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_level_id'   => '1',
			'membership_level_name' => 'Sample Membership Level',
		];

		$membership_level_id = (int) ( isset( $data['filter']['membership_level_id']['value'] ) ? $data['filter']['membership_level_id']['value'] : '-1' );

		if ( $membership_level_id > 0 ) {
			$membership = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wlm_userlevels WHERE level_id= %s ORDER BY id DESC LIMIT 1", $membership_level_id ) );
		} else {
			$membership = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wlm_userlevels ORDER BY id DESC LIMIT 1" );
		}
		if ( ! empty( $membership ) ) {
			$membership_data = WishlistMember::get_membership_detail_context( (int) $membership->level_id, (int) $membership->user_id );
			$user_data       = WordPress::get_user_context( $membership->user_id );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$post_data = [
			'post_id'      => '1',
			'activity_id'  => '2',
			'post_author'  => '1',
			'post_content' => 'New sample post...!',
			'post_title'   => 'Sample Post',
			'post_excerpt' => 'sample',
			'post_status'  => 'publish',
			'post_type'    => 'peepso-post',
		];

		$post = $wpdb->get_row( "SELECT act_id, act_owner_id, act_external_id FROM {$wpdb->prefix}peepso_activities ORDER BY act_id DESC LIMIT 1" );

		if ( ! empty( $post ) ) {
			$post_data = PeepSo::get_pp_activity_context( (int) $post->act_external_id, (int) $post->act_id );
			$user_data = WordPress::get_user_context( $post->act_owner_id );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $post_data );

		return $context;
	}

	/**
	 * Get last data for Peepso User triggers.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_user_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';
		$term                     = $data['search_term'];

		if ( ! class_exists( 'PeepSoUser' ) ) {
			return [];
		}

		if ( 'user_follows_member' === $term || 'user_gains_follower' === $term ) {
			$member_id = $data['filter']['follow_user_id']['value'];
			if ( -1 === $member_id ) {
				$followers = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM 
				{$wpdb->prefix}peepso_user_followers WHERE uf_follow = %d ORDER BY uf_id DESC LIMIT 1",
						1
					) 
				);
			} else {
				$followers = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM 
				{$wpdb->prefix}peepso_user_followers WHERE uf_passive_user_id = %d AND 
				uf_follow = %d ORDER BY uf_id DESC LIMIT 1",
						$member_id,
						1
					) 
				);
			}
		} elseif ( 'user_unfollows_member' === $term || 'user_loses_follower' === $term ) {
			$member_id = $data['filter']['follow_user_id']['value'];
			if ( -1 === $member_id ) {
				$followers = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM 
				{$wpdb->prefix}peepso_user_followers WHERE uf_follow = %d ORDER BY uf_id DESC LIMIT 1",
						0
					) 
				);
			} else {
				$followers = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM 
				{$wpdb->prefix}peepso_user_followers WHERE uf_passive_user_id = %d AND 
				uf_follow = %d ORDER BY uf_id DESC LIMIT 1",
						$member_id,
						0
					) 
				);
			}
		} elseif ( 'user_updates_avatar' === $term ) {
			$followers = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM 
			{$wpdb->prefix}peepso_users WHERE usr_avatar_custom = %d AND usr_role = %s 
			ORDER BY usr_last_activity DESC LIMIT 1",
					1,
					'member'
				) 
			);
		} elseif ( 'user_updates_specific_profile_field' === $term ) {
			$field_id  = $data['filter']['user_profile_field_id']['value'];
			$followers = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM 
			{$wpdb->prefix}peepso_users WHERE usr_role = %s 
			ORDER BY usr_last_activity DESC LIMIT 1",
					'member'
				) 
			);
		}
		if ( 'user_updates_avatar' === $term ) {
			if ( ! empty( $followers ) ) {
				$context_data['user']     = WordPress::get_user_context( $followers[0]->usr_id );
				$context['response_type'] = 'live';
			} else {
				$context_data['user'] = WordPress::get_sample_user_context();
			}
		} elseif ( 'user_updates_specific_profile_field' === $term ) {
			if ( ! empty( $followers ) ) {
				$user = PeepSoUser::get_instance( $followers[0]->usr_id );
				$user->profile_fields->load_fields();
				$user_fields = $user->profile_fields->get_fields();
				foreach ( $user_fields as $key => $value ) {
					$val = get_user_meta( $followers[0]->usr_id, $value->key, true );
					if ( '' != $val ) {
						$context_data[ $value->title ] = $val;
					}
				}
				$curruser                    = get_userdata( $followers[0]->usr_id );
				$context_data['user_id']     = $followers[0]->usr_id;
				$context_data['user_email']  = $user->get_email();
				$context_data['avatar_url']  = $user->get_avatar();
				$context_data['profile_url'] = $user->get_profileurl();
				$context_data['about_me']    = get_user_meta( $followers[0]->usr_id, 'description', true );
				if ( $curruser instanceof \WP_User ) {
					$context_data['website'] = $curruser->user_url;
				}
				$context_data['role']     = $user->get_user_role();
				$context['response_type'] = 'live';
			} else {
				$context_data['user'] = WordPress::get_sample_user_context();
			}
		} else {
			if ( ! empty( $followers ) ) {
				$context_data['follower_user']  = WordPress::get_user_context( $followers[0]->uf_active_user_id );
				$context_data['following_user'] = WordPress::get_user_context( $followers[0]->uf_passive_user_id );
				$context['response_type']       = 'live';
			} else {
				$context_data['follower_user']  = WordPress::get_sample_user_context();
				$context_data['following_user'] = WordPress::get_sample_user_context();
			}
		}

		$context['pluggable_data'] = $context_data;

		return $context;
	}

	/**
	 * Search Peepso User Profile Fields list.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_profile_field_list( $data ) {
		$options = [];
		if ( ! class_exists( 'PeepSoUser' ) ) {
			return [];
		}
		$options    = [
			[
				'label' => 'Allow others to "like" my profile',
				'value' => 'peepso_is_profile_likable',
			],
			[
				'label' => 'Hide my birthday year',
				'value' => 'peepso_hide_birthday_year',
			],
			[
				'label' => 'Who can see my profile',
				'value' => 'usr_profile_acc',
			],
			[
				'label' => 'Who can post on my profile',
				'value' => 'peepso_profile_post_acc',
			],
			[
				'label' => "Don't show my online status",
				'value' => 'peepso_hide_online_status',
			],
			[
				'label' => 'My timezone',
				'value' => 'peepso_gmt_offset',
			],
		];
		$peepsouser = PeepSoUser::get_instance( 0 );
		$peepsouser->profile_fields->load_fields();
		$fields = $peepsouser->profile_fields->get_fields();
		foreach ( $fields as $field ) {
			if ( 1 == $field->published ) {
				$options[] = [
					'label' => $field->title,
					'value' => $field->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Peepso Roles list.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_role_list( $data ) {
		$options = [
			[
				'label' => 'Community Member',
				'value' => 'member',
			],
			[
				'label' => 'Community Moderator',
				'value' => 'moderator',
			],
			[
				'label' => 'Community Administrator',
				'value' => 'admin',
			],
			[
				'label' => 'Banned',
				'value' => 'ban',
			],
			[
				'label' => 'Pending user email verification',
				'value' => 'register',
			],
			[
				'label' => 'Pending admin approval',
				'value' => 'verified',
			],
		];
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Peepso Users list.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_follow_user_list( $data ) {
		$options = [];
		global $wpdb;
		$users = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}peepso_users", ARRAY_A );
		if ( count( $users ) > 0 ) {
			foreach ( $users as $user ) {
				$user_by_id = get_user_by( 'id', $user['usr_id'] );
				if ( $user_by_id instanceof \WP_User ) {
					$options[] = [
						'label' => sprintf( '%s %s [%s]', $user_by_id->last_name, $user_by_id->first_name, $user_by_id->user_email ),
						'value' => $user['usr_id'],
					];
				} else {
					$options[] = [
						'label' => '#' . $user['usr_id'],
						'value' => $user['usr_id'],
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_restrict_content_pro_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_level_id'          => '190',
			'membership_level'             => 'Sample Membership',
			'membership_initial_payment'   => '0.00',
			'membership_recurring_payment' => '0.00',
			'membership_expiry_date'       => 'January 22, 2023',
		];

		$customer_id   = (int) ( isset( $data['filter']['membership_customer_id']['value'] ) ? $data['filter']['membership_customer_id']['value'] : '-1' );
		$membership_id = (int) ( isset( $data['filter']['membership_level_id']['value'] ) ? $data['filter']['membership_level_id']['value'] : '-1' );

		$args = [];
		if ( 'membership_purchased' === $data['search_term'] ) {
			$args = [
				'status'  => 'active',
				'number'  => 1,
				'orderby' => 'id',
			];  
		} elseif ( 'membership_cancelled' === $data['search_term'] ) {
			$args = [
				'status'  => 'cancelled',
				'number'  => 1,
				'orderby' => 'id',
			];  
		} elseif ( 'membership_expired' === $data['search_term'] ) {
			$args = [
				'status'  => 'expired',
				'number'  => 1,
				'orderby' => 'id',
			];
		}

		if ( 'membership_expired' === $data['search_term'] && -1 !== $customer_id ) {
			$args['customer_id'] = $customer_id;
		}

		if ( -1 !== $membership_id ) {
			$args['object_id'] = $membership_id;
		}

		$memberships = rcp_get_memberships( $args );
		if ( count( $memberships ) > 0 ) {
			$membership_data = RestrictContent::get_rcp_membership_detail_context( $memberships[0] );
			$user_data       = WordPress::get_user_context( $memberships[0]->get_user_id() );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_events_calendar_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$event_data = [
			'event_id'  => 1,
			'event'     => [
				'ID'                    => 58,
				'post_author'           => 1,
				'post_date'             => '2023-01-19 09:27:58',
				'post_date_gmt'         => '2023-01-19 09:27:58',
				'post_content'          => '',
				'post_title'            => 'New event',
				'post_excerpt'          => '',
				'post_status'           => 'publish',
				'comment_status'        => 'open',
				'ping_status'           => 'closed',
				'post_password'         => '',
				'post_name'             => 'new-event',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2023-01-19 09:44:25',
				'post_modified_gmt'     => '2023-01-19 09:44:25',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'http://connector.com/?post_type=tribe_events&#038;p=58',
				'menu_order'            => -1,
				'post_type'             => 'tribe_events',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			],
			'attendies' => [
				'order_id'           => 68,
				'purchaser_name'     => 'John Doe',
				'purchaser_email'    => 'john@test.com',
				'provider'           => 'Tribe__Tickets__RSVP',
				'provider_slug'      => 'rsvp',
				'purchase_time'      => '2023-01-19 09:48:43',
				'optout'             => 1,
				'ticket'             => 'Prime',
				'attendee_id'        => 68,
				'security'           => '2cefc3b53e',
				'product_id'         => 65,
				'check_in'           => '',
				'order_status'       => 'yes',
				'order_status_label' => 'Going',
				'user_id'            => 1,
				'ticket_sent'        => 1,
				'event_id'           => 58,
				'ticket_name'        => 'Prime',
				'holder_name'        => 'John Doe',
				'holder_email'       => 'john@test.com',
				'ticket_id'          => 68,
				'qr_ticket_id'       => 68,
				'security_code'      => '2cefc3b53e',
				'attendee_meta'      => '',
				'is_subscribed'      => '',
				'is_purchaser'       => 1,
				'ticket_exists'      => 1,
			],
		];

		$event_id = (int) ( isset( $data['filter']['event_id']['value'] ) ? $data['filter']['event_id']['value'] : '-1' );
		$term     = $data['search_term'];

		if ( 'event_register' === $term || 'attendee_registered_event' === $term ) {
			$args = [
				'post_type'   => 'tribe_rsvp_attendees',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'post_status' => 'publish',
				'numberposts' => 1,
			];

			if ( -1 !== $event_id ) {
				$args['meta_query'] = [
					[
						'key'   => '_tribe_rsvp_event',
						'value' => $event_id,
					],
				];
			}

			$attendees = get_posts( $args );

			if ( count( $attendees ) > 0 ) {
				$attendee    = $attendees[0];
				$attendee_id = $attendee->ID;

				$product_id = get_post_meta( $attendee_id, '_tribe_rsvp_product', true );
				$order_id   = get_post_meta( $attendee_id, '_tribe_rsvp_order', true );

				$event_context = TheEventCalendar::get_event_context( $product_id, $order_id );

				if ( ! empty( $event_context ) ) {
					$event_data               = $event_context;
					$context['response_type'] = 'live';
				}
			}
		} elseif ( 'event_attends' === $term ) {
			if ( -1 == $event_id ) {
				$args     = [
					'numberposts' => 1,
					'orderby'     => 'rand',
					'post_type'   => 'tribe_events',
				];
				$posts    = get_posts( $args );
				$event_id = $posts[0]->ID;
			}
			$args      = [
				'post_type'   => 'tribe_rsvp_attendees',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'post_status' => 'publish',
				'numberposts' => 1,
				'meta_query'  => [
					'relation' => 'AND',
					[
						'key'     => '_tribe_rsvp_checkedin',
						'value'   => 1,
						'compare' => '=',
					],
					[
						'key'     => '_tribe_rsvp_event',
						'value'   => $event_id,
						'compare' => '=',
					],
				],
			];
			$attendees = get_posts( $args );
			if ( ! function_exists( 'tribe_tickets_get_attendees' ) ) {
				return [];
			}
			if ( ! empty( $attendees ) ) {
				$attendee    = $attendees[0];
				$attendee_id = $attendee->ID;
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$attendee_details = tribe_tickets_get_attendees( $attendee_id, 'rsvp_order' );
				foreach ( $attendee_details as $detail ) {
					if ( (int) $detail['attendee_id'] !== (int) $attendee_id ) {
						continue;
					}
					$attendee = $detail;
				}
				$product_id    = get_post_meta( $attendee_id, '_tribe_rsvp_product', true );
				$order_id      = get_post_meta( $attendee_id, '_tribe_rsvp_order', true );
				$event_context = TheEventCalendar::get_event_context( $product_id, $order_id );
				if ( ! empty( $event_context ) ) {
					$event_data               = array_merge( $attendee, $event_context );
					$context['response_type'] = 'live';
				}
			} else {
				$order      = [
					'order_id'           => 7962,
					'purchaser_name'     => 'bella4 bella4',
					'purchaser_email'    => 'bella4@yopmail.com',
					'provider'           => 'Tribe__Tickets__RSVP',
					'provider_slug'      => 'rsvp',
					'purchase_time'      => '2024 - 03 - 04 07:26:41',
					'optout'             => 1,
					'ticket'             => 'test test',
					'attendee_id'        => 7962,
					'security'           => 'eb3a2d7bc4',
					'product_id'         => 7959,
					'check_in'           => 1,
					'order_status'       => 'yes',
					'order_status_label' => 'Going',
					'user_id'            => 35,
					'ticket_sent'        => 1,
					'event_id'           => 7956,
					'ticket_name'        => 'test test',
					'holder_name'        => 'bella4 bella4',
					'holder_email'       => 'bella4@yopmail.com',
					'ticket_id'          => 7962,
					'qr_ticket_id'       => 7962,
					'security_code'      => 'eb3a2d7bc4',
					'is_purchaser'       => 1,
					'ticket_exists'      => 1,
				];
				$event_data = array_merge( $order, $event_data );
			}
		} elseif ( 'event_attendee_wc_register' === $term ) {
			if ( ! class_exists( 'Tribe__Tickets__Tickets' ) ) {
				return [];
			}
			if ( -1 === $event_id ) {
				$args     = [
					'numberposts' => 1,
					'orderby'     => 'rand',
					'post_type'   => 'tribe_events',
				];
				$posts    = get_posts( $args );
				$event_id = $posts[0]->ID;
			}
			$event_ticket_id = (int) ( isset( $data['filter']['event_ticket_id']['value'] ) ? $data['filter']['event_ticket_id']['value'] : '-1' );
			if ( -1 === $event_ticket_id ) {
				$tickets         = \Tribe__Tickets__Tickets::get_all_event_tickets( $event_id );
				$random_key      = array_rand( $tickets );
				$random_value    = $tickets[ $random_key ];
				$event_ticket_id = $random_value;
			}
			$args = [
				'post_type'   => 'tribe_wooticket',
				'orderby'     => 'ID',
				'order'       => 'DESC',
				'post_status' => 'publish',
				'meta_query'  => [
					'relation' => 'AND',
					[
						'key'     => '_tribe_wooticket_event',
						'value'   => $event_id,
						'compare' => '=',
					],
					[
						'key'     => '_tribe_wooticket_product',
						'value'   => $event_ticket_id,
						'compare' => '=',
					],
				],
			];

			$woo_attendees = get_posts( $args );
			if ( ! empty( $woo_attendees ) ) {
				$attendee    = $woo_attendees[0];
				$attendee_id = $attendee->ID;
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$attendee_details = tribe_tickets_get_attendees( $attendee_id, 'rsvp_order' );
				foreach ( $attendee_details as $detail ) {
					if ( (int) $detail['attendee_id'] !== (int) $attendee_id ) {
						continue;
					}
					$attendee = $detail;
				}
				$order_id      = get_post_meta( $attendee_id, '_tribe_wooticket_order', true );
				$product_id    = get_post_meta( $attendee_id, '_tribe_wooticket_product', true );
				$event_context = TheEventCalendar::get_event_context( $product_id, $order_id );
				if ( ! empty( $event_context ) ) {
					$event_data               = $event_context;
					$context['response_type'] = 'live';
				}
				$context['response_type'] = 'live';
			}
		}

		$context['pluggable_data'] = $event_data;

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_triggers_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];
		$user_data                 = WordPress::get_sample_user_context();

		$product_data['product'] = [
			'id'                => '169',
			'name'              => 'Sample Product',
			'description'       => 'This is description of sample product.',
			'short_description' => 'This is short description of sample product.',
			'image_url'         => SURE_TRIGGERS_URL . 'assets/images/sample.svg',
			'slug'              => 'sample-product',
			'status'            => 'publish',
			'type'              => 'simple',
			'price'             => '89',
			'featured'          => '0',
			'sku'               => 'hoodie-blue-sm',
			'regular_price'     => '90',
			'sale_price'        => '89',
			'total_sales'       => '21',
			'category'          => 'Uncategorized',
			'tags'              => 'sample, new, 2022',
		];

		$comment_data = [
			'comment_id'           => '1',
			'comment'              => 'This is a sample comment..!',
			'comment_author'       => 'testsure',
			'comment_date'         => '2023-06-23 10:10:40',
			'comment_author_email' => 'testsure@example.com',
		];

		$order_data = [
			'order_id'             => '500',
			'total_order_value'    => '45',
			'currency'             => 'USD',
			'shipping_total'       => '5',
			'order_payment_method' => 'cod',
			'billing_firstname'    => 'John',
			'billing_lastname'     => 'Doe',
			'billing_company'      => 'BSF',
			'billing_address_1'    => '1004 Beaumont',
			'billing_address_2'    => '',
			'billing_city'         => 'Casper',
			'billing_state'        => 'Wyoming',
			'billing_postcode'     => '82601',
			'billing_country'      => 'US',
			'billing_email'        => 'john_doe@gmail.com',
			'billing_phone'        => '(307) 7626541',
			'shipping_firstname'   => 'John',
			'shipping_lastname'    => 'Doe',
			'shipping_company'     => 'BSF',
			'shipping_address_1'   => '1004 Beaumont',
			'shipping_address_2'   => '',
			'shipping_city'        => 'Casper',
			'shipping_state'       => 'Wyoming',
			'shipping_postcode'    => '82601',
			'shipping_country'     => 'US',
			'coupon_codes'         => 'e3mstekq, f24sjakb',
			'total_items_in_order' => '1',
			'user_id'              => '1',
		];

		$variation_data = [
			'product_variation_id' => '626',
			'product_variation'    => 'Color: Silver',
		];

        $order_sample_data = json_decode( '{"id":37,"parent_id":0,"status":"processing","currency":"USD","version":"7.3.0","prices_include_tax":false,"date_created":{"date":"2023-01-18 08:00:49.000000","timezone_type":1,"timezone":"+00:00"},"date_modified":{"date":"2023-01-18 08:00:50.000000","timezone_type":1,"timezone":"+00:00"},"discount_total":"0","discount_tax":"0","shipping_total":"0","shipping_tax":"0","cart_tax":"0","total":"22.00","total_tax":"0","customer_id":1,"order_key":"wc_order_VdLfjJ9vP7pDs","billing":{"first_name":"John","last_name":"Rana","company":"","address_1":"test","address_2":"","city":"Mohali","state":"AL","postcode":"12344","country":"US","email":"test@example.com","phone":"13232323"},"shipping":{"first_name":"","last_name":"","company":"","address_1":"","address_2":"","city":"","state":"","postcode":"","country":"","phone":""},"payment_method":"cod","payment_method_title":"Cash on delivery","transaction_id":"","customer_ip_address":"::1","customer_user_agent":"Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/108.0.0.0 Safari\/537.36","created_via":"checkout","customer_note":"","date_completed":null,"date_paid":null,"cart_hash":"10b8e2799df0f88e1506edc0f3ed99c9","order_stock_reduced":true,"download_permissions_granted":true,"new_order_email_sent":true,"recorded_sales":true,"recorded_coupon_usage_counts":true,"number":"37","meta_data":[{"id":204,"key":"is_vat_exempt","value":"no"}],"line_items":{"id":"2, 3","order_id":"37, 37","name":"Variable product - Red, Test product","product_id":"34, 31","variation_id":"35, 0","quantity":"1, 1","tax_class":", ","subtotal":"12, 10","subtotal_tax":"0, 0","total":"12, 10","total_tax":"0, 0","taxes":", ","meta_data":", "},"tax_lines":[],"shipping_lines":[],"fee_lines":[],"coupon_lines":[],"products":[{"id":2,"order_id":37,"name":"Variable product - Red","product_id":34,"variation_id":35,"quantity":1,"tax_class":"","subtotal":"12","subtotal_tax":"0","total":"12","total_tax":"0","taxes":{"total":[],"subtotal":[]},"meta_data":{"19":{"key":"color","value":"Red","display_key":"Color","display_value":"<p>Red<\/p>\n"}}},{"id":3,"order_id":37,"name":"Test product","product_id":31,"variation_id":0,"quantity":1,"tax_class":"","subtotal":"10","subtotal_tax":"0","total":"10","total_tax":"0","taxes":{"total":[],"subtotal":[]},"meta_data":[]}],"quantity":"1, 1","wp_user_id":1,"user_login":"john","display_name":"john smith","user_firstname":"John","user_lastname":"Smith","user_email":"test@example.com","user_role":["subscriber"]}', true ); //phpcs:ignore

		$product_id = (int) ( isset( $data['filter']['product_id']['value'] ) ? $data['filter']['product_id']['value'] : -1 );
		$term       = isset( $data['search_term'] ) ? $data['search_term'] : '';

		$order_status      = ( isset( $data['filter']['to_status']['value'] ) ? $data['filter']['to_status']['value'] : -1 );
		$from_order_status = ( isset( $data['filter']['from_status']['value'] ) ? $data['filter']['from_status']['value'] : -1 );
		$order_note_type   = ( isset( $data['filter']['note_type']['value'] ) ? $data['filter']['note_type']['value'] : -1 );

		if ( in_array( $term, [ 'product_added_to_cart', 'product_viewed' ], true ) ) {
			if ( -1 === $product_id ) {
				$args     = [
					'post_type'   => 'product',
					'orderby'     => 'ID',
					'order'       => 'DESC',
					'post_status' => 'publish',
					'numberposts' => 1,
				];
				$products = get_posts( $args );

				if ( count( $products ) > 0 ) {
					$product_id = $products[0]->ID;
				}
			}

			if ( -1 !== $product_id ) {
				$post                       = get_post( $product_id );
				$user_data                  = WordPress::get_user_context( $post->post_author );
				$product_data['product_id'] = $product_id;
				$product_data['product']    = WooCommerce::get_product_context( $product_id );
				$terms                      = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
					$cat_name = [];
					foreach ( $terms as $cat ) {
						$cat_name[] = $cat->name;
					}
					$product_data['product']['category'] = implode( ', ', $cat_name );
				}
				$terms_tags = get_the_terms( $product_id, 'product_tag' );
				if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
					$tag_name = [];
					foreach ( $terms_tags as $tag ) {
						$tag_name[] = $tag->name;
					}
					$product_data['product']['tag'] = implode( ', ', $tag_name );
				}
                unset( $product_data['product']['id'] ); //phpcs:ignore
				$context['response_type'] = 'live';
			}

			if ( 'product_added_to_cart' === $term ) {
				$product_data['product_quantity'] = 1;
			}

			$context['pluggable_data'] = array_merge( $product_data, $user_data );

		} elseif ( 'product_reviewed' === $term ) {
			$comment_args = [
				'number'  => 1,
				'type'    => 'review',
				'orderby' => 'comment_ID',
				'post_id' => -1 !== $product_id ? $product_id : 0,
			];

			$comments = get_comments( $comment_args );

			if ( count( $comments ) > 0 ) {
				$comment      = $comments[0];
				$comment_data = [
					'comment_id'           => $comment->comment_ID,
					'comment'              => $comment->comment_content,
					'comment_author'       => $comment->comment_author,
					'comment_date'         => $comment->comment_date,
					'comment_author_email' => $comment->comment_author_email,
				];
				$product_data = WooCommerce::get_product_context( $comment->comment_post_ID );
				if ( is_object( $comment ) ) {
					$terms = get_the_terms( (int) $comment->comment_post_ID, 'product_cat' );
					if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
						$cat_name = [];
						foreach ( $terms as $cat ) {
							$cat_name[] = $cat->name;
						}
						$product_data['product']['category'] = implode( ', ', $cat_name );
					}
					$terms_tags = get_the_terms( (int) $comment->comment_post_ID, 'product_tag' );
					if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
						$tag_name = [];
						foreach ( $terms_tags as $tag ) {
							$tag_name[] = $tag->name;
						}
						$product_data['product']['tag'] = implode( ', ', $tag_name );
					}
				}
				$user_data                = WordPress::get_user_context( $comment->user_id );
				$context['response_type'] = 'live';
			}

			$context['pluggable_data'] = array_merge( $product_data, $user_data, $comment_data );

		} elseif ( 'product_purchased' === $term ) {
			$order_id                 = 0;
			$product_data['quantity'] = '1';
			if ( -1 !== $product_id ) {
				$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );
				if ( count( $order_ids ) > 0 ) {
					$order_id = $order_ids[0];
				}
			} else {
				$orders = wc_get_orders( [ 'numberposts' => 1 ] );
				if ( count( $orders ) > 0 ) {
					$order_id = $orders[0]->get_id();
				}
			}

			if ( 0 !== $order_id ) {
				$order = wc_get_order( $order_id );

				if ( $order ) {
					$user_id = $order->get_customer_id();
					$items   = $order->get_items();

					$product_ids = [];

					$iteration = 0;
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$item_id = $item->get_product_id();
							if ( -1 === $product_id && 0 === $iteration ) {
								$product_ids[] = $item_id;
								break;
							} elseif ( $item_id === $product_id ) {
								$product_ids[] = $item_id;
								break;
							}
						}

						$iteration++;
					}
					$order_data                         = WooCommerce::get_order_context( $order_id );
					$user_data                          = WordPress::get_user_context( $user_id );
					$order_data['total_items_in_order'] = count( $product_ids );
					$product_data                       = [];
					foreach ( $product_ids as $key => $product_id ) {
						$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
						$terms                            = get_the_terms( $product_id, 'product_cat' );
						if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
							$cat_name = [];
							foreach ( $terms as $cat ) {
								$cat_name[] = $cat->name;
							}
							$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
						}
						$terms_tags = get_the_terms( $product_id, 'product_tag' );
						if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
							$tag_name = [];
							foreach ( $terms_tags as $tag ) {
								$tag_name[] = $tag->name;
							}
							$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
						}
						$product = wc_get_product( $product_id );
						/**
						 *
						 * Ignore line
						 *
						 * @phpstan-ignore-next-line
						 */
						if ( $product->is_downloadable() ) {
							/**
							 *
							 * Ignore line
							 *
							 * @phpstan-ignore-next-line
							 */
							foreach ( $product->get_downloads() as $key_download_id => $download ) {
								$download_name                                = $download->get_name();
								$download_link                                = $download->get_file();
								$download_id                                  = $download->get_id();
								$download_type                                = $download->get_file_type();
								$download_ext                                 = $download->get_file_extension();
								$product_data[ 'product' . $key ]['download'] = [
									'download_name' => $download_name,
									'download_link' => $download_link,
									'download_id'   => $download_id,
									'download_type' => $download_type,
									'download_ext'  => $download_ext,
								];
							}
						}                       
					}
					$context['response_type'] = 'live';
				}
			}

			$context['pluggable_data'] = array_merge( $order_data, $product_data, $user_data );

		} elseif ( 'variable_product_purchased' === $term ) {
			$product_variation_id = (int) ( isset( $data['filter']['product_variation_id']['value'] ) ? $data['filter']['product_variation_id']['value'] : -1 );
			$order_ids            = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( $order ) {
					$user_id            = $order->get_customer_id();
					$items              = $order->get_items();
					$product_variations = [];

					$iteration = 0;
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_variation_id' ) ) {
							$variation_id = $item->get_variation_id();  
							if ( -1 === $product_variation_id && 0 === $iteration ) {
								$product_variations[] = $variation_id;
								break;
							} elseif ( $variation_id === $product_variation_id ) {
								$product_variations[] = $variation_id;
								break;
							}
						}

						$iteration++;
					}

					if ( count( $product_variations ) > 0 ) {
						$product_data   = WooCommerce::get_product_context( $product_variation_id );
						$order_data     = WooCommerce::get_order_context( $order_id );
						$user_data      = WordPress::get_user_context( $user_id );
						$variation_data = [
							'product_variation_id' => $product_variations[0],
							'product_variation'    => get_the_excerpt( $product_variations[0] ),
						];

						$context['response_type'] = 'live';
						break;
					}
				}
			}

			$context['pluggable_data'] = array_merge( $order_data, $user_data, $variation_data );

		} elseif ( 'variable_subscription_purchased' === $term ) {
			$product_data['quantity']       = '1';
			$product_data['product_name']   = 'Sample Product';
			$product_data['billing_period'] = '2021-2022';

			$context['pluggable_data'] = array_merge( $order_data, $product_data, $user_data );

			$subscription_order_id = 0;
			$order_ids             = [];

			if ( -1 !== $product_id ) {
				$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			} else {
				$orders = wc_get_orders( [] );
				if ( count( $orders ) > 0 ) {
					$order_ids[] = $orders[0]->get_id();
				}
			}

			foreach ( $order_ids as $order_id ) {
				$query_args          = [
					'post_type'      => 'shop_subscription',
					'orderby'        => 'ID',
					'order'          => 'DESC',
					'post_status'    => 'wc-active',
					'posts_per_page' => 1,
					'post_parent'    => $order_id,
				];
				$query_result        = new WP_Query( $query_args );
				$subscription_orders = $query_result->get_posts();

				if ( count( $subscription_orders ) > 0 ) {
					$subscription_order_id = $subscription_orders[0]->ID;
					break;
				}
			}

			if ( 0 !== $subscription_order_id ) {
				$subscription = wcs_get_subscription( $subscription_order_id );
				if ( $subscription instanceof WC_Subscription ) {
					$last_order_id = $subscription->get_last_order();
					if ( ! empty( $last_order_id ) && $last_order_id === $subscription->get_parent_id() ) {
						$user_id = wc_get_order( $last_order_id )->get_customer_id();
						$items   = $subscription->get_items();

						foreach ( $items as $item ) {
							$product = $item->get_product();
							if ( class_exists( '\WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
								if ( $product->is_type( [ 'subscription', 'subscription_variation', 'variable-subscription' ] ) ) {

									$product_data = WooCommerce::get_variable_subscription_product_context( $item, $last_order_id );
									$user_data    = WordPress::get_user_context( $user_id );

									$context['response_type']  = 'live';
									$context['pluggable_data'] = array_merge( $product_data, $user_data );
								}
							}
						}
					}
				}
			}
		} elseif ( 'order_created' === $term ) {
			$orders   = wc_get_orders( [ 'numberposts' => 1 ] );
			$order_id = '';
			if ( count( $orders ) > 0 ) {
				$order_id                 = $orders[0]->get_id();
				$order                    = wc_get_order( $order_id );
				$user_id                  = $order->get_customer_id();
				$order_sample_data        = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $order_sample_data;

		} elseif ( 'order_status_changed' === $term ) {
			if ( -1 == $order_status ) {
				$args = [
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
				];
			} else {
				$args = [
					'status'      => [ $order_status ],
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
				];
			}
			$orders   = wc_get_orders( $args );
			$order_id = '';
			if ( count( $orders ) > 0 ) {
				$order_id    = $orders[0]->get_id();
				$order       = wc_get_order( $order_id );
				$user_id     = $order->get_customer_id();
				$items       = $order->get_items();
				$product_ids = [];
				foreach ( $items as $item ) {
					$product_ids[] = $item['product_id'];
				}
				$product_data = [];
				foreach ( $product_ids as $key => $product_id ) {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$terms = get_the_terms( $product_id, 'product_cat' );
					if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
						$cat_name = [];
						foreach ( $terms as $cat ) {
							$cat_name[] = $cat->name;
						}
						$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
					}
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$terms_tags = get_the_terms( $product_id, 'product_tag' );
					if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
						$tag_name = [];
						foreach ( $terms_tags as $tag ) {
							$tag_name[] = $tag->name;
						}
						$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
					}
				}
				$order_sample_data                = array_merge(
					WooCommerce::get_order_context( $order_id ),
					$product_data
				);
				$order_sample_data['user']        = WordPress::get_user_context( $user_id );
				$order_sample_data['to_status']   = $order_status;
				$order_sample_data['from_status'] = $from_order_status;
				$context['response_type']         = 'live';
			}

			$order_sample_data['to_status']   = $order_status;
			$order_sample_data['from_status'] = $from_order_status;
			$context['pluggable_data']        = $order_sample_data;
		} elseif ( 'order_note_added' === $term ) {
			global $wpdb;
			$result   = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}comments WHERE comment_type ='order_note' ORDER BY comment_ID DESC LIMIT 1" );
			$order_id = '';
			if ( ! empty( $result ) ) {
				$order_id = $result[0]->comment_post_ID;
				$order    = wc_get_order( $order_id );
				if ( ! empty( $order ) ) {
					$user_id           = $order->get_customer_id();
					$order_sample_data = array_merge(
						WooCommerce::get_order_context( $order_id ),
						WordPress::get_user_context( $user_id )
					);
					if ( -1 == $order_note_type ) {
						$args = [
							'order_id' => $order_id,
							'limit'    => 1,
						];
					} else {
						$args = [
							'order_id' => $order_id,
							'type'     => $order_note_type,
							'limit'    => 1,
						];
					}

					$notes = wc_get_order_notes( $args );
					if ( ! empty( $notes ) ) {
						$order_sample_data['note'] = [
							'id'      => $notes[0]->id,
							'date'    => $notes[0]->date_created,
							'author'  => $notes[0]->added_by,
							'content' => $notes[0]->content,
						];
					} else {
						$order_sample_data['note'] = [
							'id'      => '1',
							'date'    => [
								'date'          => '2023-06-23 10:10:40',
								'timezone_type' => 1,
								'timezone'      => '+00:00',
							],
							'author'  => 'admin',
							'content' => 'new note',
						];
					}
					$order_sample_data['note_type'] = $order_note_type;
					$context['response_type']       = 'live';
				}
			} else {
				$order_sample_data['note']      = [
					'id'      => '1',
					'date'    => [
						'date'          => '2023-06-23 10:10:40',
						'timezone_type' => 1,
						'timezone'      => '+00:00',
					],
					'author'  => 'admin',
					'content' => 'new note',
				];
				$order_sample_data['note_type'] = 'customer';
			}
			$context['pluggable_data'] = $order_sample_data;
		} elseif ( 'order_paid' === $term ) {
			$args     = [
				'status'      => [ 'completed' ],
				'numberposts' => 1,
			];
			$orders   = wc_get_orders( $args );
			$order_id = '';
			if ( count( $orders ) > 0 ) {
				$order_id                 = $orders[0]->get_id();
				$order                    = wc_get_order( $order_id );
				$user_id                  = $order->get_customer_id();
				$order_sample_data        = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = $order_sample_data;
		} elseif ( 'product_category_purchased' === $term ) {
			$product_category_id = (int) ( isset( $data['filter']['product_category_id']['value'] ) ? $data['filter']['product_category_id']['value'] : -1 );
			$args                = [
				'post_status'         => 'publish',
				'product_category_id' => [ $product_category_id ],
				'return'              => 'ids',
			];
			$products            = wc_get_products( $args );
			if ( ! empty( $products ) ) {
				$order_id                 = 0;
				$product_data['quantity'] = '1';
				$orders                   = wc_get_orders(
					[
						'status' => 'any',
					]
				);
				$filtered_orders          = [];
				if ( ! empty( $orders ) ) {
					foreach ( $orders as $order ) {
						$order_items = $order->get_items();
						foreach ( $order_items as $item ) {
							if ( method_exists( $item, 'get_product_id' ) ) {
								$product_id = $item->get_product_id();
							}
							if ( is_array( $products ) && in_array( $product_id, $products ) ) {
								$filtered_orders[] = $order;
								break;
							}
						}
					}
				}
				if ( ! empty( $filtered_orders ) ) {
					if ( count( $filtered_orders ) > 0 ) {
						$order_id = $filtered_orders[0]->get_id();
					}
					if ( 0 !== $order_id ) {
						$order = wc_get_order( $order_id );
						if ( $order instanceof WC_Order ) {
							$user_id     = $order->get_customer_id();
							$items       = $order->get_items();
							$product_ids = [];
							$iteration   = 0;
							foreach ( $items as $item ) {
								if ( method_exists( $item, 'get_product_id' ) ) {
									$item_id = $item->get_product_id();
									if ( -1 === $product_id && 0 === $iteration ) {
										$product_ids[] = $item_id;
										break;
									} elseif ( $item_id === $product_id ) {
										$product_ids[] = $item_id;
										break;
									}
								}
								$iteration++;
							}
							$order_data                         = WooCommerce::get_order_context( $order_id );
							$user_data                          = WordPress::get_user_context( $user_id );
							$order_data['total_items_in_order'] = count( $product_ids );
							$product_data                       = [];
							$category_ids                       = [];
							foreach ( $product_ids as $key => $product_id ) {
								$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
								$terms                            = get_the_terms( $product_id, 'product_cat' );
								if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
									$cat_name = [];
									foreach ( $terms as $cat ) {
										$cat_name[]     = $cat->name;
										$category_ids[] = $cat->term_id;
									}
									$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
								}
								$terms_tags = get_the_terms( $product_id, 'product_tag' );
								if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
									$tag_name = [];
									foreach ( $terms_tags as $tag ) {
										$tag_name[] = $tag->name;
									}
									$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
								}
								$product = wc_get_product( $product_id );
								/**
								 *
								 * Ignore line
								 *
								 * @phpstan-ignore-next-line
								 */
								if ( $product->is_downloadable() ) {
									/**
									 *
									 * Ignore line
									 *
									 * @phpstan-ignore-next-line
									 */
									foreach ( $product->get_downloads() as $key_download_id => $download ) {
										$download_name                                = $download->get_name();
										$download_link                                = $download->get_file();
										$download_id                                  = $download->get_id();
										$download_type                                = $download->get_file_type();
										$download_ext                                 = $download->get_file_extension();
										$product_data[ 'product' . $key ]['download'] = [
											'download_name' => $download_name,
											'download_link' => $download_link,
											'download_id'  => $download_id,
											'download_type' => $download_type,
											'download_ext' => $download_ext,
										];
									}
								}
							}
							$context['response_type'] = 'live';
						}
					}
				}
			}
			$context['pluggable_data'] = array_merge( $order_data, $product_data, $user_data );
			if ( ! empty( $category_ids ) ) {
				foreach ( $category_ids as $category_id ) {
					$context['pluggable_data']['product_category_id'] = $category_id;
				}
			} else {
				$context['pluggable_data']['product_category_id'] = 1;
			}
		}

		return $context;
	}

	/**
	 * Search LMS data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_lifter_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$meta_key  = '_is_complete';
		$trigger   = $data['search_term'];
		$context   = [];
		$post_id   = -1;

		if ( ! class_exists( 'LLMS_Section' ) ) {
			return [];
		}

		if ( 'lifterlms_purchase_course' === $trigger ) {
			$product_type = 'course';
			$post_id      = $data['filter']['course_id']['value'];
		} elseif ( 'lifterlms_purchase_membership' === $trigger ) {
			$product_type = 'membership';
			$post_id      = $data['filter']['membership_id']['value'];
		} elseif ( 'lifterlms_cancel_membership' === $trigger ) {
			$product_type = 'membership';
			$post_id      = $data['filter']['membership_id']['value'];
		} elseif ( 'lifterlms_lesson_completed' === $trigger ) {
			$post_id = $data['filter']['lesson']['value'];
		} elseif ( 'lifterlms_course_completed' === $trigger || 'lifterlms_course_enrolled' === $trigger || 'lifterlms_course_user_removed' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
		} elseif ( 'lifterlms_section_completed' === $trigger ) {
			$post_id = $data['filter']['section']['value'];
		}

		$where = 'postmeta.post_id = "' . $post_id . '" AND';

		if ( 'llms_order' === $post_type ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='llms_order' AND posts.post_status= 'llms-completed' AND postmeta.meta_value=%s AND postmeta.meta_key= '_llms_product_type'", $product_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='llms_order' AND posts.post_status= 'llms-completed' AND postmeta.meta_value=%s AND postmeta.meta_key= '_llms_product_id'", $post_id ) );
			}
		} elseif ( 'lifterlms_course_enrolled' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.meta_value='enrolled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", '_status', $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.post_id = %s AND postmeta.meta_value='enrolled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", $post_id, '_status', $post_type ) );
			}
		} elseif ( 'lifterlms_course_user_removed' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.meta_value='cancelled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", '_status', $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.post_id = %s AND postmeta.meta_value='cancelled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", $post_id, '_status', $post_type ) );
			}
		} elseif ( 'lifterlms_cancel_membership' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.meta_value='cancelled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", '_status', $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.post_id = %s AND postmeta.meta_value='cancelled' AND postmeta.meta_key=%s AND posts.post_type=%s ORDER BY postmeta.meta_id DESC", $post_id, '_status', $post_type ) );
			}
		} else {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.meta_value='yes' AND postmeta.meta_key=%s AND posts.post_type=%s", $meta_key, $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.post_id = %s AND postmeta.meta_value='yes' AND postmeta.meta_key=%s AND posts.post_type=%s", $post_id, $meta_key, $post_type ) );
			}
		}

		$response = [];
		if ( ! empty( $result ) ) {
			$result_post_id = $result[0]->post_id;
			$result_user_id = $result[0]->user_id;

			switch ( $trigger ) {
				case 'lifterlms_lesson_completed':
					$context = array_merge(
						WordPress::get_user_context( $result_user_id ),
						LifterLMS::get_lms_lesson_context( $result_post_id )
					);

					$context['course'] = get_the_title( get_post_meta( $result_post_id, '_llms_parent_course', true ) );
					if ( '' !== ( get_post_meta( $result_post_id, '_llms_parent_section', true ) ) ) {
						$context['parent_section'] = get_the_title( get_post_meta( $result_post_id, '_llms_parent_section', true ) );
					}
					break;
				case 'lifterlms_course_enrolled':
				case 'lifterlms_course_user_removed':
				case 'lifterlms_course_completed':
					$context = array_merge(
						WordPress::get_user_context( $result_user_id ),
						LifterLMS::get_lms_course_context( $result_post_id )
					);
					break;
				case 'lifterlms_section_completed':
					$data                           = new \LLMS_Section( $result_post_id );
					$lessons                        = $data->get_lessons();
					$context                        = array_merge(
						WordPress::get_user_context( $result_user_id ),
						WordPress::get_post_context( $result_post_id )
					);
					$context['parent_course']       = $data->get( 'parent_course' );
					$context['parent_course_title'] = get_the_title( $data->get( 'parent_course' ) );
					if ( ! empty( $lessons ) ) {
						foreach ( $lessons as $key => $lesson ) {
							$context['section_lesson'][ $key ]       = $lesson->id;
							$context['section_lesson_title'][ $key ] = get_the_title( $lesson->id );
						}
					}
					$context['section_course']      = $data->get( 'parent_course' );
					$context['parent_course_title'] = get_the_title( $data->get( 'parent_course' ) );
					break;
				case 'lifterlms_purchase_course':
					$user_id                      = get_post_meta( $result_post_id, '_llms_user_id', true );
					$context['course_id']         = get_post_meta( $result_post_id, '_llms_product_id', true );
					$context['course_name']       = get_post_meta( $result_post_id, '_llms_product_title', true );
					$context['course_amount']     = get_post_meta( $result_post_id, '_llms_original_total', true );
					$context['currency']          = get_post_meta( $result_post_id, '_llms_currency', true );
					$context ['order']            = WordPress::get_post_context( $result_post_id );
					$context['order_type']        = get_post_meta( $result_post_id, '_llms_order_type', true );
					$context['trial_offer']       = get_post_meta( $result_post_id, '_llms_trial_offer', true );
					$context['billing_frequency'] = get_post_meta( $result_post_id, '_llms_billing_frequency', true );
					$context                      = array_merge( $context, WordPress::get_user_context( $user_id ) );
					break;
				case 'lifterlms_purchase_membership':
					$user_id                      = get_post_meta( $result_post_id, '_llms_user_id', true );
					$context['membership_id']     = get_post_meta( $result_post_id, '_llms_product_id', true );
					$context['membership_name']   = get_post_meta( $result_post_id, '_llms_product_title', true );
					$context['membership_amount'] = get_post_meta( $result_post_id, '_llms_original_total', true );
					$context['currency']          = get_post_meta( $result_post_id, '_llms_currency', true );
					$context ['order']            = WordPress::get_post_context( $result_post_id );
					$context['order_type']        = get_post_meta( $result_post_id, '_llms_order_type', true );
					$context['trial_offer']       = get_post_meta( $result_post_id, '_llms_trial_offer', true );
					$context['billing_frequency'] = get_post_meta( $result_post_id, '_llms_billing_frequency', true );
					$context                      = array_merge( $context, WordPress::get_user_context( $user_id ) );
					break;
				case 'lifterlms_cancel_membership':
					$context                    = array_merge( WordPress::get_post_context( $result_post_id ), WordPress::get_user_context( $result[0]->user_id ) );
					$context['membership_id']   = $result_post_id;
					$context['membership_name'] = get_the_title( $result_post_id );
					break;
				default:
					return;

			}
			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';

		}

		return $response;

	}

	/**
	 * Search SM data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_suremember_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$meta_key  = '_is_complete';
		$trigger   = $data['search_term'];
		$post_id   = $data['filter']['group_id']['value'];

		if ( 'suremember_updated_group' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts WHERE posts.post_type=%s", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts WHERE posts.ID=%s AND posts.post_type=%s", $post_id, $post_type ) );
			}
		} else {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}usermeta as usermeta WHERE usermeta.meta_key = %s", 'suremembers_user_access_group_' . $post_id ) );
		}

		$response = [];

		if ( ! empty( $result ) ) {
			$context = [];
			switch ( $trigger ) {
				case 'suremember_updated_group':
					$group_id                                   = $result[0]->ID;
					$suremembers_post['rules']                  = get_post_meta( $group_id, 'suremembers_plan_include', true );
					$suremembers_post['exclude']                = get_post_meta( $group_id, 'suremembers_plan_exclude', true ); //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					$suremembers_post['suremembers_user_roles'] = get_post_meta( $group_id, 'suremembers_user_roles', true );
					$suremembers_post['title']                  = get_the_title( $group_id );
					$suremembers_post['restrict']               = get_post_meta( $group_id, 'suremembers_plan_rules', true )['restrict'];
					$context['group']                           = array_merge( WordPress::get_post_context( $group_id ), $suremembers_post );
					$context['group_id']                        = $group_id;
					unset( $context['group']['ID'] );
					$response['pluggable_data'] = $context;
					$response['response_type']  = 'live';
					break;
				case 'suremember_user_added_in_group':
					foreach ( $result as $res ) {
						$meta_value = unserialize( $res->meta_value );
						if ( 'active' === $meta_value['status'] ) {
							$context             = WordPress::get_user_context( $res->user_id );
							$context['group']    = WordPress::get_post_context( $post_id );
							$context['group_id'] = $post_id;
							unset( $context['group']['ID'] );
							$response['pluggable_data'] = $context;
							$response['response_type']  = 'live';
						}
					}
					break;
				case 'suremember_user_removed_from_group':
					foreach ( $result as $res ) {
						$meta_value = unserialize( $res->meta_value );
						if ( 'revoked' === $meta_value['status'] ) {
							$context             = WordPress::get_user_context( $res->user_id );
							$context['group']    = WordPress::get_post_context( $post_id );
							$context['group_id'] = $post_id;
							unset( $context['group']['ID'] );
							$response['pluggable_data'] = $context;
							$response['response_type']  = 'live';
						}
					}
					break;
				default:
					return;

			}
		}

		return $response;

	}

	/**
	 * Search CartFlows data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_cartflows_last_data( $data ) {
		global $wpdb;
		$trigger = $data['search_term'];
		$context = [];
		if ( 'cartflows_offer_accepted' === $trigger ) {
			$result = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}posts as posts  JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='shop_order' AND postmeta.meta_value='upsell' AND postmeta.meta_key= '_cartflows_offer_type'" );
		}
		$response = [];
		if ( ! empty( $result ) ) {
			$context                    = [];
			$order_upsell_id            = $result[0]->post_id;
			$step_id                    = get_post_meta( $order_upsell_id, '_cartflows_offer_step_id', true );
			$order_id                   = get_post_meta( $order_upsell_id, '_cartflows_offer_parent_id', true );
			$order                      = wc_get_order( $order_id );
			$upsell_order               = wc_get_order( $order_upsell_id );
			$variation_id               = $upsell_order->get_items()[0]['product_id'];
			$input_qty                  = $upsell_order->get_items()[0]['quantity'];
			$offer_product              = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );
			$user_id                    = get_post_meta( $order_upsell_id, '_customer_user', true );
			$context                    = WordPress::get_user_context( $user_id );
			$context['order']           = $order->get_data();
			$context['upsell']          = $offer_product;
			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';
		}

		return $response;

	}


	/**
	 * Fetch user context.
	 *
	 * @param int $initiator_id initiator id.
	 * @param int $friend_id friend id.
	 * @return array
	 */
	public function get_user_context( $initiator_id, $friend_id ) {
		$context = WordPress::get_user_context( $initiator_id );

		$friend_context = WordPress::get_user_context( $friend_id );

		$avatar = get_avatar_url( $initiator_id );

		$context['avatar_url'] = ( $avatar ) ? $avatar : '';

		$context['friend_id']         = $friend_id;
		$context['friend_first_name'] = $friend_context['user_firstname'];
		$context['friend_last_name']  = $friend_context['user_lastname'];
		$context['friend_email']      = $friend_context['user_email'];

		$friend_avatar                = get_avatar_url( $friend_id );
		$context['friend_avatar_url'] = $friend_avatar;
		return $context;
	}

	/**
	 * Search BP data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_friendships( $data ) {
		global $wpdb, $bp;
		$context                  = [];
		$sample['pluggable_data'] = [
			'wp_user_id'        => 4,
			'user_login'        => 'katy1ßßßß',
			'display_name'      => 'Katy Smith',
			'user_firstname'    => 'Katy',
			'user_lastname'     => 'Smith',
			'user_email'        => 'katy1@gmail.com',
			'user_role'         => [ 'subscriber' ],
			'avatar_url'        => 'http://pqr.com/avatar',
			'friend_id'         => 1,
			'friend_first_name' => 'John',
			'friend_last_name'  => 'Wick',
			'friend_email'      => 'john@gmail.com',
			'friend_avatar_url' => 'http://abc.com/avatar',
		];
		$sample['response_type']  = 'sample';

		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}bp_friends'" );
		if ( $table_exists ) {
			$friendships = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_friends LIMIT 1" );
			if ( ! empty( $friendships ) ) {
				$friendship                = $friendships[0];
				$initiator_id              = $friendship->initiator_user_id;
				$friend_user_id            = $friendship->friend_user_id;
				$context['pluggable_data'] = $this->get_user_context( $initiator_id, $friend_user_id );
				$context['response_type']  = 'live';
			} else {
				$context = $sample;
			}
		} else {
			$context = $sample;
		}
		
		
		return $context;
	}
	
	/**
	 * Search Buddyboss profile types data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_profile_types( $data ) {
		global $wpdb, $bp;
		$context                  = [];
		$sample['pluggable_data'] = [
			'wp_user_id'           => 4,
			'user_login'           => 'katy1ßßßß',
			'display_name'         => 'Katy Smith',
			'user_firstname'       => 'Katy',
			'user_lastname'        => 'Smith',
			'user_email'           => 'katy1@gmail.com',
			'user_role'            => [ 'subscriber' ],
			'bb_profile_type'      => '10',
			'bb_profile_type_name' => 'student',
		];
		$sample['response_type']  = 'sample';

		$post_id      = $data['filter']['bb_profile_type']['value'];
		$get_existing = get_post_meta( $post_id, '_bp_member_type_key', true );

		$type_term = get_term_by(
			'name',
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$get_existing,
			'bp_member_type'
		);

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID FROM {$wpdb->prefix}users u INNER JOIN {$wpdb->prefix}term_relationships r 
				ON u.ID = r.object_id WHERE u.user_status = 0 AND 
				r.term_taxonomy_id = %d ORDER BY RAND() LIMIT 1",
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$type_term->term_id 
			)
		);

		if ( ! empty( $results ) ) {
			$user                      = $results[0];
			$context['pluggable_data'] = WordPress::get_user_context( $user->ID );
			$context['pluggable_data']['bb_profile_type']      = $post_id;
			$context['pluggable_data']['bb_profile_type_name'] = get_post_meta( $post_id, '_bp_member_type_label_singular_name', true );
			$context['response_type']                          = 'live';
		} else {
			$context = $sample;
		}
		
		return $context;
	}

	/**
	 * Search BP User data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bb_users( $data ) {
		global $wpdb;
		$context = [];

		if ( ! class_exists( 'BP_Signup' ) ) {
			return [];
		}

		$term = $data['search_term'];

		if ( 'account_activated' === $term ) {
			$signups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}signups WHERE active = 1 ORDER BY signup_id DESC LIMIT 1" );
		} elseif ( 'updates_profile' === $term ) {
			$custom_ids = $wpdb->get_var( "SELECT user_id FROM {$wpdb->prefix}bp_xprofile_data ORDER BY last_updated DESC LIMIT 1" );
			$args       = [ 'include' => $custom_ids ];
			$users      = get_users( $args );
		} elseif ( 'gains_follower' === $term ) {
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_follow ORDER BY id DESC LIMIT 1" );
		}
		
		if ( ! empty( $signups ) ) {
			$pluggable_data = $signups[0];
			$pluggable_data = get_object_vars( $pluggable_data );
			unset( $pluggable_data['activation_key'] );
			if ( is_string( $pluggable_data['meta'] ) ) {
				$pluggable_data['meta'] = unserialize( $pluggable_data['meta'] );
			}
			if ( is_array( $pluggable_data['meta'] ) ) {
				unset( $pluggable_data['meta']['password'] );
			}
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} elseif ( 'updates_profile' === $term ) {
			if ( ! empty( $users ) ) {
				$user                         = $users[0];
				$fields                       = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, value FROM {$wpdb->prefix}bp_xprofile_data WHERE user_id = %d", $user->ID ) );
				$user_data                    = WordPress::get_user_context( $user->ID );
				$pluggable_data['user_id']    = $user->ID;
				$pluggable_data['user_email'] = $user_data['user_email'];
				foreach ( $user_data['user_role'] as $key => $role ) {
					$pluggable_data['user_role'][ $key ] = $role;
				}
				foreach ( $fields as $field ) {
					if ( function_exists( 'xprofile_get_field' ) ) {
						$fieldj                          = xprofile_get_field( $field->field_id );
						$pluggable_data[ $fieldj->name ] = $field->value;
					}
				}
				$context['pluggable_data'] = $pluggable_data;
				$context['response_type']  = 'live';
			} else {
				$context['pluggable_data'] = [
					'user_login'     => 'john',
					'display_name'   => 'john',
					'user_firstname' => 'john',
					'user_lastname'  => 'd',
					'user_email'     => 'johnd@yopmail.com',
					'user_role'      => [
						'subscriber',
					],
					'Name'           => 'john',
					'Nickname'       => 'johnd',
					'wp_user_id'     => 16,
				];
				$context['response_type']  = 'sample';
			}
		} elseif ( 'gains_follower' === $term ) {
			if ( ! empty( $results ) ) {
				$pluggable_data['follower'] = WordPress::get_user_context( $results[0]->follower_id );
				$pluggable_data['leader']   = WordPress::get_user_context( $results[0]->leader_id );
				$context['pluggable_data']  = $pluggable_data;
				$context['response_type']   = 'live';
			} else {
				$context['pluggable_data'] = [
					'follower' => [
						'wp_user_id'     => 126,
						'user_login'     => 'belli',
						'display_name'   => 'belli',
						'user_firstname' => 'test',
						'user_lastname'  => 'test',
						'user_email'     => 'belli@gmail.com',
						'user_role'      => [
							'subscriber',
							'wpamelia-customer',
						],
					],
					'leader'   => [
						'wp_user_id'     => 34,
						'user_login'     => 'bella3@gmail.com',
						'display_name'   => 'bella3@gmail.com',
						'user_firstname' => 'bella3',
						'user_lastname'  => 'bella3',
						'user_email'     => 'bellaaa3@gmail.com',
						'user_role'      => [
							'wpamelia-customer',
						],
					],
				];
				$context['response_type']  = 'sample';
			}
		} else {
			$context['pluggable_data'] = [
				'signup_id'     => '16',
				'domain'        => '',
				'path'          => '',
				'title'         => '',
				'user_login'    => 'johnd',
				'user_email'    => 'johnd@yopmail.com',
				'registered'    => '2024-01-29 04:52:13',
				'activated'     => '0000-00-00 00:00:00',
				'active'        => '0',
				'meta'          => [
					'field_1'           => 'john',
					'field_3'           => 'd',
					'field_4'           => '09878988766',
					'field_2'           => '123 Main Street',
					'field_5'           => 'johnd',
					'profile_field_ids' => '1,3,4,2,5',
				],
				'id'            => 16,
				'user_name'     => 'johnd',
				'date_sent'     => '2024-01-29 04:52:13',
				'recently_sent' => true,
				'count_sent'    => 1,
			];
			$context['response_type']  = 'sample';
		}

		return $context;
	}

	/**
	 * Search BP data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_groups( $data ) {
		global $wpdb, $bp;
		$context    = [];
		$group_data = [];
		$args       = [
			'orderby' => 'user_nicename',
			'order'   => 'ASC',
			'number'  => 1,
		];

		$term = $data['search_term'];

		if ( ! function_exists( 'groups_get_group' ) || ! function_exists( 'bp_groups_get_group_types' ) || ! function_exists( 'bp_groups_get_group_type' ) || ! function_exists( 'bp_get_group_cover_url' ) || ! function_exists( 'bp_get_group_avatar_url' ) || ! function_exists( 'groups_get_group_members' ) || ! function_exists( 'groups_get_invites' ) ) {
			return [];
		}

		if ( 'user_joins_specific_type_group' == $term ) {
			if ( -1 !== $data['filter']['group_type']['value'] ) {
				$group_type = $data['filter']['group_type']['value'];
			} else {
				$registered_types = bp_groups_get_group_types();
				$random_key       = array_rand( $registered_types );
				$random_value     = $registered_types[ $random_key ];
				$group_type       = $random_value;
			}
			if ( function_exists( 'bp_get_group_ids_by_group_types' ) ) {
				$group_ids    = bp_get_group_ids_by_group_types( $group_type );
				$random_key   = array_rand( $group_ids );
				$random_value = $group_ids[ $random_key ];
				$group_id     = $random_value;
				if ( function_exists( 'groups_get_group' ) ) {
					$group                           = groups_get_group( $group_id['id'] );
					$group_data['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
					$group_data['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
					$group_data['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';
					$group_data['group_type']        = $group_type;
					if ( function_exists( 'groups_get_group_members' ) ) {
						$members = groups_get_group_members(
							[
								'group_id' => $group_id,
							]
						);
						$ids     = [];
						foreach ( $members['members'] as $member ) {
							$ids[] = $member->ID;
						}
						$args  = [
							'number'  => 1,
							'include' => $ids,
						];
						$users = get_users( $args );
					}
				}
			}
		} elseif ( 'requests_access_private_group' === $data['search_term'] ) {
			$group_id = $data['filter']['group_id']['value'];
			if ( $group_id > 0 ) {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, item_id FROM {$wpdb->prefix}bp_invitations WHERE type LIKE %s AND item_id = %d ORDER BY id DESC LIMIT 1", 'request', $group_id ) );
			} else {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT user_id, item_id FROM {$wpdb->prefix}bp_invitations WHERE type = %s ORDER BY id DESC LIMIT 1", 'request' ) );
			}
			$custom_ids                      = $results[0]->user_id;
			$args                            = [ 'include' => $custom_ids ];
			$users                           = get_users( $args );
			$group_id                        = $results[0]->item_id;
			$group                           = groups_get_group( $group_id );
			$group_data['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
			$group_data['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
			$group_data['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';
		} elseif ( 'bb_group_created' == $term ) {
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups ORDER BY id DESC LIMIT 1" );
			if ( function_exists( 'groups_get_group' ) ) {
				$group                             = groups_get_group( $results[0]->id );
				$group_data['group_id']            = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
				$group_data['group_name']          = ( property_exists( $group, 'name' ) ) ? $group->name : '';
				$group_data['group_description']   = ( property_exists( $group, 'description' ) ) ? $group->description : '';
				$current_types                     = (array) bp_groups_get_group_type( $results[0]->id, false );
				$group_data['group_type']          = $current_types;
				$group_data['group_status']        = $group->status;
				$group_data['group_date_created']  = $group->date_created;
				$group_data['group_enabled_forum'] = $group->enable_forum;
				$group_data['group_cover_url']     = bp_get_group_cover_url( $group );
				$group_data['group_avatar_url']    = bp_get_group_avatar_url( $group );
				$group_data['group_creator']       = WordPress::get_user_context( $group->creator_id );
				if ( function_exists( 'groups_get_group_members' ) ) {
					$members = groups_get_group_members(
						[
							'group_id' => $results[0]->id,
						]
					);
					foreach ( $members['members'] as $key => $member ) {
						$group_data['group_member'][ $key ] = WordPress::get_user_context( $member->ID );
					}
				}
				$args        = [
					'item_id' => $results[0]->id,
				];
				$invitations = groups_get_invites( $args );
				if ( ! empty( $invitations ) ) {
					foreach ( $invitations as $key => $invite ) {
						$group_data['invitation'][ $key ] = $invite;
					}
				}
			}
		} else {
			$users = get_users( $args );

			if ( isset( $data['filter']['group_id']['value'] ) ) {
				$group_id         = $data['filter']['group_id']['value'];
				$args['group_id'] = $group_id;
				if ( $group_id > 0 ) {
					$group                           = groups_get_group( $group_id );
					$group_data['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
					$group_data['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
					$group_data['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';
				} else {
					$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}bp_groups'" );
					if ( $table_exists ) {
						$groups            = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups LIMIT 1" );
						$context['groups'] = $groups;
						if ( ! empty( $groups ) ) {
							foreach ( $groups as $group ) {
								$group_data['group_id']          = $group->id;
								$group_data['group_name']        = $group->name;
								$group_data['group_description'] = $group->description;
							}
						}
					}
				}
			}
		}

		if ( 'bb_group_created' == $term ) {
			if ( ! empty( $group_data ) ) {
				$pluggable_data            = $group_data;
				$context['pluggable_data'] = $pluggable_data;
				$context['response_type']  = 'live';
			} else {
				$context['pluggable_data'] = [
					'group_id'            => 112,
					'group_name'          => 'New Group',
					'group_description'   => 'New Group Description',
					'group_type'          => [ 'business' ],
					'group_status'        => 'public',
					'group_date_created'  => '2024-02-16 05:37:22',
					'group_enabled_forum' => 1,
					'group_cover_url'     => 'https:\/\/example.com\/wp-content\/uploads\/buddypress\/groups\/23\/cover-image\/65cef4c3ea9b6-bp-cover-image.jpeg',
					'group_avatar_url'    => 'https:\/\/example.com\/wp-content\/uploads\/group-avatars\/23\/65cef4d7cee19-bpfull.png',
					'group_creator'       => [
						'wp_user_id'     => 183,
						'user_login'     => 'johnd',
						'display_name'   => 'john',
						'user_firstname' => 'john',
						'user_lastname'  => 'd',
						'user_email'     => 'johnd@yopmail.com',
						'user_role'      => [
							'subscriber',
							'bbp_participant',
						],
					],
				];
				$context['response_type']  = 'sample';
			}
		} elseif ( ! empty( $users ) ) {
			$user           = $users[0];
			$pluggable_data = $group_data;

			$avatar                           = get_avatar_url( $user->ID );
			$pluggable_data['wp_user_id']     = $user->ID;
			$pluggable_data['avatar_url']     = ( $avatar ) ? $avatar : '';
			$pluggable_data['user_login']     = $user->user_login;
			$pluggable_data['display_name']   = $user->display_name;
			$pluggable_data['user_firstname'] = $user->user_firstname;
			$pluggable_data['user_lastname']  = $user->user_lastname;
			$pluggable_data['user_email']     = $user->user_email;
			$pluggable_data['user_role']      = $user->roles;
			$context['pluggable_data']        = $pluggable_data;
			$context['response_type']         = 'live';
		} else {
			$context['pluggable_data'] = [
				'wp_user_id'        => 1,
				'user_login'        => 'admin',
				'display_name'      => 'Johnd',
				'user_firstname'    => 'John',
				'user_lastname'     => 'D',
				'user_email'        => 'johnd@gmail.com',
				'user_role'         => [ 'subscriber' ],
				'group_id'          => 112,
				'group_name'        => 'New Group',
				'group_description' => 'New Group Description',
			];
			$context['response_type']  = 'sample';
		}

		return $context;
	}

	/**
	 * Search complete courses.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_course( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_course_id']['value'] ) ) {
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		if ( -1 === $course_id ) {
			$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='course' AND activity.activity_status= %d ORDER BY activity.activity_id DESC", 1 ) );
		} else {
			$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='course' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $course_id, $course_id ) );
		}

		if ( ! empty( $courses ) ) {
			$course                                   = $courses[0];
			$course_data['course_name']               = $course->post_title;
			$course_data['sfwd_course_id']            = $course->ID;
			$course_data['course_url']                = get_permalink( $course->ID );
			$course_data['course_featured_image_id']  = get_post_meta( $course->ID, '_thumbnail_id', true );
			$course_data['course_featured_image_url'] = get_the_post_thumbnail_url( $course->ID );
			$timestamp                                = get_user_meta( $course->user_id, 'course_completed_' . $course->ID, true );
			$timestamp                                = is_numeric( $timestamp ) ? (int) $timestamp : null;
			$date_format                              = get_option( 'date_format' );
			if ( is_string( $date_format ) ) {
				$course_data['course_completion_date'] = wp_date( $date_format, $timestamp );
			}
			if ( function_exists( 'learndash_get_course_certificate_link' ) ) {
				$course_data['course_certificate'] = learndash_get_course_certificate_link( $course->ID, $course->user_id );
			}
			$context['response_type'] = 'live';
		} else {
			$course_data['course_name']               = 'Test Course';
			$course_data['sfwd_course_id']            = 112;
			$course_data['course_url']                = 'https://abc.com/test-course';
			$course_data['course_featured_image_id']  = 113;
			$course_data['course_featured_image_url'] = 'https://pqr.com/test-course-img';
			$course_data['course_completion_date']    = '2023-10-20';
			$course_data['course_certificate']        = 'https://example.com/certificates/good-performance/?course_id=112&cert-nonce=f80d0f9cc1';
			$context['response_type']                 = 'sample';
		}

		$users_data = $this->search_pluggables_add_user_role( [] );
		$user_data  = $users_data['pluggable_data'];

		$context['pluggable_data'] = array_merge( $course_data, $user_data );
		return $context;
	}

	/**
	 * Search lessons.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_lesson( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_lesson_id']['value'] ) ) {
			$lesson_id = $data['filter']['sfwd_lesson_id']['value'];
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		if ( -1 === $course_id ) {
			$courses    = get_posts(
				[
					'posts_per_page' => - 1,
					'post_type'      => 'sfwd-courses',
					'post_status'    => 'publish',
					'fields'         => 'ids',
				]
			);
			$course_key = array_rand( $courses );
			$course_id  = $courses[ $course_key ];
		}
		$course         = get_post( $course_id );
		$pluggable_data = LearnDash::get_course_context( $course );

		if ( -1 === $lesson_id ) {
			$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='lesson' AND activity.activity_status= %d AND activity.course_id= %d", 1, $course_id ) );
		} else {
			$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='lesson' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $lesson_id, $course_id ) );
		}

		if ( ! empty( $lessons ) ) {
			$lesson = $lessons[0];

			$pluggable_data                              = WordPress::get_user_context( $lesson->user_id );
			$pluggable_data['lesson_name']               = $lesson->post_title;
			$pluggable_data['sfwd_lesson_id']            = $lesson->ID;
			$pluggable_data['lesson_url']                = get_permalink( $lesson->ID );
			$pluggable_data['lesson_featured_image_id']  = get_post_meta( $lesson->ID, '_thumbnail_id', true );
			$pluggable_data['lesson_featured_image_url'] = get_the_post_thumbnail_url( $lesson->ID );
			$context['response_type']                    = 'live';
		} else {
			$pluggable_data                              = WordPress::get_sample_user_context();
			$pluggable_data['lesson_name']               = 'Test Lesson';
			$pluggable_data['sfwd_lesson_id']            = 114;
			$pluggable_data['lesson_url']                = 'https://abc.com/test-lesson';
			$pluggable_data['lesson_featured_image_id']  = 116;
			$pluggable_data['lesson_featured_image_url'] = 'https://pqr.com/test-lesson-img';
			$context['response_type']                    = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search topics.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_topic( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_topic_id']['value'] ) ) {
			$topic_id  = $data['filter']['sfwd_topic_id']['value'];
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		if ( -1 === $course_id ) {
			$courses    = get_posts(
				[
					'posts_per_page' => - 1,
					'post_type'      => 'sfwd-courses',
					'post_status'    => 'publish',
					'fields'         => 'ids',
				]
			);
			$course_key = array_rand( $courses );
			$course_id  = $courses[ $course_key ];
		}
		$course         = get_post( $course_id );
		$pluggable_data = LearnDash::get_course_context( $course );

		if ( -1 === $topic_id ) {
			$topics = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='topic' AND activity.activity_status= %d AND activity.course_id= %d", 1, $course_id ) );
		} else {
			$topics = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='topic' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $topic_id, $course_id ) );
		}

		if ( ! empty( $topics ) ) {
			$topic                                      = $topics[0];
			$pluggable_data                             = WordPress::get_user_context( $topics[0]->user_id );
			$pluggable_data['topic_name']               = $topic->post_title;
			$pluggable_data['sfwd_topic_id']            = $topic->ID;
			$pluggable_data['topic_url']                = get_permalink( $topic->ID );
			$pluggable_data['topic_featured_image_id']  = get_post_meta( $topic->ID, '_thumbnail_id', true );
			$pluggable_data['topic_featured_image_url'] = get_the_post_thumbnail_url( $topic->ID );
			$context['response_type']                   = 'live';
		} else {
			$pluggable_data                             = WordPress::get_sample_user_context();
			$pluggable_data['topic_name']               = 'Test Topic';
			$pluggable_data['sfwd_topic_id']            = 117;
			$pluggable_data['topic_url']                = 'https://abc.com/test-topic';
			$pluggable_data['topic_featured_image_id']  = 118;
			$pluggable_data['topic_featured_image_url'] = 'https://pqr.com/test-topic-img';
			$context['response_type']                   = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search purchase courses.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_purchase_course( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$purchase_data = [
			'course_product_id'   => '1',
			'course_product_name' => 'Sample Course',
			'currency'            => 'USD',
			'total_amount'        => '100',
			'first_name'          => 'John',
			'last_name'           => 'Doe',
			'email'               => 'john_doe@gmail.com',
			'phone'               => '+923007626541',
		];

		$product_id = (int) ( isset( $data['filter']['course_product_id']['value'] ) ? $data['filter']['course_product_id']['value'] : '-1' );
		$order_id   = 0;

		if ( -1 !== $product_id ) {
			$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			if ( count( $order_ids ) > 0 ) {
				$order_id = $order_ids[0];
			}
		} else {
			$orders = wc_get_orders( [] );
			if ( count( $orders ) > 0 ) {
				foreach ( $orders as $order ) {
					$items = $order->get_items();

					if ( count( $items ) > 1 ) {
						continue;
					}

					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$product_id = $item->get_product_id();
							if ( ! empty( get_post_meta( $item->get_product_id(), '_related_course', true ) ) ) {
								$order_id = $order->get_id();
								break;
							}
						}
					}
				}
			}
		}

		if ( 0 !== $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order ) {

				$purchase_data = LearnDash::get_purchase_course_context( $order );

				$context['response_type'] = 'live';
			}
		}

		$context['pluggable_data'] = $purchase_data;

		return $context;
	}

	/**
	 * Search quiz data in LearnDash.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_ld_quiz( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';
		global $wpdb;
		$user_data                    = WordPress::get_sample_user_context();
		$quiz_data                    = [
			'quiz'                => 7193,
			'score'               => 1,
			'count'               => 1,
			'question_show_count' => 1,
			'pass'                => 1,
			'rank'                => '-',
			'time'                => 1703595328,
			'pro_quizid'          => 1,
			'course'              => 0,
			'lesson'              => 0,
			'topic'               => 0,
			'points'              => 20,
			'total_points'        => 20,
			'percentage'          => 100,
			'timespent'           => 2.7309999999999999,
			'has_graded'          => false,
			'statistic_ref_id'    => 9,
			'started'             => 1703595325,
			'completed'           => 1703595328,
			'ld_version'          => '4.8.0.1',
			'quiz_key'            => '1703595328_1_7193_0',
		];
		$output_questions['question'] = [
			'ID'            => 7195,
			'post_content'  => '<p>This is a first question<\/p>',
			'type'          => 'sfwd-question',
			'question_type' => 'single',
			'points'        => 20,
			'answers'       => [
				'sort_answer'        => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
				'classic_answer'     => [
					[
						'answer'  => 'Ans 1',
						'points'  => 1,
						'correct' => false,
						'type'    => 'answer',
					],
					[
						'answer'  => 'Ans 2',
						'points'  => 0,
						'correct' => true,
						'type'    => 'answer',
					],
				],
				'matrix_sort_answer' => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
				'cloze_answer'       => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
				'free_answer'        => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
				'assessment_answer'  => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
				'essay'              => [
					[
						'answer'  => '',
						'points'  => 0,
						'correct' => false,
						'type'    => 'answer',
					],
				],
			],
		];      

		$quiz_id     = (int) ( isset( $data['filter']['sfwd_quiz_id']['value'] ) ? $data['filter']['sfwd_quiz_id']['value'] : '-1' );
		$question_id = $data['filter']['sfwd_question_id']['value'];
		$term        = $data['search_term'];
		$mark        = 1;
		if ( 'passes_quiz' == $term ) {
			$mark = 1;
		} elseif ( 'fails_quiz' == $term ) {
			$mark = 0;
		}
		if ( 'passes_quiz' == $term || 'fails_quiz' == $term ) {
			if ( -1 == $quiz_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learndash_user_activity as activity  JOIN {$wpdb->prefix}learndash_pro_quiz_statistic_ref as statistic ON activity.activity_completed = statistic.create_time WHERE activity.activity_type='quiz' AND activity.activity_status=%d order by activity_id DESC LIMIT 1", $mark ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learndash_user_activity as activity LEFT JOIN {$wpdb->prefix}learndash_pro_quiz_statistic_ref as statistic ON activity.activity_completed = statistic.create_time WHERE activity.activity_type='quiz' AND activity.activity_status=%d AND activity.post_id = %d order by activity.activity_id DESC LIMIT 1", $mark, $quiz_id ) );
			}
		} elseif ( 'quiz_essay_submitted' == $term ) {
			$question_id = $data['filter']['sfwd_question_id']['value'];
			$args        = [
				'post_type'      => 'sfwd-essays',
				'posts_per_page' => 1,
				'order'          => 'DESC',
				'post_status'    => [ 'graded', 'not_graded' ],
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => 'quiz_post_id',
						'value'   => $quiz_id,
						'compare' => '=',
					],
					[
						'key'     => 'question_post_id',
						'value'   => $question_id,
						'compare' => '=',
					],
				],
			];
			$essay       = get_posts( $args );
		} elseif ( 'quiz_essay_graded' == $term ) {
			$args  = [
				'post_type'      => 'sfwd-essays',
				'posts_per_page' => 1,
				'order'          => 'DESC',
				'post_status'    => 'graded',
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => 'quiz_post_id',
						'value'   => $quiz_id,
						'compare' => '=',
					],
					[
						'key'     => 'question_post_id',
						'value'   => $question_id,
						'compare' => '=',
					],
				],
			];
			$essay = get_posts( $args );
		}

		if ( 'quiz_essay_submitted' == $term || 'quiz_essay_graded' == $term ) {
			if ( ! empty( $essay ) ) {
				$context                     = WordPress::get_user_context( $essay[0]->post_author );
				$course_id                   = get_post_meta( $essay[0]->ID, 'course_id', true );
				$lesson_id                   = get_post_meta( $essay[0]->ID, 'lesson_id', true );
				$context['quiz_name']        = get_the_title( $quiz_id );
				$context['sfwd_quiz_id']     = $quiz_id;
				$context['sfwd_question_id'] = $question_id;
				$context['question_name']    = is_int( $question_id ) ? (int) get_the_title( $question_id ) : null;
				$context['course_name']      = is_int( $course_id ) ? (int) get_the_title( $course_id ) : null;
				$context['course_id']        = $course_id;
				$context['lesson_name']      = is_int( $lesson_id ) ? (int) get_the_title( $lesson_id ) : null;
				$context['lesson_id']        = $lesson_id;
				$context['essay_id']         = $essay[0]->ID;
				$context['essay']            = WordPress::get_post_context( $essay[0]->ID );
				if ( 'quiz_essay_graded' == $term ) {
					$users_quiz_data = get_user_meta( (int) $essay[0]->post_author, '_sfwd-quizzes', true );
					if ( is_array( $users_quiz_data ) && is_array( $users_quiz_data[0] ) ) {
						$essay_post_id                  = $essay[0]->ID;
						$result                         = array_filter(
							$users_quiz_data[0]['graded'],
							function( $item ) use ( $essay_post_id ) {
								return $item['post_id'] === $essay_post_id;
							}
						);
						$context['essay_points_earned'] = $result[1]['points_awarded'];
					}
				}
				$context['response_type'] = 'live';
			} else {
				$context                     = WordPress::get_sample_user_context();
				$context['quiz_name']        = 'Test Quiz';
				$context['sfwd_quiz_id']     = 11;
				$context['sfwd_question_id'] = 12;
				$context['course_name']      = 'Test Course';
				$context['course_id']        = 13;
				$context['lesson_name']      = 'Test Lesson';
				$context['lesson_id']        = 14;
				$context['essay']            = [
					'ID'           => 12,
					'post_content' => 'demo',
				];
				$context['response_type']    = 'sample';
			}

			$context['pluggable_data'] = $context;
		} else {
			if ( ! empty( $result ) ) {
				$user_data   = WordPress::get_user_context( $result[0]->user_id );
				$quiz_result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}usermeta WHERE user_id = %d AND meta_key = '_sfwd-quizzes'", $result[0]->user_id ) );
				
				$data          = unserialize( $quiz_result[0]->meta_value );
				$found_element = null;
				foreach ( (array) $data as $element ) {
					if ( is_array( $element ) ) {
						if ( isset( $element['statistic_ref_id'] ) && $element['statistic_ref_id'] == $result[0]->statistic_ref_id ) {
							$found_element = $element;
							break;
						}
					}
				}
				$quiz_data        = (array) $found_element;
				$output_questions = LearnDash::get_quiz_questions_answers( $quiz_id );
				if ( is_array( $quiz_data ) ) {
					$quiz_data['quiz_name']    = get_the_title( $quiz_id );
					$quiz_data['sfwd_quiz_id'] = $quiz_id;
				}
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $quiz_data, $user_data, $output_questions );
		}

		return $context;
	}

	/**
	 * Fetch BB templates.
	 *
	 * @return array
	 */
	public function get_beaver_builder_templates() {
		$allowed_types = [ 'subscribe-form', 'contact-form' ];
		$templates     = [];
		$all_templates = get_posts(
			[
				'post_type'      => 'fl-builder-template',
				'meta_key'       => '_fl_builder_data',
				'posts_per_page' => -1,
			]
		);
		$posts         = get_posts(
			[
				'post_type'      => 'any',
				'meta_key'       => '_fl_builder_data',
				'posts_per_page' => -1,
			]
		);
		$posts         = array_merge( $all_templates, $posts );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$meta = get_post_meta( $post->ID, '_fl_builder_data', true );
				foreach ( (array) $meta as $node_id => $node ) {
					if ( isset( $node->type ) && 'module' === $node->type ) {
						$settings = $node->settings;
						if ( in_array( $settings->type, $allowed_types, true ) ) {
							$label = $post->post_title;
							if ( '' !== $settings->node_label ) {
								$label .= ' - ' . $settings->node_label;
							}
							$templates[] = [
								'label' => $label,
								'value' => $node_id,
							];
						}
					}
				}
			}
		}
		return $templates;
	}

	/**
	 * Search beaver builder forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_beaver_builder_forms( $data ) {
		$templates = $this->get_beaver_builder_templates();
		return [
			'options' => $templates,
			'hasMore' => false,
		];
	}

	/**
	 * Search FluentCRM fields.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluentcrm_custom_fields( $data ) {
		$context           = [];
		$custom_fields     = ( new CustomContactField() )->getGlobalFields()['fields'];
		$context['fields'] = $custom_fields;
		return $context;
	}

	/**
	 * Search FluentCRM Company fields.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluentcrm_company_custom_fields( $data ) {
		$context = [];
		if ( function_exists( 'fluentcrm_get_custom_company_fields' ) ) {
			$custom_fields     = fluentcrm_get_custom_company_fields();
			$context['fields'] = $custom_fields;
		}
		return $context;
	}

	/**
	 * Search FluentCRM fields and display it in dropdown for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluentcrm_custom_fields_data( $data ) {

		if ( ! class_exists( 'FluentCrm\App\Models\CustomContactField' ) ) {
			return [];
		}

		$custom_fields = ( new CustomContactField() )->getGlobalFields()['fields'];
		$options       = [];

		if ( ! empty( $custom_fields ) ) {
			foreach ( $custom_fields as $custom_field ) {
				$options[] = [
					'label' => $custom_field['label'],
					'value' => $custom_field['slug'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Fetch WP JOB Manager Last Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_job_manger_last_data( $data ) {
		global $wpdb;
		$job_type = $data['filter']['job_type']['value'];
		$args     = [
			'posts_per_page' => 1,
			'post_type'      => 'job_listing',
			'orderby'        => 'id',
			'order'          => 'DESC',
		];

		if ( -1 !== $job_type ) {
			$args['tax_query'] = [              // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'job_listing_type',
					'field'    => 'term_id',
					'terms'    => $job_type,
				],
			];
		}
		$posts = get_posts( $args );
		if ( empty( $posts ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"ID":145,"wpjob_author":"1","wpjob_date":"2023-01-22 17:38:03","wpjob_date_gmt":"2023-01-22 17:38:03","wpjob_content":"","wpjob_title":"PHP Developer","wpjob_excerpt":"","wpjob_status":"publish","comment_status":"closed","ping_status":"closed","wpjob_password":"","wpjob_name":"project-manager","to_ping":"","pinged":"","wpjob_modified":"2023-01-23 03:23:35","wpjob_modified_gmt":"2023-01-23 03:23:35","wpjob_content_filtered":"","wpjob_parent":0,"guid":"http:\/\/connector.com\/?post_type=job_listing&#038;p=145","menu_order":-1,"wpjob_type":"job_listing","wpjob_mime_type":"","comment_count":"0","filter":"raw","_filled":["0"],"_featured":["1"],"_tribe_ticket_capacity":["0"],"_tribe_ticket_version":["5.5.6"],"_edit_lock":["1674444219:1"],"_job_expires":["2023-02-21"],"_tracked_submitted":["1674409083"],"_edit_last":["1"],"_job_location":[""],"_application":["johnsmith@bexample.com"],"_company_name":["test"],"_company_website":[""],"_company_tagline":[""],"_company_twitter":[""],"_company_video":[""],"_remote_position":["1"],"_llms_reviews_enabled":[""],"_llms_display_reviews":[""],"_llms_num_reviews":["0"],"_llms_multiple_reviews_disabled":[""],"wp_user_id":1,"user_login":"john","display_name":"john","user_firstname":"john","user_lastname":"smith","user_email":"johnsmith@bexample.com","user_role":["administrator","subscriber","tutor_instructor"]}}', true );
			return $context;
		}

		$post         = $posts[0];
		$post_content = WordPress::get_post_context( $post->ID );
		$post_meta    = WordPress::get_post_meta( $post->ID );
		$job_data     = array_merge( $post_content, $post_meta, WordPress::get_user_context( $post->post_author ) );
		foreach ( $job_data as $key => $job ) {
			$newkey = str_replace( 'post', 'wpjob', $key );
			unset( $job_data[ $key ] );
			$job_data[ $newkey ] = $job;
		}
		$context['response_type']  = 'live';
		$context['pluggable_data'] = $job_data;
		return $context;

	}

	/**
	 * Get Amelia Appointment Category.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_category_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$categories = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name FROM {$wpdb->prefix}amelia_categories WHERE status = %s ORDER BY name ASC LIMIT %d OFFSET %d",
				[ 'visible', $limit, $offset ]
			),
			OBJECT
		);

		$categories_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$options[] = [
					'label' => $category->name,
					'value' => $category->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $categories_count > $limit && $categories_count > $offset,
		];

	}

	/**
	 * Get Amelia Appointment Services.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_service_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$services = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name FROM {$wpdb->prefix}amelia_services 
				WHERE categoryId = %d AND status = %s 
				ORDER BY name ASC LIMIT %d OFFSET %d",
				[ $data['dynamic'], 'visible', $limit, $offset ]
			),
			OBJECT
		);

		$services_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $services ) ) {
			foreach ( $services as $category ) {
				$options[] = [
					'label' => $category->name,
					'value' => $category->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $services_count > $limit && $services_count > $offset,
		];

	}

	/**
	 * Get Amelia Events.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_events_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$events = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name from {$wpdb->prefix}amelia_events WHERE status = %s ORDER BY name ASC LIMIT %d OFFSET %d",
				[ 'approved', $limit, $offset ]
			),
			OBJECT
		);

		$list_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$options[] = [
					'label' => $event->name,
					'value' => $event->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $list_count > $limit && $list_count > $offset,
		];

	}

	/**
	 * Get Amelia Events.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_booking_status_list( $data ) {

		$options   = [];
		$options[] = [
			'label' => 'Approved',
			'value' => 'approved',
		];
		$options[] = [
			'label' => 'Pending',
			'value' => 'pending',
		];
		$options[] = [
			'label' => 'Rejected',
			'value' => 'rejected',
		];
		$options[] = [
			'label' => 'Canceled',
			'value' => 'canceled',
		];
		$options[] = [
			'label' => 'No-show',
			'value' => 'no-show',
		];
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_booked_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments )',
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments ) AND appointments.serviceId = %d',
					[ $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 13:00:00","bookingEnd":"2023-02-28 14:00:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"1","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 13:00:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"John","lastName":"Doe","email":"johnd@gmail.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_booking_status_changed_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];
		$appointment_status   = $data['filter']['appointment_status']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments )',
					ARRAY_A
				);
			}
		} else {
			if ( -1 === $appointment_status ) {
				$result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.serviceId = %d',
						[ $appointment_service ]
					),
					ARRAY_A
				);
			} else {
				$result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.status = %s AND appointments.serviceId = %d',
						[ $appointment_status, $appointment_service ]
					),
					ARRAY_A
				);
			}
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data']                         = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['pluggable_data']['amelia_category_list'] = $appointment_category;
			if ( -1 === $appointment_status ) {
				$context['pluggable_data']['appointment_status'] = 'approved';
			} else {
				$context['pluggable_data']['appointment_status'] = $appointment_status;
			}
			if ( -1 === $appointment_service ) {
				$context['pluggable_data']['amelia_service_list'] = $service_result['id'];
			} else {
				$context['pluggable_data']['amelia_service_list'] = $appointment_service;
			}
			$context['response_type'] = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 13:00:00","bookingEnd":"2023-02-28 14:00:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"1","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 13:00:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"John","lastName":"Doe","email":"johnd@gmail.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1","amelia_category_list": "2","appointment_status": "approved","amelia_service_list": "4"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_event_status_changed_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$event_selected = $data['filter']['amelia_events_list']['value'];
		$event_status   = $data['filter']['event_booking_status']['value'];

		$query = 'SELECT * 
			FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
			INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
			ON customer.id = event_period.customerBookingId';

		if ( -1 === $event_selected ) {
			if ( -1 !== $event_status ) {
				$query .= $wpdb->prepare(
					' WHERE event_period.customerBookingId = (
						SELECT MAX(customerBookingId) 
						FROM ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods
					) AND customer.status = %s',
					$event_status
				);
			} else {
				$query .= ' WHERE event_period.customerBookingId = (
					SELECT MAX(customerBookingId) 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods
				)';
			}
		} else {
			if ( -1 !== $event_status ) {
				$query .= $wpdb->prepare(
					' WHERE event_period.eventPeriodId = %d AND customer.status = %s',
					$event_selected,
					$event_status
				);
			} else {
				$query .= $wpdb->prepare(
					' WHERE event_period.eventPeriodId = %d',
					$event_selected
				);
			}
		}

		$result = $wpdb->get_row( $query, ARRAY_A ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( ! empty( $result ) ) {
			$event      = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events WHERE id = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$event_tags = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events_tags WHERE eventId = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$tags       = [];
			if ( ! empty( $event_tags ) ) {
				foreach ( $event_tags as $key => $tag ) {
					$tags['event_tag'][ $key ] = $tag['name'];
				}
			} else {
				$tags = [];
			}
			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);
			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}
			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}
			$context['pluggable_data'] = array_merge( $result, $fields_arr, $event, $customer_result, $coupon_result, $tags );
			if ( -1 === $event_status ) {
				$context['pluggable_data']['event_booking_status'] = 'approved';
			} else {
				$context['pluggable_data']['event_booking_status'] = $event_status;
			}
			$context['response_type'] = 'live';
		} else {

			$context = json_decode( '{"response_type": "sample","pluggable_data": {"id": "1","appointmentId": null,"customerId": "1","status": "visible","price": "10","persons": "1","couponId": null,"token": "6485b07ce9","info": "{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"+213551223123\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\/Kolkata\",\"urlParams\":null}","utcOffset": null,"aggregatedPrice": "1","packageCustomerServiceId": null,"duration": null,"created": "2023-02-02 06:35:18","actionsCompleted": "1","Do You Know Automation?": "Yes","When Are You Coming?": "2023-04-20","Upload Something": "","Tell Us About You!": "Hey there!","customerBookingId": "105","eventPeriodId": "5","parentId": null,"name": "Music Event","bookingOpens": null,"bookingCloses": "2023-02-09 08:00:00","bookingOpensRec": "same","bookingClosesRec": "same","ticketRangeRec": "calculate","recurringCycle": null,"recurringOrder": null,"recurringInterval": null,"recurringMonthly": null,"monthlyDate": null,"monthlyOnRepeat": null,"monthlyOnDay": null,"recurringUntil": null,"maxCapacity": "12","maxCustomCapacity": null,"maxExtraPeople": null,"locationId": null,"customLocation": "Kolkata","description": null,"color": "#1788FB","show": "1","notifyParticipants": "1","settings": "{\"payments\":{\"onSite\":true,\"payPal\":{\"enabled\":false},\"stripe\":{\"enabled\":false},\"mollie\":{\"enabled\":false},\"razorpay\":{\"enabled\":false}},\"general\":{\"minimumTimeRequirementPriorToCanceling\":null,\"redirectUrlAfterAppointment\":null},\"zoom\":{\"enabled\":false},\"lessonSpace\":{\"enabled\":false}}","zoomUserId": null,"bringingAnyone": "1","bookMultipleTimes": "1","translations": "{\"defaultLanguage\":\"en_US\"}","depositPayment": "disabled","depositPerPerson": "1","fullPayment": "0","deposit": "0","customPricing": "0","organizerId": "2","closeAfterMin": null,"closeAfterMinBookings": "0","type": "customer","externalId": "91","firstName": "Jogn","lastName": "Doe","email": "johnd@gmail.com","birthday": null,"phone": "+213551223123","gender": null,"note": null,"pictureFullPath": null,"pictureThumbPath": null,"password": null,"usedTokens": null,"countryPhoneIso": "dz","timeZone": null,"event_booking_status":"approved"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_new_event_attendee_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$event_selected = $data['filter']['amelia_events_list']['value'];

		if ( -1 === $event_selected ) {
			$result = $wpdb->get_row(
				'SELECT * 
				FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
				INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
				ON customer.id = event_period.customerBookingId 
				WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods )',
				ARRAY_A
			);
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
					INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
					ON customer.id = event_period.customerBookingId 
					WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods ) AND eventPeriodId = %d',
					[ $event_selected ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$event      = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events WHERE id = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$event_tags = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events_tags WHERE eventId = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$tags       = [];
			if ( ! empty( $event_tags ) ) {
				foreach ( $event_tags as $key => $tag ) {
					$tags['event_tag'][ $key ] = $tag['name'];
				}
			} else {
				$tags = [];
			}

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $event, $customer_result, $coupon_result, $tags );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type": "sample","pluggable_data": {"id": "1","appointmentId": null,"customerId": "1","status": "visible","price": "10","persons": "1","couponId": null,"token": "6485b07ce9","info": "{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"+213551223123\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\/Kolkata\",\"urlParams\":null}","utcOffset": null,"aggregatedPrice": "1","packageCustomerServiceId": null,"duration": null,"created": "2023-02-02 06:35:18","actionsCompleted": "1","Do You Know Automation?": "Yes","When Are You Coming?": "2023-04-20","Upload Something": "","Tell Us About You!": "Hey there!","customerBookingId": "105","eventPeriodId": "5","parentId": null,"name": "Music Event","bookingOpens": null,"bookingCloses": "2023-02-09 08:00:00","bookingOpensRec": "same","bookingClosesRec": "same","ticketRangeRec": "calculate","recurringCycle": null,"recurringOrder": null,"recurringInterval": null,"recurringMonthly": null,"monthlyDate": null,"monthlyOnRepeat": null,"monthlyOnDay": null,"recurringUntil": null,"maxCapacity": "12","maxCustomCapacity": null,"maxExtraPeople": null,"locationId": null,"customLocation": "Kolkata","description": null,"color": "#1788FB","show": "1","notifyParticipants": "1","settings": "{\"payments\":{\"onSite\":true,\"payPal\":{\"enabled\":false},\"stripe\":{\"enabled\":false},\"mollie\":{\"enabled\":false},\"razorpay\":{\"enabled\":false}},\"general\":{\"minimumTimeRequirementPriorToCanceling\":null,\"redirectUrlAfterAppointment\":null},\"zoom\":{\"enabled\":false},\"lessonSpace\":{\"enabled\":false}}","zoomUserId": null,"bringingAnyone": "1","bookMultipleTimes": "1","translations": "{\"defaultLanguage\":\"en_US\"}","depositPayment": "disabled","depositPerPerson": "1","fullPayment": "0","deposit": "0","customPricing": "0","organizerId": "2","closeAfterMin": null,"closeAfterMinBookings": "0","type": "customer","externalId": "91","firstName": "John","lastName": "Doe","email": "johnd@gmail.com","birthday": null,"phone": "+213551223123","gender": null,"note": null,"pictureFullPath": null,"pictureThumbPath": null,"password": null,"usedTokens": null,"countryPhoneIso": "dz","timeZone": null}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_event_booking_cancelled_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$event_selected = $data['filter']['amelia_events_list']['value'];

		if ( -1 === $event_selected ) {
			$result = $wpdb->get_row(
				'SELECT * 
				FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
				INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
				ON customer.id = event_period.customerBookingId 
				WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods ) AND customer.status = "canceled"',
				ARRAY_A
			);
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
					INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
					ON customer.id = event_period.customerBookingId 
					WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods ) AND eventPeriodId = %d AND customer.status = "canceled"',
					[ $event_selected ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {
			$event      = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events WHERE id = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$event_tags = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events_tags WHERE eventId = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);
			$tags       = [];
			if ( ! empty( $event_tags ) ) {
				foreach ( $event_tags as $key => $tag ) {
					$tags['event_tag'][ $key ] = $tag['name'];
				}
			} else {
				$tags = [];
			}
			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);
			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}
			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}
			$context['pluggable_data'] = array_merge( $result, $fields_arr, $event, $customer_result, $coupon_result, $tags );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type": "sample","pluggable_data": {"id": "1","appointmentId": null,"customerId": "1","status": "visible","price": "10","persons": "1","couponId": null,"token": "6485b07ce9","info": "{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"+213551223123\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\/Kolkata\",\"urlParams\":null}","utcOffset": null,"aggregatedPrice": "1","packageCustomerServiceId": null,"duration": null,"created": "2023-02-02 06:35:18","actionsCompleted": "1","Do You Know Automation?": "Yes","When Are You Coming?": "2023-04-20","Upload Something": "","Tell Us About You!": "Hey there!","customerBookingId": "105","eventPeriodId": "5","parentId": null,"name": "Music Event","bookingOpens": null,"bookingCloses": "2023-02-09 08:00:00","bookingOpensRec": "same","bookingClosesRec": "same","ticketRangeRec": "calculate","recurringCycle": null,"recurringOrder": null,"recurringInterval": null,"recurringMonthly": null,"monthlyDate": null,"monthlyOnRepeat": null,"monthlyOnDay": null,"recurringUntil": null,"maxCapacity": "12","maxCustomCapacity": null,"maxExtraPeople": null,"locationId": null,"customLocation": "Kolkata","description": null,"color": "#1788FB","show": "1","notifyParticipants": "1","settings": "{\"payments\":{\"onSite\":true,\"payPal\":{\"enabled\":false},\"stripe\":{\"enabled\":false},\"mollie\":{\"enabled\":false},\"razorpay\":{\"enabled\":false}},\"general\":{\"minimumTimeRequirementPriorToCanceling\":null,\"redirectUrlAfterAppointment\":null},\"zoom\":{\"enabled\":false},\"lessonSpace\":{\"enabled\":false}}","zoomUserId": null,"bringingAnyone": "1","bookMultipleTimes": "1","translations": "{\"defaultLanguage\":\"en_US\"}","depositPayment": "disabled","depositPerPerson": "1","fullPayment": "0","deposit": "0","customPricing": "0","organizerId": "2","closeAfterMin": null,"closeAfterMinBookings": "0","type": "customer","externalId": "91","firstName": "Jogn","lastName": "Doe","email": "johnd@gmail.com","birthday": null,"phone": "+213551223123","gender": null,"note": null,"pictureFullPath": null,"pictureThumbPath": null,"password": null,"usedTokens": null,"countryPhoneIso": "dz","timeZone": null}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_rescheduled_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments )',
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments ) AND appointments.serviceId = %d',
					[ $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$appointment_data['isRescheduled'] = '1';
			$context['pluggable_data']         = array_merge( $result, $fields_arr, $appointment_data, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']          = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 15:30:00","bookingEnd":"2023-02-28 16:30:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"54","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","isRescheduled":"1","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 15:30:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"John","lastName":"Doe","email":"johnd@gmail.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}


	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_cancelled_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.status = %s order by RAND() DESC LIMIT 1',
						[ 'canceled' ]
					),
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.status = %s AND appointments.serviceId = %d order by RAND() DESC LIMIT 1',
					[ 'canceled', $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 15:30:00","bookingEnd":"2023-02-28 16:30:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"54","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"John\",\"lastName\":\"Doe\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 15:30:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"John","lastName":"Doe","email":"johnd@gmail.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}

	/**
	 * Get MailPoet Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_forms( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forms = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mailpoet_forms WHERE `deleted_at` IS NULL AND `status` = %s ORDER BY id LIMIT %d OFFSET %d',
				[ 'enabled', $limit, $offset ]
			)
		);

		$form_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];

		if ( ! empty( $forms ) ) {
			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$options[] = [
						'label' => $form->name,
						'value' => $form->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];

	}

	/**
	 * Get MailPoet List.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_list( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		$mailpoet = \MailPoet\API\API::MP( 'v1' );
		$lists    = $mailpoet->getLists();

		$options = [];

		if ( ! empty( $lists ) ) {
			if ( is_array( $lists ) ) {
				foreach ( $lists as $list ) {
					$options[] = [
						'label' => $list['name'],
						'value' => $list['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MailPoet Subscriber Status.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_subscriber_status( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		$subscriber_status = [
			'subscribed'   => 'Subscribed',
			'unconfirmed'  => 'Unconfirmed',
			'unsubscribed' => 'Unsubscribed',
			'inactive'     => 'Inactive',
			'bounced'      => 'Bounced',
		];

		$options = [];
		foreach ( $subscriber_status as $key => $status ) {
			$options[] = [
				'label' => $status,
				'value' => $key,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MailPoet Subscribers.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_subscribers( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$subscribers = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT SQL_CALC_FOUND_ROWS id,email FROM ' . $wpdb->prefix . 'mailpoet_subscribers ORDER BY id DESC LIMIT %d OFFSET %d',
				[ $limit, $offset ]
			)
		);

		$subscribers_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];

		if ( ! empty( $subscribers ) ) {
			if ( is_array( $subscribers ) ) {
				foreach ( $subscribers as $subscriber ) {
					$options[] = [
						'label' => $subscriber->email,
						'value' => $subscriber->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $subscribers_count > $limit && $subscribers_count > $offset,
		];
	}

	/**
	 * Get ConvertPro Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_convertpro_form_list( $data ) {
		if ( ! class_exists( '\Cp_V2_Loader' ) ) {
			return;
		}

		$cp_popups_inst = CP_V2_Popups::get_instance();
		$popups         = $cp_popups_inst->get_all();

		$form_count = count( $popups );

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$options = [];

		if ( ! empty( $popups ) ) {
			if ( is_array( $popups ) ) {
				foreach ( $popups as $form ) {
					$options[] = [
						'label' => $form->post_title,
						'value' => $form->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];

	}

	/**
	 * Get ProjectHuddle Websites.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_project_huddle_websites( $data ) {

		$sites = new WP_Query(
			[
				'post_type'      => 'ph-website',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			]
		);

		$site_ids = (array) $sites->posts;

		$options = [];
		if ( ! empty( $site_ids ) ) {
			if ( is_array( $site_ids ) ) {
				foreach ( $site_ids as $site_id ) {
					$options[] = [
						'label' => get_the_title( $site_id ),
						'value' => $site_id,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_project_huddle_comment_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		if ( -1 !== $data['dynamic'] ) {
			$threads = get_posts(
				[
					'post_type'      => 'phw_comment_loc',
					'posts_per_page' => 1,
					'meta_value'     => $data['dynamic'],
					'meta_key'       => 'project_id',
					'orderby'        => 'asc',
				]
			);
		} else {
			$threads = [];
		}

		if ( ! empty( $threads ) ) {
			$comment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT  ' . $wpdb->prefix . 'comments.comment_ID
					FROM ' . $wpdb->prefix . 'comments 
					WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_comment") AND comment_post_ID = %d
					ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
					LIMIT 0,1',
					$threads[0]->ID
				),
				ARRAY_A
			);

			if ( ! empty( $comment_result ) ) {
				$comment_id                  = get_comment( $comment_result['comment_ID'], ARRAY_A );
				$comments['comment_ID']      = isset( $comment_id['comment_ID'] ) ? $comment_id['comment_ID'] : '';
				$comments['comment_post_ID'] = isset( $comment_id['comment_post_ID'] ) ? $comment_id['comment_post_ID'] : '';

				$comments['comment_author'] = isset( $comment_id['comment_author'] ) ? $comment_id['comment_author'] : '';

				$comments['comment_author_email'] = isset( $comment_id['comment_author_email'] ) ? $comment_id['comment_author_email'] : '';

				$comments['comment_date'] = isset( $comment_id['comment_date'] ) ? $comment_id['comment_date'] : '';

				$comments['comment_content'] = isset( $comment_id['comment_content'] ) ? $comment_id['comment_content'] : '';

				$comments['comment_type'] = isset( $comment_id['comment_type'] ) ? $comment_id['comment_type'] : '';

				$comment_item_id = get_comment_meta( $comment_result['comment_ID'], 'item_id' );
				if ( ! empty( $comment_item_id ) && is_array( $comment_item_id ) ) {
					$comments['comment_item_id']         = $comment_item_id[0];
					$comments['comment_item_page_title'] = get_the_title( (int) $comment_item_id[0] );
					$comments['comment_item_page_url']   = get_post_meta( (int) $comment_item_id[0], 'page_url', true );
				}

				$context['pluggable_data'] = $comments;
				$context['response_type']  = 'live';
			} else {
				$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment"}}', true );
			}
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment","comment_item_id": 9579, "comment_item_page_title": "About Us","comment_item_page_url": "https://example.com/abotu-us"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_project_huddle_resolved_comment_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$get_comments = $wpdb->get_row(
			'SELECT  ' . $wpdb->prefix . 'comments.comment_ID, ' . $wpdb->prefix . 'comments.comment_content
			FROM ' . $wpdb->prefix . 'comments 
			WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_status") AND comment_content = "Resolved"
			ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
			LIMIT 0,1'
		);

		if ( ! empty( $get_comments ) ) {
			$comment_id     = get_comment( $get_comments->comment_ID, ARRAY_A );
			$comment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT  ' . $wpdb->prefix . 'comments.comment_ID
					FROM ' . $wpdb->prefix . 'comments 
					WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_comment") AND comment_post_ID = %d
					ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
					LIMIT 0,1',
					isset( $comment_id['comment_post_ID'] ) ? $comment_id['comment_post_ID'] : ''
				),
				ARRAY_A
			);

			$actual_comment                   = get_comment( $comment_result['comment_ID'], ARRAY_A );
			$comments['comment_ID']           = isset( $actual_comment['comment_ID'] ) ? $actual_comment['comment_ID'] : '';
			$comments['comment_post_ID']      = isset( $actual_comment['comment_post_ID'] ) ? $actual_comment['comment_post_ID'] : '';
			$comments['comment_author']       = isset( $actual_comment['comment_author'] ) ? $actual_comment['comment_author'] : '';
			$comments['comment_author_email'] = isset( $actual_comment['comment_author_email'] ) ? $actual_comment['comment_author_email'] : '';
			$comments['comment_date']         = isset( $actual_comment['comment_date'] ) ? $actual_comment['comment_date'] : '';
			$comments['comment_content']      = isset( $actual_comment['comment_content'] ) ? $actual_comment['comment_content'] : '';
			$comments['comment_type']         = isset( $actual_comment['comment_type'] ) ? $actual_comment['comment_type'] : '';
			$comments['comment_status']       = $get_comments->comment_content;
			$comment_item_id                  = get_comment_meta( $comment_result['comment_ID'], 'item_id' );
			if ( ! empty( $comment_item_id ) && is_array( $comment_item_id ) ) {
				$comments['comment_item_id']         = $comment_item_id[0];
				$comments['comment_item_page_title'] = get_the_title( (int) $comment_item_id[0] );
				$comments['comment_item_page_url']   = get_post_meta( (int) $comment_item_id[0], 'page_url', true );
			}
			$context['pluggable_data'] = $comments;
			$context['response_type']  = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment","comment_status":"Resolved","comment_item_id": 9579, "comment_item_page_title": "About Us","comment_item_page_url": "https://example.com/abotu-us" }}', true );
		}

		return $context;
	}

	/**
	 * Get MasterStudy LMS Courses.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_courses( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'stm-courses',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$courses = get_posts( $args );

		$course_count = wp_count_posts( 'stm-courses' )->publish;

		$options = [];
		if ( ! empty( $courses ) ) {
			if ( is_array( $courses ) ) {
				foreach ( $courses as $course ) {
					$options[] = [
						'label' => $course->post_title,
						'value' => $course->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $course_count > $limit && $course_count > $offset,
		];

	}

	/**
	 * Get MasterStudy LMS Lessons.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_lessons( $data ) {

		global $wpdb;
		$options   = [];
		$course_id = $data['dynamic'];

		// Use curriculum repository class.
		if ( class_exists( '\MasterStudy\Lms\Repositories\CurriculumRepository' ) ) {
			$curriculum_repo = new \MasterStudy\Lms\Repositories\CurriculumRepository();
		} else {
			$curriculum_repo = false;
		}

		if ( '-1' === $course_id ) {
			$lessons = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS ID, post_title FROM $wpdb->posts WHERE post_type = %s ORDER BY post_title ASC",
					'stm-lessons'
				)
			);

			if ( ! empty( $lessons ) ) {
				if ( is_array( $lessons ) ) {
					foreach ( $lessons as $lesson ) {
						$options[] = [
							'label' => $lesson->post_title,
							'value' => $lesson->ID,
						];
					}
				}
			}
		} else {
			$lessons = [];
			if ( $curriculum_repo ) {
				$curriculum = $curriculum_repo->get_curriculum( absint( $course_id ) );
				if ( ! empty( $curriculum ) && is_array( $curriculum ) && isset( $curriculum['materials'] ) ) {
					if ( ! empty( $curriculum['materials'] ) && is_array( $curriculum['materials'] ) ) {
						foreach ( $curriculum['materials'] as $material ) {
							if ( 'stm-lessons' === $material['post_type'] ) {
								$lessons[] = [
									'value' => $material['post_id'],
									'text'  => $material['title'],
								];
							}
						}
					}
				}
				if ( ! empty( $lessons ) ) {
					if ( is_array( $lessons ) ) {
						foreach ( $lessons as $lesson ) {
							$options[] = [
								'label' => $lesson['text'],
								'value' => $lesson['value'],
							];
						}
					}
				}
			} else {
				$lessons = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT SQL_CALC_FOUND_ROWS ID, post_title
						FROM $wpdb->posts
						WHERE FIND_IN_SET(
							ID,
							(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'curriculum')
						)
						AND post_type = 'stm-lessons'
						ORDER BY post_title ASC",
						absint( $course_id )
					)
				);
				if ( ! empty( $lessons ) ) {
					if ( is_array( $lessons ) ) {
						foreach ( $lessons as $lesson ) {
							$options[] = [
								'label' => $lesson->post_title,
								'value' => $lesson->ID,
							];
						}
					}
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get MasterStudy LMS Quiz.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_quiz( $data ) {

		global $wpdb;

		$options   = [];
		$course_id = $data['dynamic'];

		// Use curriculum repository class.
		if ( class_exists( '\MasterStudy\Lms\Repositories\CurriculumRepository' ) ) {
			$curriculum_repo = new \MasterStudy\Lms\Repositories\CurriculumRepository();
		} else {
			$curriculum_repo = false;
		}

		if ( '-1' === $course_id ) {
			$quizzes = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title
					FROM $wpdb->posts
					WHERE post_type = %s
					ORDER BY post_title ASC",
					'stm-quizzes'
				)
			);
			if ( ! empty( $quizzes ) ) {
				if ( is_array( $quizzes ) ) {
					foreach ( $quizzes as $quiz ) {
						$options[] = [
							'label' => $quiz->post_title,
							'value' => $quiz->ID,
						];
					}
				}
			}
		} else {
			$quizzes = [];
			if ( $curriculum_repo ) {
				$curriculum = $curriculum_repo->get_curriculum( absint( $course_id ) );
				if ( ! empty( $curriculum ) && is_array( $curriculum ) && isset( $curriculum['materials'] ) ) {
					if ( ! empty( $curriculum['materials'] ) && is_array( $curriculum['materials'] ) ) {
						foreach ( $curriculum['materials'] as $material ) {
							if ( 'stm-quizzes' === $material['post_type'] ) {
								$quizzes[] = [
									'value' => $material['post_id'],
									'text'  => $material['title'],
								];
							}
						}
					}
				}
				if ( ! empty( $quizzes ) ) {
					if ( is_array( $quizzes ) ) {
						foreach ( $quizzes as $quiz ) {
							$options[] = [
								'label' => $quiz['text'],
								'value' => $quiz['value'],
							];
						}
					}
				}
			} else {
				$quizzes = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT ID, post_title
						FROM $wpdb->posts
						WHERE FIND_IN_SET(
							ID,
							(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'curriculum')
						)
						AND post_type = 'stm-quizzes'
						ORDER BY post_title ASC
						",
						absint( $course_id )
					)
				);
				if ( ! empty( $quizzes ) ) {
					if ( is_array( $quizzes ) ) {
						foreach ( $quizzes as $quiz ) {
							$options[] = [
								'label' => $quiz->post_title,
								'value' => $quiz->ID,
							];
						}
					}
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Search MasterStudy LMS data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_ms_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( 'stm_lms_course_completed' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.progress_percent=100 AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_id = %s AND postmeta.progress_percent=100 AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_lesson_passed' === $trigger ) {
			$post_id = $data['filter']['lesson']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_lessons as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.lesson_id WHERE posts.post_type=%s order by postmeta.user_lesson_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_lessons as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.lesson_id WHERE postmeta.lesson_id=%s AND posts.post_type=%s order by postmeta.user_lesson_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_quiz_passed' === $trigger ) {
			$post_id = $data['filter']['quiz']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.status='passed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.quiz_id=%s AND postmeta.status='passed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_quiz_failed' === $trigger ) {
			$post_id = $data['filter']['quiz']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.status='failed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.quiz_id=%s AND postmeta.status='failed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_lms_user_enroll_course' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.status='enrolled' AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_id=%s AND postmeta.status='enrolled' AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_id, $post_type ) );
			}       
		}

		if ( ! empty( $result ) ) {

			switch ( $trigger ) {
				case 'stm_lms_course_completed':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;
					$course           = get_the_title( $result_course_id );
					$course_link      = get_the_permalink( $result_course_id );
					$featured_image   = get_the_post_thumbnail_url( $result_course_id );

					$data         = [
						'course_id'             => $result_course_id,
						'course_title'          => $course,
						'course_link'           => $course_link,
						'course_featured_image' => $featured_image,
						'course_progress'       => $result[0]->progress_percent,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_lesson_passed':
					$result_lesson_id = $result[0]->lesson_id;
					$result_user_id   = $result[0]->user_id;
					$lesson           = get_the_title( $result_lesson_id );
					$lesson_link      = get_the_permalink( $result_lesson_id );

					$data         = [
						'lesson_id'    => $result_lesson_id,
						'lesson_title' => $lesson,
						'lesson_link'  => $lesson_link,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_quiz_passed':
					$result_quiz_id = $result[0]->quiz_id;
					$result_user_id = $result[0]->user_id;
					$quiz_title     = get_the_title( $result_quiz_id );
					$quiz_link      = get_the_permalink( $result_quiz_id );

					$data         = [
						'quiz_id'    => $result_quiz_id,
						'quiz_title' => $quiz_title,
						'quiz_link'  => $quiz_link,
						'quiz_score' => $result[0]->progress,
						'result'     => 'passed',
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_quiz_failed':
					$result_quiz_id = $result[0]->quiz_id;
					$result_user_id = $result[0]->user_id;
					$quiz_title     = get_the_title( $result_quiz_id );
					$quiz_link      = get_the_permalink( $result_quiz_id );

					$data         = [
						'quiz_id'    => $result_quiz_id,
						'quiz_title' => $quiz_title,
						'quiz_link'  => $quiz_link,
						'quiz_score' => $result[0]->progress,
						'result'     => 'failed',
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_lms_user_enroll_course':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;

					$course         = get_the_title( $result_course_id );
					$course_link    = get_the_permalink( $result_course_id );
					$featured_image = get_the_post_thumbnail_url( $result_course_id );

					$data         = [
						'course_id'             => $result_course_id,
						'course_title'          => $course,
						'course_link'           => $course_link,
						'course_featured_image' => $featured_image,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context_data;
			$context['response_type']  = 'live';
		}

		return $context;

	}

	/**
	 * Prepare Fluent Support Mailbox list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fs_mailbox_list( $data ) {
		$options = [];
		global $wpdb;
		$mailboxes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_mail_boxes ORDER BY id DESC", ARRAY_A );
		if ( ! empty( $mailboxes ) ) {
			foreach ( $mailboxes as $key => $value ) {

				$options[] = [
					'label' => $value['name'],
					'value' => $value['id'],
				];
	
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Fluent Support Product list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fs_product_list( $data ) {
		$options = [];

		if ( ! class_exists( '\FluentSupport\App\Models\Product' ) ) {
			return [];
		}

		$products = \FluentSupport\App\Models\Product::select( [ 'id', 'title' ] )->get();
		if ( ! empty( $products ) ) {
			foreach ( $products as $key => $product ) {
				$options[] = [
					'label' => $product['title'],
					'value' => $product['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Fluent Support Product list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fs_person_type_list( $data ) {
		$options = [
			[
				'label' => 'Customer',
				'value' => 'customer',
			],
			[
				'label' => 'Agent',
				'value' => 'agent',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluent_support_triggers_last_data( $data ) {
		global $wpdb;
		$context                  = [];
		$context['response_type'] = 'sample';

		if ( ! class_exists( '\FluentSupport\App\Models\Ticket' ) ) {
			return [];
		}

		$ticket_data = [
			'id'                                   => '1',
			'customer_id'                          => '2',
			'agent_id'                             => '3',
			'product_id'                           => '5',
			'product_source'                       => 'local',
			'privacy'                              => 'private',
			'priority'                             => 'normal',
			'client_priority'                      => 'medium',
			'status'                               => 'active',
			'title'                                => 'Sample Ticket Title',
			'slug'                                 => 'sample-ticket-title',
			'hash'                                 => 'f8a8cfb946',
			'content_hash'                         => 'd65500d62621be8b493c22b1d888052c',
			'content'                              => '<p>Sample content.</p>',
			'last_customer_response'               => '2023-04-27 07:30:46',
			'waiting_since'                        => '2023-04-27 07:30:46',
			'response_count'                       => '2',
			'total_close_time'                     => '7042',
			'resolved at'                          => '2023-04-27 09:28:08',
			'closed_by'                            => '1',
			'created_at'                           => '2023-04-27 07:30:46',
			'updated_at'                           => '2023-04-27 10:28:08',
			'mailbox_id'                           => '1',
			'mailbox_name'                         => 'SureTriggers',
			'mailbox_slug'                         => 'suretriggers',
			'mailbox_box_type'                     => 'web',
			'mailbox email'                        => 'john_doe@sample.com',
			'mailbox_settings_admin_email_address' => 'john_doe@sample.com',
			'mailbox_created_by'                   => '1',
			'mailbox_is_default'                   => 'yes',
			'mailbox_created_at'                   => '2023-04-26 06:29:01',
			'mailbox_updated_at'                   => '2023-04-26 06:29:01',
		];

		$customer_data = [
			'id'          => '1',
			'first_name'  => 'John',
			'last_name'   => 'Doe',
			'email'       => 'john_doe@sample.com',
			'person_type' => 'agent',
			'status'      => 'active',
			'hash'        => '3b2b5f0432561cb81b1302b8a16b93a0',
			'user_id'     => '1',
			'created_at'  => '2023-04-27 07:30:46',
			'updated_at'  => '2023-04-27 10:28:08',
			'full_name'   => 'John Doe',
			'photo'       => 'https://www.gravatar.com/avatar/c2b06ae950033b392998ada50767b50e?s=128',
		];

		$reply_data = [
			'ticket_id'          => '1',
			'conversation_type'  => 'response',
			'content'            => '<p>Sample content.</p>',
			'source'             => 'web',
			'content_hash'       => '2cc0e35d8fb92a0675d67999b073b3a4',
			'created_at'         => '2023-04-27 07:30:46',
			'updated_at'         => '2023-04-27 10:28:08',
			'id'                 => '1',
			'person_id'          => '2',
			'person_first_name'  => 'John',
			'person_last_name'   => 'Doe',
			'person_email'       => 'john_doe@sample.com',
			'person_person_type' => 'agent',
			'person_status'      => 'active',
			'person_hash'        => '3b2b5f0432561cb81b1302b8a16b93a0',
			'person_user_id'     => '1',
			'person_created_at'  => '2023-04-27 07:30:46',
			'person_updated_at'  => '2023-04-27 10:28:08',
			'person_full_name'   => 'John Doe',
			'person_photo'       => 'https://www.gravatar.com/avatar/c2b06ae950033b392998ada50767b50e?s=128',
		];

		$term       = isset( $data['search_term'] ) ? $data['search_term'] : '';
		$mailbox    = isset( $data['mailbox_id'] ) ? $data['mailbox_id'] : '';
		$product_id = isset( $data['filter']['ticket_product_id']['value'] ) ? $data['filter']['ticket_product_id']['value'] : '';
		$person_id  = isset( $data['filter']['person_id']['value'] ) ? (string) $data['filter']['person_id']['value'] : '';
		
		if ( in_array( $term, [ 'response_added_by_agent', 'response_added_by_customer', 'ticket_replied_product_agent', 'ticket_replied_product_customer' ], true ) ) {
			if ( 'response_added_by_agent' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'agent'" );
				$replies     = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE conversation_type = 'response' ORDER BY id DESC LIMIT 1" );
				$tickets     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d ORDER BY id DESC LIMIT 1", $replies[0]->ticket_id ) );
				if ( ! empty( $tickets ) ) {
					$customers                = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data              = $tickets[0];
					$customer_data            = $customers[0];
					$reply_data               = $replies[0];
					$context['response_type'] = 'live';
				}
			} elseif ( 'response_added_by_customer' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'customer'" );
				$replies     = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE conversation_type = 'response' ORDER BY id DESC LIMIT 1" );
				$tickets     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE id = %d ORDER BY id DESC LIMIT 1", $replies[0]->ticket_id ) );
				if ( ! empty( $tickets ) ) {
					$customers                = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data              = $tickets[0];
					$customer_data            = $customers[0];
					$reply_data               = $replies[0];
					$context['response_type'] = 'live';
				}
			} elseif ( 'ticket_replied_product_agent' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'agent'" );
				$replies     = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE conversation_type = 'response' ORDER BY id DESC LIMIT 1" );
				if ( -1 == $product_id ) {
					$products = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_products", ARRAY_A );
					$products = array_map(
						function( $product ) {
							return $product['id'];
						},
						$products 
					);
					$tickets  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id IN (%s) ORDER BY last_customer_response DESC LIMIT 1", implode( ',', $products ) ) );
				} else {
					$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id = %d ORDER BY last_agent_response DESC LIMIT 1", $product_id ) );
				}
				if ( ! empty( $tickets ) ) {
					$customers                = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data              = $tickets[0];
					$customer_data            = $customers[0];
					$reply_data               = $replies[0];
					$context['response_type'] = 'live';
				}
			} elseif ( 'ticket_replied_product_customer' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'customer'" );
				$replies     = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_conversations WHERE conversation_type = 'response' ORDER BY id DESC LIMIT 1" );
				if ( -1 == $product_id ) {
					$products = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_products", ARRAY_A );
					$products = array_map(
						function( $product ) {
							return $product['id'];
						},
						$products 
					);
					$tickets  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id IN (%s) ORDER BY last_customer_response DESC LIMIT 1", implode( ',', $products ) ) );
				} else {
					$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id = %d ORDER BY last_customer_response DESC LIMIT 1", $product_id ) );
				}
				if ( ! empty( $tickets ) ) {
					$customers                = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data              = $tickets[0];
					$customer_data            = $customers[0];
					$reply_data               = $replies[0];
					$context['response_type'] = 'live';
				}
			}
			$context['pluggable_data'] = array_merge(
				[
					'reply'    => $reply_data,
					'ticket'   => $ticket_data,
					'customer' => $customer_data,
				]
			);
			if ( 'ticket_replied_product_customer' == $term || 'ticket_replied_product_agent' == $term ) {
				$context['pluggable_data']['ticket_product_id'] = $product_id;
			}
			if ( is_object( $ticket_data ) ) {
				$ticket_data = get_object_vars( $ticket_data );
			}
			$ticket = \FluentSupport\App\Models\Ticket::find( $ticket_data['id'] );
			if ( is_object( $ticket ) && method_exists( $ticket, 'customData' ) ) {
				$context['pluggable_data']['custom_fields'] = $ticket->customData();
			}
			$context['pluggable_data']['ticket_link'] = admin_url( "admin.php?page=fluent-support#/tickets/{$ticket_data['id']}/view" );
		} else {
			if ( 'ticket_created' == $term ) {
				if ( -1 == $mailbox ) {
					$tickets = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE status = 'new' ORDER BY id DESC LIMIT 1" );
				} else {
					$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE status = 'new' AND mailbox_id = %d ORDER BY id DESC LIMIT 1", $mailbox ) );
				}
				if ( ! empty( $tickets ) ) {
					$customers     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d", $tickets[0]->customer_id ) );
					$ticket_data   = $tickets[0];
					$customer_data = $customers[0];
				}
			} elseif ( 'ticket_closed_by_customer' == $term ) {
				$customers_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'customer'" );
				$tickets        = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE status = 'closed' AND closed_by = %s ORDER BY id DESC LIMIT 1", $customers_type[0] ) );
				if ( ! empty( $tickets ) ) {
					$customers     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data   = $tickets[0];
					$customer_data = $customers[0];
				}
			} elseif ( 'ticket_closed_by_agent' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = 'agent'" );
				$tickets     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE status = 'closed' AND closed_by = %s ORDER BY id DESC LIMIT 1", $agents_type[0] ) );
				if ( ! empty( $tickets ) ) {
					$customers     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data   = $tickets[0];
					$customer_data = $customers[0];
				}
			} elseif ( 'ticket_created_product' == $term ) {
				$agents_type = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}fs_persons" );
				if ( -1 == $product_id ) {
					$products = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_products", ARRAY_A );
					$products = array_map(
						function( $product ) {
							return $product['id'];
						},
						$products 
					);
					$tickets  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id IN (%s) ORDER BY id DESC LIMIT 1", implode( ',', $products ) ) );
				} else {
					$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id = %d ORDER BY id DESC LIMIT 1", $product_id ) );
				}
				if ( ! empty( $tickets ) ) {
					$customers     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data   = $tickets[0];
					$customer_data = $customers[0];
				}
			} elseif ( 'ticket_closed_product' == $term ) {
				$agents_type = $wpdb->get_results( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}fs_persons WHERE person_type = %s", $person_id ), ARRAY_A );
				$agents      = array_map(
					function( $agent ) {
						return $agent['id'];
					},
					$agents_type 
				);
				if ( -1 == $product_id ) {
					$products = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}fs_products", ARRAY_A );
					$products = array_map(
						function( $product ) {
							return $product['id'];
						},
						$products 
					);
					$tickets  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id IN (%s) AND status = 'closed' AND closed_by IN (%d) ORDER BY id DESC LIMIT 1", implode( ',', $products ), implode( ',', $agents ) ) );
				} else {
					$tickets = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_tickets WHERE product_id = %d AND status = 'closed' AND closed_by IN (%d) ORDER BY id DESC LIMIT 1", $product_id, implode( ',', $agents ) ) );
				}
				if ( ! empty( $tickets ) ) {
					$customers     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fs_persons WHERE id = %d ORDER BY id DESC LIMIT 1", $tickets[0]->customer_id ) );
					$ticket_data   = $tickets[0];
					$customer_data = $customers[0];
				}
			}
			$context['pluggable_data'] = array_merge(
				[
					'ticket'   => $ticket_data,
					'customer' => $customer_data,
				]
			);
			if ( 'ticket_created' == $term ) {
				$context['pluggable_data']['mailbox_id'] = $mailbox;
			} elseif ( 'ticket_created_product' == $term || 'ticket_replied_product_customer' == $term || 'ticket_replied_product_agent' == $term ) {
				$context['pluggable_data']['ticket_product_id'] = $product_id;
			} elseif ( 'ticket_closed_product' == $term ) {
				$context['pluggable_data']['ticket_product_id'] = $product_id;
				$context['pluggable_data']['person_id']         = $person_id;
			}
			if ( is_object( $ticket_data ) ) {
				$ticket_data = get_object_vars( $ticket_data );
			}
			$ticket = \FluentSupport\App\Models\Ticket::find( $ticket_data['id'] );
			if ( is_object( $ticket ) && method_exists( $ticket, 'customData' ) ) {
				$context['pluggable_data']['custom_fields'] = $ticket->customData();
			}
			$context['pluggable_data']['ticket_link'] = admin_url( "admin.php?page=fluent-support#/tickets/{$ticket_data['id']}/view" );
			$context['response_type']                 = 'live';
		}

		return $context;
	}

	/**
	 * Prepare Ultimate Member user_roles.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_um_user_roles( $data ) {
		if ( function_exists( 'get_editable_roles' ) ) {
			$roles = get_editable_roles();
		} else {
			$roles = wp_roles()->roles;
			$roles = apply_filters( 'editable_roles', $roles );
		}

		$options = [];
		foreach ( $roles as $role => $details ) {

			$options[] = [
				'label' => $details['name'],
				'value' => $role,
			];

		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Ultimate Member forms_list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_um_forms_list( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_type'      => 'um_form',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		];
		if ( 'register' == $data['filter']['type'] ) {
			$args['meta_query'] = [
				[
					'key'     => '_um_mode',
					'value'   => 'register',
					'compare' => 'LIKE',
				],
			];
		} elseif ( 'login' == $data['filter']['type'] ) {
			$args['meta_query'] = [
				[
					'key'     => '_um_mode',
					'value'   => 'login',
					'compare' => 'LIKE',
				],
			];
		}

		$forms_list = get_posts( $args );

		$forms_list_count = wp_count_posts( 'um_form' )->publish;

		$options = [];
		if ( ! empty( $forms_list ) ) {
			foreach ( $forms_list as $form ) {
				$title     = html_entity_decode( get_the_title( $form ), ENT_QUOTES, 'UTF-8' );
				$options[] = [
					'label' => $title,
					'value' => $form,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $forms_list_count > $limit && $forms_list_count > $offset,
		];
	}

	/**
	 * Get last data for Ultimate Member Login trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_logsin( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => '_um_last_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'test',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Register trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_registers( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => 'um_user_profile_url_slug_user_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'test',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Register trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_inactive( $data ) {
		$context = [];
		$args    = [
			'orderby'    => 'user_id',
			'meta_key'   => 'account_status',
			'meta_value' => 'inactive',
			'order'      => 'ASC',
			'number'     => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                                  = $users[0];
			$pluggable_data                        = [];
			$pluggable_data[]                      = WordPress::get_user_context( $user->ID );
			$pluggable_data['user_account_status'] = 'inactive';
			$context['pluggable_data']             = $pluggable_data;
			$context['response_type']              = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'          => 1,
				'user_login'          => 'test',
				'display_name'        => 'Test User',
				'user_firstname'      => 'Test',
				'user_lastname'       => 'User',
				'user_email'          => 'testuser@gmail.com',
				'user_role'           => [ $role ],
				'user_account_status' => 'inactive',
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Change Role trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_role_change( $data ) {
		$context = [];

		$role = $data['filter']['role']['value'];

		$args  = [
			'number' => 1,
			'role'   => $role,
		];
		$users = get_users( $args );
		shuffle( $users );
		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'          => 1,
				'user_login'          => 'test',
				'display_name'        => 'Test User',
				'user_firstname'      => 'Test',
				'user_lastname'       => 'User',
				'user_email'          => 'testuser@gmail.com',
				'user_role'           => [ $role ],
				'user_account_status' => 'inactive',
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get JetEngine WP Posttypes.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_je_posttype_list( $data ) {

		$post_types = get_post_types( [ 'public' => true ], 'object' );
		$post_types = apply_filters( 'suretriggers_post_types', $post_types );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$options = [];
		foreach ( $post_types as $post_type => $details ) {
			$options[] = [
				'label' => $details->label,
				'value' => $post_type,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get JetEngine WP fields.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_je_field_list( $data ) {

		$post_type = $data['dynamic'];

		$metaboxes = (array) get_option( 'jet_engine_meta_boxes', [] );

		$post_fields = array_filter(
			$metaboxes,
			function( $metabox ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				return 'post' === $metabox['args']['object_type'];
			}
		);

		$post_fields_count = count( $post_fields );

		$options = [];
		if ( ! empty( $post_fields ) ) {
			if ( is_array( $post_fields ) ) {
				foreach ( $post_fields as $post_field ) {
					if ( in_array( $post_type, $post_field['args']['allowed_post_type'], true ) ) {
						foreach ( $post_field['meta_fields']  as $meta_field ) {
							$options[] = [
								'label' => $meta_field['title'],
								'value' => $meta_field['name'],
							];
						}
					}
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_jet_engine_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];

		if ( -1 === $field ) {
			$metaboxes = (array) get_option( 'jet_engine_meta_boxes', [] );

			$post_fields = array_filter(
				$metaboxes,
				function( $metabox ) {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					return 'post' === $metabox['args']['object_type'];
				}
			);

			$options = [];
			if ( ! empty( $post_fields ) ) {
				if ( is_array( $post_fields ) ) {
					foreach ( $post_fields as $post_field ) {
						if ( in_array( $post_type, $post_field['args']['allowed_post_type'], true ) ) {
							foreach ( $post_field['meta_fields']  as $meta_field ) {
								$options[] = $meta_field['name'];
							}
						}
					}
				}
			}
			$random_key   = array_rand( $options );
			$random_value = $options[ $random_key ];
			$string       = '%' . $random_value . '%';
		} else {
			$string = '%' . $data['filter']['field_id']['value'] . '%';
		}

		$result = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key LIKE %s',
				[ $string ]
			),
			ARRAY_A
		);

		$ids = [];

		if ( ! empty( $result ) ) {
			foreach ( $result as $val ) {
				$ids[] = $val['post_id'];
			}
		}

		$lastupdated_args = [
			'post_type'      => $post_type,
			'orderby'        => 'modified',
			'post__in'       => $ids,
			'posts_per_page' => 1,
		];
		$lastupdated_loop = get_posts( $lastupdated_args );

		$response = [];
		if ( ! empty( $result ) ) {
			$context['post'] = $lastupdated_loop[0];

			$meta_value = get_post_meta( $lastupdated_loop[0]->ID, sprintf( '%s', $data['filter']['field_id']['value'] ), true );
			$meta_key   = sprintf( '%s', $data['filter']['field_id']['value'] );

			$context[ $meta_key ] = $meta_value;

			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"post":{"ID":198,"post_author":"1","post_date":"2023-02-08 13:31:13","post_date_gmt":"2023-02-08 13:31:13","post_content":"New Category1 - content","post_title":"jennjennn - Post - jenn","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","post_password":"","post_name":"jennjennn-post-jenn","to_ping":"","pinged":"","post_modified":"2023-04-10 06:23:40","post_modified_gmt":"2023-04-10 06:23:40","post_content_filtered":"","post_parent":0,"guid":"https:\/\/suretriggerswp.local\/jennjennn-post-jenn\/","menu_order":0,"post_type":"post","post_mime_type":"","comment_count":"0","filter":"raw"},"enter-post-extra-content-title":"dummy"}}', true );
		}

		return $response;

	}

	/**
	 * Get Formidable Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_formidable_form_list( $data ) {
		if ( ! class_exists( 'FrmForm' ) ) {
			return;
		}

		$page     = $data['page'];
		$term     = $data['search_term'];
		$limit    = Utilities::get_search_page_limit();
		$offset   = $limit * ( $page - 1 );
		$per_page = 10;

		$query                = [
			[
				'or'             => 1,
				'name LIKE'      => $term,
				'parent_form_id' => null,
			],
		];
		$query['is_template'] = 0;
		$query['status !']    = 'trash';
		$forms_list           = FrmForm::getAll( $query, '', $offset . ',' . $per_page );
		$form_count           = FrmForm::getAll( $query );
		$form_count           = count( $form_count );
		$options              = [];

		if ( ! empty( $forms_list ) ) {
			if ( is_array( $forms_list ) ) {
				foreach ( $forms_list as $form ) {
					$options[] = [
						'label' => $form->name,
						'value' => $form->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];
	}

	/**
	 * Get JetFormBuilder Form List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_jetform_list( $data ) {
		if ( ! class_exists( '\Jet_Form_Builder\Classes\Tools' ) ) {
			return;
		}

		$forms = \Jet_Form_Builder\Classes\Tools::get_forms_list_for_js();

		$options = [];
		foreach ( $forms as $form ) {

			if ( ! empty( $form['value'] ) ) {
				$options[] = [
					'label' => esc_html( $form['label'] ),
					'value' => esc_attr( $form['value'] ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Forminator Form List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_forminator_form_list( $data ) {
		if ( ! class_exists( 'Forminator_API' ) ) {
			return;
		}

		$forms = Forminator_API::get_forms( null, 1, 10 );

		$options = [];
		foreach ( $forms as $form ) {
			$options[] = [
				'label' => isset( $form->settings ) && isset( $form->settings['form_name'] ) ? $form->settings['form_name'] : $form->name,
				'value' => $form->id,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}


	/**
	 * Get BbPress topics list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bbp_topic_list( $data ) {
		$page     = $data['page'];
		$forum_id = $data['dynamic'];
		$limit    = Utilities::get_search_page_limit();
		$offset   = $limit * ( $page - 1 );
		$args     = [
			'post_type'  => 'topic',
			'offset'     => $offset,
			'meta_query' => [
				[
					'key'     => '_bbp_forum_id',
					'value'   => $forum_id,
					'compare' => '==',
				],           
			],
		];

		$topics       = get_posts( $args );
		$topics_count = wp_count_posts( 'topic' )->publish;

		$options = [];
		if ( ! empty( $topics ) ) {
			if ( is_array( $topics ) ) {
				foreach ( $topics as $topic ) {
					$options[] = [
						'label' => $topic->post_title,
						'value' => $topic->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $topics_count > $limit && $topics_count > $offset,
		];
	}


	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_bbp_last_data( $data ) {
		global $wpdb;

		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( 'topic' === $post_type ) {
			$post_id = $data['filter']['forum']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'topic' AND postmeta.meta_key= '_bbp_forum_id' ORDER BY posts.ID DESC LIMIT 1" );

			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='topic' AND postmeta.meta_key= '_bbp_forum_id' AND postmeta.meta_value=%s ORDER BY posts.ID DESC LIMIT 1", $post_id ) );

			}
		} elseif ( 'reply' === $post_type ) {
			$post_id = $data['filter']['topic']['value'];

			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'reply' AND postmeta.meta_key= '_bbp_topic_id' ORDER BY posts.ID DESC LIMIT 1" );

			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='reply' AND postmeta.meta_key= '_bbp_topic_id' AND postmeta.meta_value=%s ORDER BY posts.ID DESC LIMIT 1", $post_id ) );
			}
		}


		$response = [];
		if ( ! empty( $result ) ) {
			if ( 'bbpress_topic_created' === $trigger ) {
				$topic_id          = $result[0]->post_id;
				$forum_id          = $result[0]->meta_value;
				$topic             = get_the_title( $topic_id );
				$topic_link        = get_the_permalink( $topic_id );
				$topic_description = get_the_content( $topic_id );
				$topic_status      = get_post_status( $topic_id );

				$forum             = get_the_title( $forum_id );
				$forum_link        = get_the_permalink( $forum_id );
				$forum_description = get_the_content( $forum_id );
				$forum_status      = get_post_status( $forum_id );

				$forum = [
					'forum'             => $forum_id,
					'forum_title'       => $forum,
					'forum_link'        => $forum_link,
					'forum_description' => $forum_description,
					'forum_status'      => $forum_status,
				];

				$topic = [
					'topic_title'       => $topic,
					'topic_link'        => $topic_link,
					'topic_description' => $topic_description,
					'topic_status'      => $topic_status,
				];

				$user_id = $result[0]->post_author;
				$context = array_merge(
					WordPress::get_user_context( $user_id ),
					$forum,
					$topic
				);

				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$reply_id          = $result[0]->post_id;
				$topic_id          = $result[0]->meta_value;
				$forum_id          = get_post_meta( $topic_id, '_bbp_forum_id', true );
				$forum_id          = intval( '"' . $forum_id . '"' );
				$reply             = get_the_title( $reply_id );
				$reply_link        = get_the_permalink( $reply_id );
				$reply_description = get_the_content( $reply_id );
				$reply_status      = get_post_status( $reply_id );


				$topic             = get_the_title( $topic_id );
				$topic_link        = get_the_permalink( $topic_id );
				$topic_description = get_the_content( $topic_id );
				$topic_status      = get_post_status( $topic_id );

				$forum             = get_the_title( $forum_id );
				$forum_link        = get_the_permalink( $forum_id );
				$forum_description = get_the_content( null, false, $forum_id );
				$forum_status      = get_post_status( $forum_id );

				$forum = [
					'forum'             => $forum_id,
					'forum_title'       => $forum,
					'forum_link'        => $forum_link,
					'forum_description' => $forum_description,
					'forum_status'      => $forum_status,
				];

				$topic = [
					'topic_title'       => $topic,
					'topic_link'        => $topic_link,
					'topic_description' => $topic_description,
					'topic_status'      => $topic_status,
				];

				$reply   = [
					'reply_title'       => $reply,
					'reply_link'        => $reply_link,
					'reply_description' => $reply_description,
					'reply_status'      => $reply_status,
				];
				$user_id = $result[0]->post_author;
				$context = array_merge(
					WordPress::get_user_context( $user_id ),
					$forum,
					$topic, 
					$reply
				);

				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			}       
		}

		return $response;
	}

	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_happyform_list( $data ) {
		if ( ! function_exists( 'happyforms_get_form_controller' ) ) {
			return;
		}

		$form_controller = happyforms_get_form_controller();

		$forms   = $form_controller->do_get();
		$options = [];
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form['post_title'],
					'value' => $form['ID'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress Course List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_mpc_lessons_list( $data ) {
		if ( ! class_exists( '\memberpress\courses\models\Lesson' ) ) {
			return;
		}
		global $wpdb;
		$options   = [];
		$course_id = $data['dynamic'];
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}mpcs_sections WHERE course_id =%s", $course_id ) );
		$sections  = [];
		foreach ( $result as $rec ) {
			$sections[] = [
				'id'    => $rec->id,
				'title' => $rec->title,
			];
		}
		if ( is_array( $sections ) && count( $sections ) > 0 ) {
			foreach ( $sections as $section ) {
				$post_types_string = \memberpress\courses\models\Lesson::lesson_cpts();
				$post_types_string = implode( "','", $post_types_string );

				$query = $wpdb->prepare(
					"SELECT * FROM {$wpdb->posts} AS p
					JOIN {$wpdb->postmeta} AS pm
						ON p.ID = pm.post_id
						AND pm.meta_key = %s
						AND pm.meta_value = %s
					JOIN {$wpdb->postmeta} AS pm_order
						ON p.ID = pm_order.post_id
						AND pm_order.meta_key = %s
					WHERE p.post_type in ( %s ) AND p.post_status <> 'trash'
					ORDER BY pm_order.meta_value * 1",
					models\Lesson::$section_id_str,
					$section['id'],
					models\Lesson::$lesson_order_str,
					stripcslashes( $post_types_string )
				);

				$db_lessons = $wpdb->get_results( stripcslashes( $query ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				foreach ( $db_lessons as $lesson ) {
					$options[] = [
						'label' => $section['title'] . '->' . $lesson->post_title,
						'value' => $lesson->ID,
					];
				}           
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress Course List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_rank_type_list( $data ) {
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT ID, post_name, post_title, post_type
										FROM $wpdb->posts
										WHERE post_type LIKE 'rank-type' AND post_status = 'publish' ORDER BY post_title ASC"
		);

		$posts_count = count( $posts );

		$options = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->post_name,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MPC last data.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mpc_last_data( $data ) {
		global $wpdb;
		$trigger     = $data['search_term'];
		$course_data = [];
		$lesson_data = [];
		$context     = [];

		if ( 'mpc_course_completed' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course]']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID= %s ORDER BY id DESC LIMIT 1", $course_id ) );
			} else {
				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts where post_type = 'mpcs-course' ORDER BY id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data = [
					'course_id'                 => $course->ID,
					'course_title'              => get_the_title( $course_id ),
					'course_url'                => get_permalink( $course_id ),
					'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
					'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
				];
			}
			$user_progress = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mpcs_user_progress WHERE course_id=%s", $course_id ) );
			if ( ! empty( $user_progress ) ) {
				$context['response_type']  = 'live';
				$context['pluggable_data'] = array_merge( WordPress::get_user_context( $user_progress->user_id ), $course_data, $lesson_data );
			} else {
				$sample_data = '{"pluggable_data":{"wp_user_id":1,"user_login":"suretriggers","display_name":"suretriggers","user_firstname":"suretriggers","user_lastname":"suretriggers","user_email":"hello@suretriggers.io","user_role":["administrator","subscriber","tutor_instructor","bbp_keymaster"],"course_id":617,"course_title":"Course One","course_url":"https:\/\/connector.com\/courses\/course-one\/","course_featured_image_id":"","course_featured_image_url":false}
				,"response_type":"sample"}  ';
				$context     = json_decode( $sample_data, true );
			}
		} elseif ( 'mpc_lesson_completed' === $trigger ) {
			$lesson_id = (int) ( isset( $data['filter']['lesson']['value'] ) ? $data['filter']['lesson']['value'] : '-1' );
			$course_id = (int) $data['filter']['course']['value'];
			if ( $lesson_id > 0 ) {

				$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID= %s ORDER BY id DESC LIMIT 1", $lesson_id ) );
			} else {
				$lesson = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts where post_type = 'mpcs-lesson' ORDER BY id DESC LIMIT 1" );
			}

			if ( ! empty( $lesson ) ) {
				$lesson_data = [
					'lesson_id'                 => $lesson->ID,
					'lesson_title'              => get_the_title( $lesson_id ),
					'lesson_url'                => get_permalink( $lesson_id ),
					'lesson_featured_image_id'  => get_post_meta( $lesson_id, '_thumbnail_id', true ),
					'lesson_featured_image_url' => get_the_post_thumbnail_url( $lesson_id ),
				];

				$lesson_section_id = get_post_meta( $lesson->ID, '_mpcs_lesson_section_id', true );

				$section = $wpdb->get_row( $wpdb->prepare( "SELECT course_id FROM {$wpdb->prefix}mpcs_sections WHERE ID= %s", $lesson_section_id ) );

				$course_data = [
					'course_id'                 => $course_id,
					'course_title'              => get_the_title( $course_id ),
					'course_url'                => get_permalink( $course_id ),
					'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
					'course_featured_image_url' => get_the_post_thumbnail_url( $section->course_id ),
				];
			}

			$user_progress = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mpcs_user_progress WHERE lesson_id= %s AND course_id=%s", $lesson_id, $course_id ) );
			if ( ! empty( $user_progress ) ) {
				$context['response_type']  = 'live';
				$context['pluggable_data'] = array_merge( WordPress::get_user_context( $user_progress->user_id ), $course_data, $lesson_data );
			} else {
				$sample_data = '{"pluggable_data":{"wp_user_id":1,"user_login":"suretriggers","display_name":"suretriggers","user_firstname":"suretriggers","user_lastname":"dev","user_email":"hello@suretriggers.com","user_role":["administrator","subscriber","tutor_instructor","bbp_keymaster"],"lesson_id":620,"lesson_title":"second section","lesson_url":"https:\/\/connector.com\/courses\/course-one\/lessons\/second-section\/","lesson_featured_image_id":"","lesson_featured_image_url":false,"course_id":617,"course_title":"Course One","course_url":"https:\/\/connector.com\/courses\/course-one\/","course_featured_image_id":"","course_featured_image_url":false},"response_type":"sample"}';
				$context     = json_decode( $sample_data, true );
			}
		}


		return $context;
	}

	/** Get GamiPress Rank List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_rank_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => $data['dynamic']['rank_type'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$rank_type = get_posts( $args );
		
		$count_args = [
			'post_type'      => $data['dynamic']['rank_type'],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$rank_posts      = get_posts( $count_args );
		$rank_type_count = count( $rank_posts );

		$options = [];
		if ( $rank_type ) {
			foreach ( $rank_type as $rank ) {
				$options[] = [
					'label' => $rank->post_title,
					'value' => $rank->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $rank_type_count > $limit && $rank_type_count > $offset,
		];
	}

	/**
	 * Get GamiPress PointType List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_point_type_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'points-type',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$point_type = get_posts( $args );

		$count_args = [
			'post_type'      => 'points-type',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$count_point_type = get_posts( $count_args );
		$point_type_count = count( $count_point_type );

		$options = [];
		if ( $point_type ) {
			foreach ( $point_type as $point ) {
				$options[] = [
					'label' => $point->post_title,
					'value' => $point->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $point_type_count > $limit && $point_type_count > $offset,
		];
	}

	/**
	 * Get GamiPress AchievementType List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_achivement_type_list( $data ) {
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT ID, post_name, post_title, post_type
			FROM $wpdb->posts
			WHERE post_type LIKE 'achievement-type' AND post_status = 'publish' ORDER BY post_title ASC"
		);

		$posts_count = count( $posts );

		$options = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->post_name,
				];
			}
		}

		$options[] = [
			'label' => 'Points awards',
			'value' => 'points-award',
		];
		$options[] = [
			'label' => 'Step',
			'value' => 'step',
		];
		$options[] = [
			'label' => 'Rank requirement',
			'value' => 'rank-requirement',
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get GamiPress Award List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_award_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => $data['dynamic']['achivement_type'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$award_type = get_posts( $args );
		$count_args = [
			'post_type'      => $data['dynamic']['achivement_type'],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$count_award_type = get_posts( $count_args );
		$award_type_count = count( $count_award_type );
		$options          = [];
		if ( $award_type ) {
			foreach ( $award_type as $award ) {
				$options[] = [
					'label' => $award->post_title,
					'value' => $award->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $award_type_count > $limit && $award_type_count > $offset,
		];
	}

	/**
	 * Get Woocommerce Subscription Product List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_subscription_product_list( $data ) {
		global $wpdb;

		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
	LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
								WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
									AND posts.post_type = %s
									AND posts.post_status = %s
								UNION ALL
								SELECT ID, post_title FROM $wpdb->posts
								WHERE post_type = %s
									AND post_status = %s
			ORDER BY post_title",
				'product',
				'publish',
				'shop_subscription',
				'publish'
			)
		);

		$options = [];
		if ( $subscriptions ) {
			foreach ( $subscriptions as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Woocommerce Subscriptions Variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_variable_subscription_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		if ( ! function_exists( 'wc_get_products' ) ) {
			return;
		}
		$subscription_products = wc_get_products(
			[
				'type'           => [ 'variable-subscription' ],
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);

		$subscription_products_count = count( (array) $subscription_products );

		$options = [];
		if ( $subscription_products ) {
			foreach ( (array) $subscription_products as $product ) {
				$options[] = [
					'label' => $product->get_title(),
					'value' => $product->get_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $subscription_products_count > $limit && $subscription_products_count > $offset,
		];
	}

	/**
	 * Get Woocommerce Variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_variation_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'product_variation',
			'post_parent'    => $data['dynamic']['variable_subscription'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$variation       = get_posts( $args );
		$variation_count = count( $variation );

		$options = [];
		if ( $variation ) {
			foreach ( $variation as $product ) {
				$options[] = [
					'label' => ! empty( $product->post_excerpt ) ? $product->post_excerpt : $product->post_title,
					'value' => $product->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $variation_count > $limit && $variation_count > $offset,
		];
	}

	/**
	 * Get Membership List.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_membership_list( $data ) {
		global $wpdb;

		$levels  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pmpro_membership_levels ORDER BY id ASC" );
		$options = [];
		if ( $levels ) {
			foreach ( $levels as $level ) {
				$options[] = [
					'label' => $level->name,
					'value' => $level->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**

	 * Get EventsManager last data.
	 * 
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_events_manager_data( $data ) {
		global $wpdb;
		$trigger = $data['search_term'];
		$context = [];

		$post_id = (int) ( isset( $data['filter']['post_id']['value'] ) ? $data['filter']['post_id']['value'] : '-1' );
		if ( 'em_user_register_in_event' === $trigger ) {
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
	
			}
		
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$location                  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				if ( ! empty( $location ) ) {
					$context['pluggable_data'] = array_merge( $context['pluggable_data'], json_decode( wp_json_encode( $location ), true ) );
				}
 
				$context['response_type'] = 'live';
			}
		} elseif ( 'em_user_unregister_from_event' === $trigger ) {
				
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status IN (2,3) AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
		
			}   
			
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				$context['response_type']  = 'live';
			}       
		} elseif ( 'em_user_booking_approved' === $trigger ) {
				
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status=1 AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status=1 AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
	
			}
	
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$location                  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				if ( ! empty( $location ) ) {
					$context['pluggable_data'] = array_merge( $context['pluggable_data'], json_decode( wp_json_encode( $location ), true ) );
				}

				$context['response_type'] = 'live';

			}
		} elseif ( 'em_user_registers_event_with_specific_ticket' === $trigger ) {
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			}
			if ( ! empty( $all_bookings ) ) {
				$ticket_id           = (int) $data['filter']['ticket_id']['value'];
				$all_ticket_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_tickets_bookings as b INNER JOIN {$wpdb->prefix}em_tickets as e ON b.ticket_id = e.ticket_id WHERE b.booking_id = %d AND e.ticket_id = %d ORDER BY b.ticket_booking_id DESC LIMIT 1", $all_bookings->booking_id, $ticket_id ) );
				if ( ! empty( $all_ticket_bookings ) ) {
					$user_id             = $all_bookings->person_id;
					$location            = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
					$bookings_str        = wp_json_encode( $all_bookings );
					$ticket_bookings_str = wp_json_encode( $all_ticket_bookings );
	
					$context['pluggable_data'] = array_merge(
						WordPress::get_user_context( $user_id )
					);
					if ( is_string( $bookings_str ) && is_string( $ticket_bookings_str ) ) {
						$bookings_arr        = json_decode( $bookings_str, true );
						$ticket_bookings_arr = json_decode( $ticket_bookings_str, true );
						if ( is_array( $bookings_arr ) && is_array( $ticket_bookings_arr ) ) {
							$context['pluggable_data'] = array_merge(
								$context['pluggable_data'],
								$bookings_arr,
								$ticket_bookings_arr
							);
						}
					}
					if ( ! empty( $location ) ) {
						$location_str = wp_json_encode( $location );
						if ( is_string( $location_str ) ) {
							$location_arr = json_decode( $location_str, true );
							if ( is_array( $location_arr ) ) {
								$context['pluggable_data'] = array_merge( $context['pluggable_data'], $location_arr );
							}
						}
					}
					$context['response_type'] = 'live';
				}
			}       
		}
		return $context;
	}

	/**
	 * Get Events Manager Events Ticket list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_em_event_tickets( $data ) {
		global $wpdb;

		$options = [];

		$event = $data['dynamic']['post_id'];

		$event_id = $wpdb->get_var( $wpdb->prepare( "SELECT event_id FROM {$wpdb->prefix}em_events WHERE post_id = %d", $event ) );
		$tickets  = $wpdb->get_results( $wpdb->prepare( "SELECT ticket_id,ticket_name FROM {$wpdb->prefix}em_tickets WHERE event_id = %d ORDER BY ticket_id", $event_id[0] ) );

		if ( $tickets ) {
			foreach ( $tickets as $ticket ) {
				$options[] = [
					'label' => $ticket->ticket_name,
					'value' => $ticket->ticket_id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**

	 * Get learnpress last data.
	 * 
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_learnpress_lms_last_data( $data ) {
		global $wpdb;
		$trigger     = $data['search_term'];
		$course_data = [];
		$lesson_data = [];
		$context     = [];
	

		if ( 'learnpress_course_completed' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && user_id>0 && status= 'finished' ORDER BY item_id DESC LIMIT 1", $course_id ) );
			} else {

				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_course' && user_id>0 && status= 'finished' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data               = array_merge( WordPress::get_user_context( $course->user_id ), LearnPress::get_lpc_course_context( $course->item_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $course_data;
			}       
		} elseif ( 'learnpress_lesson_completed' === $trigger ) {
			$lesson_id = (int) ( isset( $data['filter']['lesson']['value'] ) ? $data['filter']['lesson']['value'] : '-1' );
			if ( $lesson_id > 0 ) {

				$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && user_id>0 && status= 'completed' ORDER BY item_id DESC LIMIT 1", $lesson_id ) );
			} else {

				$lesson = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_lesson' && user_id>0 && status= 'completed' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $lesson ) ) {
				$lesson_data               = array_merge( WordPress::get_user_context( $lesson->user_id ), LearnPress::get_lpc_lesson_context( $lesson->item_id ), LearnPress::get_lpc_course_context( $lesson->ref_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $lesson_data;
			}
		} elseif ( 'learnpress_user_enrolled_in_course' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && status= 'enrolled' ORDER BY item_id DESC LIMIT 1", $course_id ) );
			} else {

				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_course' && user_id>0 && status= 'enrolled' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data               = array_merge( WordPress::get_user_context( $course->user_id ), LearnPress::get_lpc_course_context( $course->item_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $course_data;

			}       
		}

		return $context;
	}

	/**
	 * Get Woocommerce Memberships Plan List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_membership_plan_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'wc_membership_plan',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		];
		$loop = new WP_Query( $args );

		$plans       = (array) $loop->posts;
		$plans_count = wp_count_posts( 'wc_membership_plan' )->publish;

		$options = [];
		if ( ! empty( $plans ) ) {
			if ( is_array( $plans ) ) {
				foreach ( $plans as $plan_id ) {
					$options[] = [
						'label' => get_the_title( $plan_id ),
						'value' => $plan_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $plans_count > $limit && $plans_count > $offset,
		];
	}

		/**
		 * Get BuddyPress Private group.
		 *
		 * @param array $data data.
		 *
		 * @return array|void
		 */
	public function search_bp_private_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status = 'private'" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress Public group.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_public_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status = 'public'" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress group.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress field.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_field_list( $data ) {
		global $wpdb;

		$base_group_id = 1;
		if ( function_exists( 'bp_xprofile_base_group_id' ) ) {
			$base_group_id = bp_xprofile_base_group_id();
		}

		$xprofile_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_xprofile_fields WHERE parent_id = 0 AND group_id = %d ORDER BY field_order ASC", $base_group_id ) );

		$options = [];
		if ( ! empty( $xprofile_fields ) ) {
			foreach ( $xprofile_fields as $xprofile_field ) {
				$options[] = [
					'label' => $xprofile_field->name,
					'value' => $xprofile_field->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress member type.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_member_type_list( $data ) {
		$options = [];      
		if ( function_exists( 'bp_get_member_types' ) ) {
			$types = bp_get_member_types( [] );
			if ( $types ) {
				foreach ( $types as $key => $type ) {
					$options[] = [
						'label' => $type,
						'value' => $key,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for WP All Import.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_wp_all_import_last_data( $data ) {
		global $wpdb;
		$post_type = $data['filter']['post_type']['value'];
		$trigger   = $data['search_term'];

		if ( 'wp_all_import_post_type_imported' === $trigger ) {
			if ( -1 == $post_type ) {
				$imports  = $wpdb->get_row( "SELECT post_id FROM {$wpdb->prefix}pmxi_posts ORDER BY id DESC LIMIT 1", ARRAY_A );
				$posts[0] = $imports['post_id'];        
			} else {
				$imports = $wpdb->get_results( "SELECT post_id FROM {$wpdb->prefix}pmxi_posts", ARRAY_A );
				$imports = array_column( $imports, 'post_id' );
				$args    = [
					'posts_per_page' => 1,
					'post_type'      => $post_type,
					'post__in'       => $imports,
				];
				$posts   = get_posts( $args );        
			}
		} elseif ( 'wp_all_import_completed' === $trigger ) {
			$imports = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pmxi_imports WHERE failed = 0 ORDER BY id DESC LIMIT 1", ARRAY_A );    
		} elseif ( 'wp_all_import_failed' === $trigger ) {
			$imports = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pmxi_imports WHERE failed = 1 ORDER BY id DESC LIMIT 1", ARRAY_A );
		}

		if ( 'wp_all_import_post_type_imported' === $trigger && empty( $imports ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"ID": 1,"post_author": "1","post_date": "2023-07-12 06:31:35","post_date_gmt": "2023-07-12 06:31:35","post_content": "","post_title": "Test","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "open","post_password": "","post_name": "test","to_ping": "","pinged": "","post_modified": "2023-07-12 06:31:35","post_modified_gmt": "2023-07-12 06:31:35","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/example.com\/test\/","menu_order": 0,"post_type": "post","post_mime_type": "","comment_count": "0","filter": "raw"}}', true );
			return $context;
		} elseif ( empty( $imports ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id": "1","parent_import_id": "0","name": "demowpinstawpxyz.WordPress.2023_07_12.xml","friendly_name": "","type": "upload","feed_type": "","path": "\/wpallimport\/uploads\/ee8816eebf7a373454cdd1189c831241\/demowpinstawpxyz.WordPress.2023_07_12.xml","xpath": "\/rss","registered_on": "2023-07-12 05:10:29","root_element": "rss","processing": "0","executing": "0","triggered": "0","queue_chunk_number": "0","first_import": "2023-07-12 05:09:41","count": "1","imported": "0","created": "0","updated": "0","skipped": "1","deleted": "0","changed_missing": "0","canceled": "0","canceled_on": "0000-00-00 00:00:00","failed": "0","failed_on": "0000-00-00 00:00:00","settings_update_on": "0000-00-00 00:00:00","last_activity": "2023-07-12 05:10:24","iteration": "1"}}', true );
			return $context;
		}

		$context['response_type'] = 'live';
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = WordPress::get_post_context( $posts[0] );
		} else {
			$context['pluggable_data'] = $imports;
		}
		
		return $context;
	}

	/**
	 * Get Wp Simple Pay Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_simple_pay_forms( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forms = get_posts(
			[
				'post_type'      => 'simple-pay',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
			]
		);

		$forms_count = wp_count_posts( 'simple-pay' )->publish;

		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form_id ) {
				if ( function_exists( 'simpay_get_form' ) ) {
					$form      = simpay_get_form( $form_id );
					$options[] = [
						'label' => null !== get_the_title( $form_id ) ? $form->company_name : get_the_title( $form_id ),
						'value' => $form_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $forms_count > $limit && $forms_count > $offset,
		];
	}

	/**
	 * Get Post list as per post type for metabox.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_posts_list( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => $data['dynamic'],
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
			]
		);

		$all_posts = get_posts(
			[
				'post_type'      => $data['dynamic'],
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);

		$posts_count = count( $all_posts );

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$title     = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
				$options[] = [
					'label' => $title,
					'value' => $post,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Metabox Custom box in Post list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_field_list( $data ) {
		
		if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
			return [];
		}

		$options = [];

		$metabox_fields = (array) rwmb_get_object_fields( $data['dynamic'] );

		foreach ( $metabox_fields as $metabox_field ) {

			if ( ! empty( $metabox_field['id'] ) && ! empty( $metabox_field['name'] ) ) {

				$options[] = [
					'label' => $metabox_field['name'],
					'value' => $metabox_field['id'],
				];

			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Metabox Custom box user list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_user_field_list( $data ) {
		
		if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
			return [];
		}

		$options = [];

		$metabox_fields = (array) rwmb_get_object_fields( null, 'user' );

		foreach ( $metabox_fields as $metabox_field ) {

			if ( ! empty( $metabox_field['id'] ) && ! empty( $metabox_field['name'] ) ) {

				$options[] = [
					'label' => $metabox_field['name'],
					'value' => $metabox_field['id'],
				];

			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data for MetaBox.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_meta_box_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];
		$post      = $data['filter']['wp_post']['value'];

		if ( -1 === $field ) {
			if ( function_exists( 'rwmb_get_object_fields' ) ) {
				$metaboxes = rwmb_get_object_fields( $post_type );
				
				if ( ! empty( $metaboxes ) ) {
					$random_key = array_rand( $metaboxes );
					$field      = $random_key;
				} else {
					$result = '';
				}
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}

		if ( function_exists( 'rwmb_meta' ) ) {
			$result = rwmb_meta( $field, '', $post );
		}

		$response = [];
		if ( ! empty( $result ) ) {
			$response['pluggable_data'] = array_merge( [ $field => $result ], WordPress::get_post_context( $post ) );
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"custom_description": "custom message", "ID": 1, "post_author": "1", "post_date": "2023-05-31 13:26:24", "post_date_gmt": "2023-05-31 13:26:24", "post_content": "", "post_title": "Test", "post_excerpt": "", "post_status": "publish", "comment_status": "open", "ping_status": "open", "post_password": "", "post_name": "test", "to_ping": "", "pinged": "", "post_modified": "2023-08-17 09:15:56", "post_modified_gmt": "2023-08-17 09:15:56", "post_content_filtered": "", "post_parent": 0, "guid": "https:\/\/example.com\/?p=1", "menu_order": 0, "post_type": "post", "post_mime_type": "", "comment_count": "2", "filter": "raw"}}', true );
		}

		return $response;
	}

		/**
		 * Search Last Updated User Field Data MetaBox.
		 *
		 * @param array $data data.
		 * @return array
		 */
	public function search_user_meta_box_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		if ( -1 === $field ) {
			if ( function_exists( 'rwmb_get_object_fields' ) ) {
				$metabox_fields = (array) rwmb_get_object_fields( null, 'user' );
				
				if ( ! empty( $metabox_fields ) ) {
					$random_key = array_rand( $metabox_fields );
					$field      = $random_key;
				} else {
					$result = '';
				}
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}

		$users = get_users(
			[
				'fields'   => 'ID',
				'meta_key' => $field,
			]
		);

		if ( ! empty( $users ) ) {
			$user_random_key = array_rand( $users );
			$user_id         = $user_random_key;
			if ( function_exists( 'rwmb_get_value' ) ) {
				$result = rwmb_get_value( $field, [ 'object_type' => 'user' ], $users[ $user_id ] );
			}

			$response = [];
			if ( ! empty( $result ) ) {
				$context                    = [
					'field_id' => $field,
					$field     => $result,
					'user'     => WordPress::get_user_context( $users[ $user_id ] ),
				];
				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$response = json_decode(
					'{
					"response_type": "sample",
					"pluggable_data": {
						"field_id": "gender",
						"user": {
							"wp_user_id": 114,
							"user_login": "test",
							"display_name": "test",
							"user_firstname": "test",
							"user_lastname": "test",
							"user_email": "test@test.com",
							"user_role": [ "subscriber" ]
						}
					}
				}',
					true 
				);
			}
		} else {
			$response = json_decode(
				'{
				"response_type": "sample",
				"pluggable_data": {
					"field_id": "gender",
					"user": {
						"wp_user_id": 114,
						"user_login": "test",
						"display_name": "test",
						"user_firstname": "test",
						"user_lastname": "test",
						"user_email": "test@test.com",
						"user_role": [ "subscriber" ]
					}
				}
			}',
				true 
			);
		}

		return $response;
	}

	/**
	 * Search forms of Pie Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_list( $data ) {
		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pollsq' ) ) ) {

			$results = $wpdb->get_results( 'SELECT pollq_id, pollq_question FROM ' . $wpdb->prefix . 'pollsq WHERE pollq_active = 1' );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->pollq_question,
						'value' => $result->pollq_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search answers of WP-Polls questions.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_answers( $data ) {
		global $wpdb;

		$options = [];
		$poll_id = $data['dynamic'];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pollsa' ) ) ) {

			if ( '-1' !== $poll_id ) {
				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT polla_aid, polla_answers FROM ' . $wpdb->prefix . 'pollsa WHERE polla_qid = %d', $poll_id ) );
			} else {
				$results = $wpdb->get_results( 'SELECT polla_aid, polla_answers FROM ' . $wpdb->prefix . 'pollsa' );
			}

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->polla_answers,
						'value' => $result->polla_aid,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$poll = [
			'poll_id'            => 1,
			'question'           => 'Which skills are you interested to learn?',
			'answers'            => 'Web Development, Graphic Designing, Content Writing, Digital Marketing',
			'start_date'         => '2023-08-29 17:19:13',
			'end_date'           => 'Not set',
			'selected_answers'   => 'Content Writing, Web Development',
			'selected_answer_id' => 2,
		];

		$poll_data = $wpdb->get_row( "SELECT pollip_qid AS poll_id, pollip_aid AS answer_id FROM {$wpdb->prefix}pollsip ORDER BY pollip_id DESC LIMIT 1" );

		if ( ! empty( $poll_data ) ) {
			$poll                       = WpPolls::get_poll_context( (string) $poll_data->answer_id, (int) $poll_data->poll_id );
			$poll['selected_answer_id'] = (int) $poll_data->answer_id;

			$context['response_type'] = 'live';
		}

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( 'poll_submitted' === $term ) {
			unset( $poll['selected_answer_id'] );
		}

		$context['pluggable_data'] = $poll;

		return $context;
	}

	/**
	 * Get ACF Custom fields list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_acf_post_field_list( $data ) {
		
		$post_id            = $data['dynamic']['wp_post'];
		$selected_post_type = $data['dynamic']['wp_post_type'];

		if ( -1 === $post_id ) {
			$args    = [
				'numberposts' => 1,
				'fields'      => 'ids',
				'orderby'     => 'rand',
				'post_type'   => $selected_post_type,
			];
			$posts   = get_posts( $args );
			$post_id = $posts[0];
		}

		$args = [
			'post_id' => $post_id,
		];
		if ( ! is_numeric( $post_id ) ) {
			$args = [
				'post_type' => $post_id,
			];
		}
		$options = [];
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups_collection = acf_get_field_groups( $args );
			foreach ( $field_groups_collection as $field_group ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$field_groups[] = acf_get_fields( $field_group['key'] );
				}
			}
	
			if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {
				foreach ( $field_groups as $field_groups ) {
					foreach ( $field_groups as $field_group ) {
						$options[] = [
							'value' => $field_group['name'],
							'label' => $field_group['label'],
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get ACF Custom fields list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_acf_user_field_list( $data ) {
		
		if ( ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}
		$groups_user_form = [];
		$options          = [];
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups();
			foreach ( $field_groups as $group ) {
				if ( ! empty( $group['location'] ) ) {
					foreach ( $group['location'] as $locations ) {
						foreach ( $locations as $location ) {
							if ( 'user_form' === $location['param'] || 'user_role' === $location['param'] || 'current_user' === $location['param'] || 'current_user_role' === $location['param'] ) {
								$groups_user_form[] = $group;
							}
						}
					}
				}
			}

			if ( empty( $groups_user_form ) ) {
				return [];
			}

			$key_values   = array_map(
				function ( $item ) {
					return $item['key'];
				},
				$groups_user_form
			);
			$unique_keys  = array_unique( $key_values );
			$unique_array = array_intersect_key( $groups_user_form, $unique_keys );

			foreach ( $unique_array as $group ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$group_fields = acf_get_fields( $group['key'] );    
				}
				if ( ! empty( $group_fields ) ) {
					foreach ( $group_fields as $field ) {
						$options[] = [
							'value' => $field['name'],
							'label' => $field['label'],
						];
	
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get ACF Custom fields list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_acf_options_field_list( $data ) {
		
		if ( ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}
		$groups_options_form = [];
		$options             = [];
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups();
			foreach ( $field_groups as $group ) {
				if ( ! empty( $group['location'] ) ) {
					foreach ( $group['location'] as $locations ) {
						foreach ( $locations as $location ) {
							if ( 'options_page' === $location['param'] ) {
								$groups_options_form[] = $group;
							}
						}
					}
				}
			}
			if ( empty( $groups_options_form ) ) {
				return [];
			}
			$key_values   = array_map(
				function ( $item ) {
					return $item['key'];
				},
				$groups_options_form
			);
			$unique_keys  = array_unique( $key_values );
			$unique_array = array_intersect_key( $groups_options_form, $unique_keys );
			foreach ( $unique_array as $group ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$group_fields = acf_get_fields( $group['key'] );    
				}
				if ( ! empty( $group_fields ) ) {
					foreach ( $group_fields as $field ) {
						$options[] = [
							'value' => $field['name'],
							'label' => $field['label'],
						];
	
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data for ACF.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_acf_post_field_data( $data ) {
		$context = [];

		$field = ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];
		$post      = $data['filter']['wp_post']['value'];

		if ( -1 === $post ) {
			$args  = [
				'numberposts' => 1,
				'fields'      => 'ids',
				'orderby'     => 'rand',
				'post_type'   => $post_type,
			];
			$posts = get_posts( $args );
			$post  = $posts[0];
		}
		if ( -1 === $field ) {
			$args = [
				'post_id' => $post,
			];
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$field_groups_collection = acf_get_field_groups( $args );   
			}
			if ( ! empty( $field_groups_collection ) ) {
				foreach ( $field_groups_collection as $field_group ) {
					if ( function_exists( 'acf_get_fields' ) ) {
						$field_groups[] = acf_get_fields( $field_group['key'] );
					}
				}
			}
			$fields = [];
			if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {
				foreach ( $field_groups as $field_groups ) {
					$fields[] = $field_groups;
				}
			}
			if ( ! empty( $fields ) ) {
				$random_key = array_rand( $fields[0] );
				$field_key  = $fields[0][ $random_key ];
				$field      = $field_key['name'];
			} else {
				$result = '';
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}
		if ( function_exists( ( 'get_field' ) ) ) {
			$result = get_field( $field, $post );
		}
		
		$response = [];
		if ( ! empty( $result ) ) {
			$post_fields = [];
			if ( function_exists( 'get_fields' ) ) {
				$post_fields = get_fields( $post );
			}
			$response['pluggable_data'] = array_merge( [ $field => $result ], [ 'field_id' => $field ], [ 'post_fields' => $post_fields ], [ 'post' => WordPress::get_post_context( $post ) ], [ 'wp_post' => $post ], [ 'wp_post_type' => get_post_type( $post ) ] );
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"custom_description": "custom message", "ID": 1, "post_author": "1", "post_date": "2023-05-31 13:26:24", "post_date_gmt": "2023-05-31 13:26:24", "post_content": "", "post_title": "Test", "post_excerpt": "", "post_status": "publish", "comment_status": "open", "ping_status": "open", "post_password": "", "post_name": "test", "to_ping": "", "pinged": "", "post_modified": "2023-08-17 09:15:56", "post_modified_gmt": "2023-08-17 09:15:56", "post_content_filtered": "", "post_parent": 0, "guid": "https:\/\/example.com\/?p=1", "menu_order": 0, "post_type": "post", "post_mime_type": "", "comment_count": "2", "filter": "raw"}}', true );
		}

		return $response;
	}

	/**
	 * Search Last Updated User Field Data ACF.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_acf_user_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		if ( -1 === $field ) {
			$groups_user_form = [];
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$field_groups = acf_get_field_groups();
			}
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $group ) {
					if ( ! empty( $group['location'] ) ) {
						foreach ( $group['location'] as $locations ) {
							foreach ( $locations as $location ) {
								if ( 'user_form' === $location['param'] || 'user_role' === $location['param'] || 'current_user' === $location['param'] || 'current_user_role' === $location['param'] ) {
									$groups_user_form[] = $group;
								}
							}
						}
					}
				}
				$field_groups = $groups_user_form;
			}
			if ( empty( $field_groups ) ) {
				$result = '';
			}
			$fields = [];
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $group ) {
					if ( function_exists( 'acf_get_fields' ) ) {
						$group_fields = acf_get_fields( $group['key'] );
					}
					if ( ! empty( $group_fields ) ) {
						foreach ( $group_fields as $field ) {
							$fields[] = $group_fields;          
						}
					}
				}
			}
			if ( ! empty( $fields ) ) {
				$random_key = array_rand( $fields );
				$field      = $random_key;
			} else {
				$result = '';
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}
		$users = get_users(
			[
				'fields'   => 'ID',
				'meta_key' => $field,
			]
		);

		if ( ! empty( $users ) ) {
			$user_random_key = array_rand( $users );
			$user_id         = $user_random_key;
			if ( function_exists( 'get_field' ) ) {
				$result = get_field( $field, 'user_' . $users[ $user_id ] );
			}
			$response = [];
			if ( ! empty( $result ) ) {
				$context                    = [
					'field_id' => $field,
					$field     => $result,
					'user'     => WordPress::get_user_context( $users[ $user_id ] ),
				];
				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$response = json_decode(
					'{
					"response_type": "sample",
					"pluggable_data": {
						"field_id": "gender",
						"user": {
							"wp_user_id": 114,
							"user_login": "test",
							"display_name": "test",
							"user_firstname": "test",
							"user_lastname": "test",
							"user_email": "test@test.com",
							"user_role": [ "subscriber" ]
						}
					}
				}',
					true 
				);
			}
		} else {
			$response = json_decode(
				'{
				"response_type": "sample",
				"pluggable_data": {
					"field_id": "gender",
					"user": {
						"wp_user_id": 114,
						"user_login": "test",
						"display_name": "test",
						"user_firstname": "test",
						"user_lastname": "test",
						"user_email": "test@test.com",
						"user_role": [ "subscriber" ]
					}
				}
			}',
				true 
			);
		}

		return $response;
	}

	/**
	 * Search Last Updated Options Field Data ACF.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_acf_options_field_data( $data ) {
		global $wpdb;
		$context = [];
		$field   = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		if ( -1 === $field ) {
			$groups_options_form = [];
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$field_groups = acf_get_field_groups();
			}
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $group ) {
					if ( ! empty( $group['location'] ) ) {
						foreach ( $group['location'] as $locations ) {
							foreach ( $locations as $location ) {
								if ( 'options_page' === $location['param'] ) {
									$groups_options_form[] = $group;
								}
							}
						}
					}
				}
			}
			if ( empty( $groups_options_form ) ) {
				$result = '';
			}
			$key_values   = array_map(
				function ( $item ) {
					return $item['key'];
				},
				$groups_options_form
			);
			$unique_keys  = array_unique( $key_values );
			$unique_array = array_intersect_key( $groups_options_form, $unique_keys );
			$fields       = [];
			if ( ! empty( $unique_array ) ) {
				foreach ( $unique_array as $group ) {
					if ( function_exists( 'acf_get_fields' ) ) {
						$group_fields = acf_get_fields( $group['key'] );
					}
					if ( ! empty( $group_fields ) ) {
						foreach ( $group_fields as $field ) {
							$fields[] = $group_fields;          
						}
					}
				}
			}
			if ( ! empty( $fields ) ) {
				$random_key = array_rand( $fields );
				$field      = $random_key;
			} else {
				$result = '';
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}
		if ( function_exists( 'get_field' ) ) {
			$option_value = get_field( $field, 'option' );
		}
		if ( ! empty( $option_value ) ) {
			if ( function_exists( 'acf_get_field' ) ) {
				$options_fields = acf_get_field( $field );
				if ( function_exists( 'acf_maybe_get' ) ) {
					$options_page = acf_maybe_get( $options_fields, 'parent' );
				}
			}
			$context                    = [
				'field_id' => $field,
				$field     => $option_value,
			];
			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';
		} else {
			$response = json_decode(
				'{
				"response_type": "sample",
				"pluggable_data": {
					"field_id": "optionpage",
					"optionpage": "newoption"
				}
			}',
				true 
			);
		}
		return $response;
	}

	/**
	 * Get WP Fusion Tags list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_fusion_tag_list( $data ) {
		
		if ( ! function_exists( 'wp_fusion' ) ) {
			return [];
		}

		$options = [];
		$tags    = wp_fusion()->settings->get( 'available_tags' );

		if ( $tags ) {
			foreach ( $tags as $t_id => $tag ) {
				if ( is_array( $tag ) && isset( $tag['label'] ) ) {
					$options[] = [
						'value' => $t_id,
						'label' => $tag['label'],
					];
				} else {
					$options[] = [
						'value' => $t_id,
						'label' => $tag,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get list of events for Modern Events Calendar.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_events_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args         = [
			'post_type'      => 'mec-events',
			'post_status'    => [ 'publish', 'private' ],
			'posts_per_page' => -1,
		];
		$loop         = new WP_Query( $args );
		$events_count = count( $loop->posts );

		$args = [
			'post_type'      => 'mec-events',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
		];

		$loop   = new WP_Query( $args );
		$events = $loop->posts;

		$options = [];
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				if ( isset( $event->ID ) ) {
					$options[] = [
						'label' => get_the_title( $event ),
						'value' => $event->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $events_count > $limit && $events_count > $offset,
		];
	}

	/**
	 * Search tickets of MEC events.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_event_tickets( $data ) {
		$options  = [];
		$event_id = $data['dynamic'];

		$event_tickets = get_post_meta( $event_id, 'mec_tickets', true );

		if ( ! empty( $event_tickets ) && is_array( $event_tickets ) ) {
			foreach ( $event_tickets as $ticket_id => $event_ticket ) {
				if ( isset( $event_ticket['name'] ) ) {
					$options[] = [
						'label' => $event_ticket['name'],
						'value' => $ticket_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$event = [
			'event_id'           => 1,
			'title'              => 'Sample Event',
			'description'        => 'Description of the sample event.',
			'categories'         => 'New, Sample',
			'start_date'         => 'September 13, 2023',
			'start_time'         => '8:00 AM',
			'end_date'           => 'September 13, 2023',
			'end_time'           => '11:00 AM',
			'location'           => 'City Hall',
			'organizer'          => 'John Doe',
			'cost'               => '5000',
			'featured_image_id'  => 1,
			'featured_image_url' => 'https://suretriggers.com/wp-content/uploads/2022/12/Screenshot_20221127_021332.png',
			'tickets'            => [
				[
					'id'          => 1,
					'name'        => 'Silver',
					'description' => 'Standard seat with reasonable price.',
					'price'       => '300',
					'price_label' => 'USD',
					'limit'       => '20',
				],
				[
					'id'          => 2,
					'name'        => 'Premium',
					'description' => 'VIP seat with high price.',
					'price'       => '500',
					'price_label' => 'USD',
					'limit'       => '10',
				],
			],
			'attendees'          => [
				[
					'id'    => 1,
					'email' => 'johndoe@test.com',
					'name'  => 'John Doe',
				],
				[
					'id'    => 2,
					'email' => 'adamsmith@test.com',
					'name'  => 'Adam Smith',
				],
			],
			'booking'            => [
				'title'               => 'johndoe@test.com - John Doe',
				'transaction_id'      => 'RSH59404',
				'amount_payable'      => '800',
				'price'               => '800',
				'time'                => '2023-09-07 06:40:32',
				'payment_gateway'     => 'Manual Pay',
				'confirmation_status' => 'Pending',
				'verification_status' => 'Verified',
				'attendees_count'     => 2,
			],
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		$where = '';

		if ( 'cancelled' === $term ) {
			$where = 'WHERE verified = -1';
		} elseif ( 'confirmed' === $term ) {
			$where = 'WHERE confirmed = 1';
		} elseif ( 'pending' === $term ) {
			$where = 'WHERE confirmed = 0';
		}

		$event_id = (int) ( isset( $data['filter']['event_id']['value'] ) ? $data['filter']['event_id']['value'] : '-1' );

		if ( -1 !== $event_id ) {
			if ( ! empty( $where ) ) {
				$where .= ' AND event_id = ' . $event_id;
			} else {
				$where = 'WHERE event_id = ' . $event_id;
			}
		}

		$event_data = $wpdb->get_row( "SELECT booking_id FROM {$wpdb->prefix}mec_bookings $where ORDER BY id DESC LIMIT 1" ); // @phpcs:ignore

		if ( ! empty( $event_data ) ) {
			$event                    = ModernEventsCalendar::get_event_context( (int) $event_data->booking_id );
			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = $event;

		return $context;
	}

	/**
	 * Get form list Contact Form 7.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_contact_form7_list( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$all_posts = get_posts(
			[
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			]
		);

		$posts_count = count( $all_posts );

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Thrive Leads form list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_thrive_leads_forms_list( $data ) {
		$options = [];

		$lg_ids = get_posts(
			[
				'post_type'      => '_tcb_form_settings',
				'fields'         => 'id=>parent',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			]
		);

		if ( function_exists( 'tve_leads_get_form_variations' ) ) {
			foreach ( $lg_ids as $lg_id => $lg_parent ) {
				$variations = tve_leads_get_form_variations( $lg_parent );
				foreach ( $variations as $variation ) {
					$options[] = [
						'label' => $variation['post_title'],
						'value' => $lg_parent,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get list for Woocommerce Subscriptions
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wc_subscription_variation_products( $data ) {
		$options = [];

		global $wpdb;

		if ( ! function_exists( 'wc_get_product' ) ) {
			return [];
		}
		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
	LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
								WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
									AND posts.post_type = %s
									AND posts.post_status = %s
								UNION ALL
								SELECT ID, post_title FROM $wpdb->posts
								WHERE post_type = %s
									AND post_status = %s
			ORDER BY post_title",
				'product',
				'publish',
				'shop_subscription',
				'publish'
			)
		);

		if ( $subscriptions ) {
			foreach ( $subscriptions as $product ) {
				$options[] = [
					'label' => $product->post_title . ' (#' . $product->ID . ')',
					'value' => (int) $product->ID,
				];
				$product_s = wc_get_product( $product->ID );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				if ( 'variable-subscription' == $product_s->product_type ) {
					$args = [
						'post_type'      => 'product_variation',
						'post_parent'    => $product->ID,
						'posts_per_page' => -1,
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					];

					$variations = get_posts( $args );

					foreach ( $variations as $var ) {
						$options[] = [
							'label' => $var->post_title . ' (#' . $var->ID . ')',
							'value' => $var->ID,
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WS Forms form list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ws_forms_list( $data ) {
		$options = [];
		global $wpdb;

		$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wsf_form", 'ARRAY_A' );    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $forms as $form ) {
			$options[] = [
				'label' => $form['label'],
				'value' => $form['id'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Learndash Achievement list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ld_achievements_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'ld-achievement',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => [ 'publish' ],
			]
		);

		$posts_count = wp_count_posts( 'ld-achievement' )->publish;

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Advanced Ads list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ads_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'advanced_ads',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => [ 'publish', 'draft' ],
			]
		);

		$posts_count = wp_count_posts( 'advanced_ads' )->publish;

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Advanced Ads pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_ad_last_data( $data ) {
		$context = [];
		$args    = [
			'post_type'      => 'advanced_ads',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		];

		if ( isset( $data['filter']['ad_status']['value'] ) ) {
			$post_status         = $data['filter']['ad_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
			$post_status         = $data['filter']['ad_new_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['ad_id']['value'] ) ) {
			$post_id = $data['filter']['ad_id']['value'];
			if ( -1 != $post_id ) {
				if ( $post_id > 0 ) {
					$args['p'] = $post_id;
				}
			}
		}

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = $posts[0];
			$context['pluggable_data'] = WordPress::get_post_context( $posts[0]->ID );
			if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
				$context['pluggable_data']['ad_new_status'] = $posts[0]->post_status;
			}
			if ( isset( $data['filter']['ad_old_status']['value'] ) ) {
				$context['pluggable_data']['ad_old_status'] = $data['filter']['ad_old_status']['value'];
			}
			$context['pluggable_data']['ad_id'] = $posts[0]->ID;
			if ( isset( $data['filter']['ad_status']['value'] ) ) {
				$context['pluggable_data']['ad_status'] = $posts[0]->post_status;
			}
			$context['response_type'] = 'live';
		} else {
			$context['pluggable_data'] = [
				'ID'                    => 1,
				'post'                  => 1,
				'post_author'           => 1,
				'post_date'             => '2022-11-18 12:18:14',
				'post_date_gmt'         => '2022-11-18 12:18:14',
				'post_content'          => 'Ad Post Content',
				'post_title'            => 'Ad Post',
				'post_excerpt'          => '',
				'post_status'           => 'draft',
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => 'ad-post',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2022-11-18 12:18:14',
				'post_modified_gmt'     => '2022-11-18 12:18:14',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'https://example.com/ad-post/',
				'menu_order'            => 0,
				'post_type'             => 'advanced_ads',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			];
			if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
				$context['pluggable_data']['ad_new_status'] = $data['filter']['ad_new_status']['value'];
			}
			if ( isset( $data['filter']['ad_old_status']['value'] ) ) {
				$context['pluggable_data']['ad_old_status'] = $data['filter']['ad_old_status']['value'];
			}
			$context['pluggable_data']['ad_id'] = 1;
			if ( isset( $data['filter']['ad_status']['value'] ) ) {
				$context['pluggable_data']['ad_status'] = $data['filter']['ad_status']['value'];
			}
			$context['response_type'] = 'sample';
		}

		return $context;
	}

	/**
	 * Get Newsletter lists
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_newsletter_lists( $data ) {
		
		$options = [];
		if ( class_exists( '\Newsletter' ) ) {

			$lists = \Newsletter::instance()->get_lists();

			if ( ! empty( $lists ) ) {
				foreach ( $lists as $list ) {
					$options[] = [
						'label' => $list->name,
						'value' => 'list_' . $list->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Newsletter pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_newsletter_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$list = $data['filter']['list_id']['value'];

		if ( -1 == $list ) {
			$log = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . "newsletter where status='C' ORDER BY id DESC LIMIT 1" );
		} else {
			$num      = $list;
			$location = 1;
			$sql      = 'SELECT * FROM ' . $wpdb->prefix . "newsletter WHERE $num = %d AND status = 'C' ORDER BY id DESC LIMIT 1";
			$log      = $wpdb->get_results( $wpdb->prepare( $sql, $location ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $log ) ) {
			$lists_arr = get_option( 'newsletter_lists' );
			if ( -1 == $list ) {
				foreach ( $log[0] as $key => $val ) {
					if ( defined( 'NEWSLETTER_LIST_MAX' ) ) {
						for ( $i = 1; $i <= NEWSLETTER_LIST_MAX; $i++ ) {
							$list_key = "list_$i";
							
							if ( $key == $list_key ) {
								if ( 1 == $val ) {
									$context['pluggable_data']['list_id'] = $key;
									if ( is_array( $lists_arr ) ) {
										if ( isset( $lists_arr[ $key ] ) ) {
											$list_name                              = $lists_arr[ $key ];
											$context['pluggable_data']['list_name'] = $list_name;
										}
									}   
									continue;
								}
							}
						}
					}
					if ( 'email' == $key ) {
						$context['pluggable_data']['email'] = $val;
					}
				}
			} else {
				$context['pluggable_data']['list_id'] = $list;
				$context['pluggable_data']['email']   = $log[0]['email'];
				if ( is_array( $lists_arr ) ) {
					if ( isset( $lists_arr[ $list ] ) ) {
						$list_name                              = $lists_arr[ $list ];
						$context['pluggable_data']['list_name'] = $list_name;
					}
				}
			}
			$context['response_type'] = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"list_id": "list_1","email": "johnd@mailinator.com","list_name": "Contact List"}}', true );
		}

		return $context;
	}

	/**
	 * Get wpForo Forum list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_forum_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$forums = WPF()->forum->get_forums( [ 'type' => 'forum' ] );

		$options = [];

		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum ) {
				$options[] = [
					'label' => $forum['title'],
					'value' => $forum['forumid'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Topic list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_topic_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$forum_id = $data['dynamic'];

		$topics = WPF()->topic->get_topics( [ 'forumid' => $forum_id ] );

		$options = [];

		if ( ! empty( $topics ) ) {
			foreach ( $topics as $topic ) {
				$options[] = [
					'label' => $topic['title'],
					'value' => $topic['topicid'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Groups list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_user_groups_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$usergroups = WPF()->usergroup->get_usergroups();

		$options = [];

		if ( ! empty( $usergroups ) ) {
			foreach ( $usergroups as $group ) {
				$options[] = [
					'label' => $group['name'],
					'value' => intval( $group['groupid'] ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Reputation list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_foro_reputation_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$levels  = WPF()->member->levels();
		$options = [];

		if ( ! empty( $levels ) ) {
			foreach ( $levels as $level ) {
				$options[] = [
					'label' => esc_attr__( 'Level', 'suretriggers' ) . ' ' . $level . ' - ' . WPF()->member->rating( $level, 'title' ),
					'value' => strval( $level ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * WPForo new topic pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wpforo_topic_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$forum_id = $data['filter']['forum_id']['value'];

		if ( -1 == $forum_id ) {
			$results = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'wpforo_topics WHERE closed = 0 ORDER BY topicid DESC LIMIT 1', ARRAY_A );
		} else {
			$forum   = $forum_id;
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'wpforo_topics WHERE forumid = %d AND closed = 0 ORDER BY topicid DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $forum ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$context['pluggable_data']['forum_id'] = $results[0]['forumid'];
			$context['pluggable_data']['topic_id'] = $results[0]['topicid'];
			
			if ( function_exists( 'WPF' ) ) {
				$context['pluggable_data']['forum'] = WPF()->forum->get_forum( $results[0]['forumid'] );
				$context['pluggable_data']['topic'] = WPF()->topic->get_topic( $results[0]['topicid'] );
			}
			$context['pluggable_data']['user'] = WordPress::get_user_context( $results[0]['userid'] );
			$context['response_type']          = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"forum_id": "2","topic_id": "1","forum": {"forumid": "2","title": "Main Forum","slug": "main-forum","description": "This is a simple parent forum","parentid": "1","icon": "fas fa-comments","cover": 0,"cover_height": "150","last_topicid": "1","last_postid": "2","last_userid": "4","last_post_date": "2023-09-19 11:40:19","topics": "1","posts": "2","permissions": "a:5:{i:1;s:4:\"full\";i:2;s:9:\"moderator\";i:3;s:8:\"standard\";i:4;s:9:\"read_only\";i:5;s:8:\"standard\";}","meta_key": "","meta_desc": "","status": "1","is_cat": "0","layout": "4","order": "0","color": "#888888","url": "https:\/\/example.com\/community\/main-forum\/","cover_url": ""},"topic": {"topicid": "1","forumid": "2","first_postid": "1","userid": "4","title": "New Forum topic title","slug": "new-forum-topic-title","created": "2023-09-19 11:39:01","modified": "2023-09-19 11:40:19","last_post": "2","posts": "2","votes": "0","answers": "0","views": "1","meta_key": "","meta_desc": "","type": "0","solved": "0","closed": "0","has_attach": "0","private": "0","status": "0","name": "","email": "","prefix": "","tags": "","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","short_url": "https:\/\/example.com\/community\/topicid\/1\/"},"user": {"wp_user_id": 4,"user_login": "john@d.com","display_name": "john@d.com","user_firstname": "john","user_lastname": "d","user_email": "john@d.com","user_role": ["customer"]}}}', true );// @phpcs:ignore
		}

		return $context;
	}

	/**
	 * WpForo Topic Reply pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wpforo_topic_reply_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$forum_id = $data['filter']['forum_id']['value'];
		$topic_id = $data['filter']['topic_id']['value'];

		if ( -1 == $forum_id && -1 != $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts WHERE topicid = %d ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $topic_id ), ARRAY_A );// @phpcs:ignore
		} elseif ( -1 != $forum_id && -1 == $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts WHERE forumid = %d ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $forum_id ), ARRAY_A );// @phpcs:ignore
		} elseif ( -1 == $forum_id && -1 == $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql ), ARRAY_A );// @phpcs:ignore
		} else {
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'wpforo_posts WHERE forumid = %d AND topicid = %d ORDER BY postid DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $forum_id, $topic_id ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$context['pluggable_data']['forum_id'] = $results[0]['forumid'];
			$context['pluggable_data']['topic_id'] = $results[0]['topicid'];
			if ( function_exists( 'WPF' ) ) {
				$context['pluggable_data']['forum'] = WPF()->forum->get_forum( $results[0]['forumid'] );
				$context['pluggable_data']['topic'] = WPF()->topic->get_topic( $results[0]['topicid'] );
				$context['pluggable_data']['reply'] = WPF()->post->get_post( $results[0]['postid'] );
			}
			$context['pluggable_data']['user'] = WordPress::get_user_context( $results[0]['userid'] );
			$context['response_type']          = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"forum_id": "2","topic_id": "1","forum": {"forumid": "2","title": "Main Forum","slug": "main-forum","description": "This is a simple parent forum","parentid": "1","icon": "fas fa-comments","cover": 0,"cover_height": "150","last_topicid": "1","last_postid": "2","last_userid": "4","last_post_date": "2023-09-19 11:40:19","topics": "1","posts": "2","permissions": "a:5:{i:1;s:4:\"full\";i:2;s:9:\"moderator\";i:3;s:8:\"standard\";i:4;s:9:\"read_only\";i:5;s:8:\"standard\";}","meta_key": "","meta_desc": "","status": "1","is_cat": "0","layout": "4","order": "0","color": "#888888","url": "https:\/\/example.com\/community\/main-forum\/","cover_url": ""},"topic": {"topicid": "1","forumid": "2","first_postid": "1","userid": "4","title": "New Forum topic title","slug": "new-forum-topic-title","created": "2023-09-19 11:39:01","modified": "2023-09-19 11:40:19","last_post": "2","posts": "2","votes": "0","answers": "0","views": "1","meta_key": "","meta_desc": "","type": "0","solved": "0","closed": "0","has_attach": "0","private": "0","status": "0","name": "","email": "","prefix": "","tags": "","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","short_url": "https:\/\/example.com\/community\/topicid\/1\/"},"reply_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","reply": {"postid": "2","parentid": "0","forumid": "2","topicid": "1","userid": 4,"title": "RE: New Forum topic title","body": "<p>new reply<\/p>","created": "2023-09-19 11:40:19","modified": "2023-09-19 11:40:19","likes": "0","votes": "0","is_answer": "0","is_first_post": "0","status": "0","name": "","email": "","private": "0","root": "-1","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","short_url": "https:\/\/example.com\/community\/postid\/2\/"},"user": {"wp_user_id": 4,"user_login": "john@d.com","display_name": "john@d.com","user_firstname": "john","user_lastname": "d","user_email": "john@d.com","user_role": ["customer"]}}}', true );// @phpcs:ignore
		}

		return $context;
	}

	/**
	 * Get RafflePress Giveaways list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_rp_giveaways_list( $data ) {

		global $wpdb;
		$options = [];

		$giveaways = $wpdb->get_results( "SELECT id,name FROM {$wpdb->prefix}rafflepress_giveaways WHERE deleted_at is null ORDER BY name ASC", ARRAY_A );
		foreach ( $giveaways as $giveaway ) {
			$options[] = [
				'label' => $giveaway['name'],
				'value' => $giveaway['id'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get RafflePress Giveaway Actions list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_rp_giveaway_actions_list( $data ) {

		global $wpdb;
		$options = [];

		$giveaway = $wpdb->get_row( $wpdb->prepare( "SELECT settings FROM {$wpdb->prefix}rafflepress_giveaways WHERE id=%d", $data['dynamic'] ), ARRAY_A );

		if ( is_array( $giveaway ) && isset( $giveaway['settings'] ) ) {
			$settings = json_decode( $giveaway['settings'], true );

			if ( is_array( $settings ) && isset( $settings['entry_options'] ) ) {
				foreach ( $settings['entry_options'] as $action ) {
					$options[] = [
						'label' => $action['name'],
						'value' => $action['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_raffle_press_triggers_last_data( $data ) {

		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$pluggable_data = [
			'giveaway_id'               => 1,
			'giveaway_title'            => 'Sample Giveaway (ID#1)',
			'giveaway_start_date'       => 'September 20, 2023',
			'giveaway_end_date'         => 'October 6, 2023',
			'giveaway_entries'          => 9,
			'giveaway_user_count'       => 3,
			'giveaway_status'           => 'Active',
			'contestant_name'           => 'John Doe',
			'contestant_email'          => 'john_doe@gmail.com',
			'contestant_email_verified' => 'Yes',
			'action_id'                 => '0jnex',
			'action_name'               => 'Visit us on Instagram',
		];

		$giveaway_id = isset( $data['filter']['giveaway_id'] ) ? $data['filter']['giveaway_id']['value'] : null;
		$action_id   = isset( $data['filter']['action_id'] ) ? $data['filter']['action_id']['value'] : null;

		$query = "SELECT contestant_id, giveaway_id, action_id, meta FROM {$wpdb->prefix}rafflepress_entries";

		if ( $giveaway_id && -1 != $giveaway_id ) {
			$query .= ' WHERE giveaway_id = ' . $giveaway_id;

			if ( $action_id ) {
				$query .= " AND action_id = '" . $action_id . "'";
			}
		}

		$query .= ' ORDER BY created_at DESC LIMIT 1';

		$giveaway_data = $wpdb->get_row( $query, ARRAY_A ); // @phpcs:ignore

		if ( ! empty( $giveaway_data ) ) {
			$pluggable_data = array_merge(
				RafflePress::get_giveaway_context( $giveaway_data['giveaway_id'] ),
				RafflePress::get_contestant_context( $giveaway_data['contestant_id'] )
			);

			$pluggable_data['performed_action_id'] = isset( $giveaway_data['action_id'] ) ? $giveaway_data['action_id'] : 0;

			$giveaway_meta                           = isset( $giveaway_data['meta'] ) ? json_decode( $giveaway_data['meta'], true ) : [];
			$pluggable_data['performed_action_name'] = is_array( $giveaway_meta ) && isset( $giveaway_meta['action'] ) ? $giveaway_meta['action'] : '';

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = $pluggable_data;

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_shipstation_triggers_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];

        $order_sample_data = json_decode( '{"product_id": 47,"id": 49,"parent_id": 0,"status": "completed","currency": "USD","version": "8.1.1","prices_include_tax": false,"date_created": {"date": "2023-09-23 10:28:00.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-09-23 10:29:55.000000","timezone_type": 1,"timezone": "+00:00"},"discount_total": "0","discount_tax": "0", "shipping_total": "0","shipping_tax": "0","cart_tax": "0","total": "1.00","total_tax": "0","customer_id": 1,"order_key": "wc_order_64IaGkeQKRXdm","billing": {"first_name": "john","last_name": "d","company": "","address_1": "123 Main Street","address_2": "","city": "London","state": "Greater London","postcode": "SW1A 1AA","country": "GB","email": "johnd@d.com","phone": "9878988766"},"shipping": {"first_name": "","last_name": "","company": "","address_1": "","address_2": "","city": "","state": "","postcode": "","country": "","phone": ""},"payment_method": "cod","payment_method_title": "Cash on delivery","transaction_id": "","customer_ip_address": "182.184.87.226","customer_user_agent": "Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/116.0.0.0 Safari\/537.36","created_via": "checkout","customer_note": "", "date_completed": { "date": "2023-09-23 10:29:55.000000", "timezone_type": 1, "timezone": "+00:00" }, "date_paid": { "date": "2023-09-23 10:29:55.000000", "timezone_type": 1, "timezone": "+00:00" }, "cart_hash": "ac073abcdc9a52025211d0fad52cfa46", "order_stock_reduced": true, "download_permissions_granted": true, "new_order_email_sent": true, "recorded_sales": true, "recorded_coupon_usage_counts": true, "number": "49", "meta_data": [{ "id": 1206, "key":"is_vat_exempt", "value": "no"},{"id": 1207,"key": "weglot_language","value": "en"},{"id": 1208,"key": "_shipstation_exported","value": "yes"},{"id": 1209,"key": "_shipstation_shipped_item_count","value": "1"}],"line_items": {"id": "25","order_id": "49","name": "New Product","product_id": "47","variation_id": "0","quantity": "1","tax_class": "","subtotal": "1","subtotal_tax": "0","total": "1","total_tax": "0","taxes": "","meta_data": []},"tax_lines": [],"shipping_lines": [],"fee_lines": [],"coupon_lines": [],"coupons": [],"products": [{"id": 25,"order_id": 49,"name": "New Product","product_id": 47,"variation_id": 0,"quantity": 1,"tax_class": "","subtotal": "1","subtotal_tax": "0","total": "1","total_tax": "0","taxes": {"total": [],"subtotal": []},"meta_data": []}],"quantity": "1","wp_user_id": 1,"user_login": "johnd","display_name": "johnd","user_firstname": "john","user_lastname": "d","user_email": "johnd@d.com","user_role": ["administrator"],"shipping_tracking_number": "","shipping_carrier": "","ship_date": "",}', true ); //phpcs:ignore

		$product_id = (int) ( isset( $data['filter']['product_id']['value'] ) ? $data['filter']['product_id']['value'] : -1 );
		$condition  = $data['filter']['condition_compare']['value'];
		$price      = $data['filter']['price']['value'];
		$term       = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( 'order_shipped' === $term ) {
			$orders = wc_get_orders( 
				[ 
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'status'      => 'completed',
					'meta_query'  => [
						[
							'key'     => '_shipstation_shipped_item_count',
							'compare' => 'EXISTS',
						],
					],
				] 
			);
			if ( count( $orders ) > 0 ) {
				$order_id    = $orders[0]->get_id();
				$order       = wc_get_order( $order_id );
				$user_id     = $order->get_customer_id();
				$product_ids = [];
				if ( $order ) {
					$items = $order->get_items();
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$product_ids[] = $item->get_product_id();
						}
					}

					foreach ( $product_ids as $product_id ) {
						$context['product_id'] = $product_id;
					}
				}
				$context                             = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $context;
		} elseif ( 'specific_product_order_shipped' === $term ) {
			if ( -1 != $product_id ) {
				$product_ids = [ $product_id ];
				$orders      = wc_get_orders( 
					[ 
						'numberposts' => 1,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'status'      => 'completed',
						'meta_query'  => [
							[
								'key'     => '_shipstation_shipped_item_count',
								'compare' => 'EXISTS',
							],
							[
								'key'     => '_product_id',
								'value'   => $product_ids,
								'compare' => 'IN',
							],
						],
					] 
				);
			} else {
				$orders = wc_get_orders( 
					[ 
						'numberposts' => 1,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'status'      => 'completed',
						'meta_query'  => [
							[
								'key'     => '_shipstation_shipped_item_count',
								'compare' => 'EXISTS',
							],
						],
					] 
				);
			}
			if ( count( $orders ) > 0 ) {
				$order_id   = $orders[0]->get_id();
				$order      = wc_get_order( $order_id );
				$user_id    = $order->get_customer_id();
				$productids = [];
				$items      = $order->get_items();
				foreach ( $items as $item ) {
					if ( method_exists( $item, 'get_product_id' ) ) {
						$productids[] = $item->get_product_id();
					}
				}
				$context = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);

				foreach ( $productids as $product_id ) {
					$context['product_id'] = $product_id;
				}

				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $context;
		} elseif ( 'specific_amount_order_shipped' === $term ) {
			$orders = wc_get_orders( 
				[ 
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'status'      => 'completed',
					'meta_query'  => [
						[
							'key'     => '_shipstation_shipped_item_count',
							'compare' => 'EXISTS',
						],
						[
							'key'     => '_order_total',
							'value'   => $price,
							'compare' => $condition,
						],
					],
				] 
			);
			if ( count( $orders ) > 0 ) {
				$order_id    = $orders[0]->get_id();
				$order       = wc_get_order( $order_id );
				$user_id     = $order->get_customer_id();
				$product_ids = [];
				$items       = $order->get_items();
				foreach ( $items as $item ) {
					if ( method_exists( $item, 'get_product_id' ) ) {
						$product_ids[] = $item->get_product_id();
					}
				}
				$context = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);

				foreach ( $product_ids as $product_id ) {
					$context['product_id'] = $product_id;
				}

				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}

			$context['pluggable_data'] = $context;
		}

		return $context;
	}

	/**
	 * Get GroundHogg Tag list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_groundhogg_tag_list( $data ) {

		if ( ! function_exists( 'Groundhogg\get_db' ) ) {
			return [];
		}

		$tags    = \Groundhogg\get_db( 'tags' )->query( [] );
		$options = [];

		if ( ! empty( $tags ) ) {
			foreach ( $tags as $tag ) {
				$options[] = [
					'label' => $tag->tag_name,
					'value' => $tag->tag_id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WP courseware courses list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wpcw_courses( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'wpcw_course',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$courses = get_posts( $args );

		$course_count = wp_count_posts( 'wpcw_course' )->publish;

		if ( ! empty( $courses ) ) {
			if ( is_array( $courses ) ) {
				foreach ( $courses as $course ) {
					$options[] = [
						'label' => $course->post_title,
						'value' => $course->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $course_count > $limit && $course_count > $offset,
		];
	}

	/**
	 * Get WP courseware courses list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wpcw_modules( $data ) {
		$options = [];

		if ( function_exists( 'wpcw_get_modules' ) ) {
			$modules = wpcw_get_modules();
		}

		if ( ! empty( $modules ) ) {
			if ( is_array( $modules ) ) {
				foreach ( $modules as $module ) {
					$options[] = [
						'label' => $module->module_title,
						'value' => $module->module_id,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WP courseware unit list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wpcw_units( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'course_unit',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$units = get_posts( $args );

		$unit_count = wp_count_posts( 'course_unit' )->publish;

		if ( ! empty( $units ) ) {
			if ( is_array( $units ) ) {
				foreach ( $units as $unit ) {
					$options[] = [
						'label' => $unit->post_title,
						'value' => $unit->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $unit_count > $limit && $unit_count > $offset,
		];
	}

	/**
	 * Search WP Courseware data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_wpcw_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( 'wpcw_course_completed' === $trigger ) {
			$post_id = $data['filter']['course_post_id']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_progress=100 order by postmeta.user_id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_id = %s AND postmeta.course_progress=100 order by postmeta.user_id DESC LIMIT 1", $post_id ) );
			}
		} elseif ( 'wpcw_module_completed' === $trigger ) {
			$post_id = $data['filter']['module_id']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses as postmeta JOIN {$wpdb->prefix}wpcw_modules as posts ON posts.parent_course_id=postmeta.course_id WHERE postmeta.course_progress>=0 order by postmeta.course_enrolment_date DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses as postmeta JOIN {$wpdb->prefix}wpcw_modules as posts ON posts.parent_course_id=postmeta.course_id WHERE postmeta.course_progress>=0 AND posts.module_id=%s order by postmeta.course_enrolment_date DESC LIMIT 1", $post_id ) );
			}
		} elseif ( 'wpcw_unit_completed' === $trigger ) {
			$post_id = $data['filter']['unit_id']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpcw_user_progress WHERE unit_completed_status='complete' order by unit_id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpcw_user_progress WHERE unit_id=%d AND unit_completed_status='complete' order by unit_id DESC LIMIT 1", $post_id ) );
			}
		} elseif ( 'wpcw_enroll_course' === $trigger ) {
			$post_id = $data['filter']['course_post_id']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses as postmeta JOIN {$wpdb->prefix}wpcw_courses as posts ON posts.course_id=postmeta.course_id order by course_enrolment_date DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wpcw_user_courses  as postmeta JOIN {$wpdb->prefix}wpcw_courses as posts ON posts.course_id=postmeta.course_id WHERE posts.course_post_id=%d order by course_enrolment_date DESC LIMIT 1", $post_id ) );
			}
		}

		if ( ! empty( $result ) ) {

			switch ( $trigger ) {
				case 'wpcw_course_completed':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;
					if ( function_exists( 'wpcw_get_course' ) ) {
						$course = wpcw_get_course( $result_course_id );
						if ( is_object( $course ) ) {
							$course = get_object_vars( $course );
						}
						$context = array_merge( WordPress::get_user_context( $result_user_id ), $course );
					}
					break;
				case 'wpcw_module_completed':
					$result_module_id = $result[0]->module_id;
					$result_user_id   = $result[0]->user_id;
					if ( function_exists( 'wpcw_get_module' ) ) {
						$module = wpcw_get_module( $result_module_id );
						if ( is_object( $module ) ) {
							$module = get_object_vars( $module );
						}
						$context = array_merge( WordPress::get_user_context( $result_user_id ), $module );
					}
					break;
				case 'wpcw_unit_completed':
					$result_unit_id = $result[0]->unit_id;
					$result_user_id = $result[0]->user_id;
					if ( function_exists( 'wpcw_get_unit' ) ) {
						$unit = wpcw_get_unit( $result_unit_id );
						if ( is_object( $unit ) ) {
							$unit         = get_object_vars( $unit );
							$unit['name'] = get_the_title( $result_unit_id );
						}
						$context = array_merge( WordPress::get_user_context( $result_user_id ), $unit );
					}
					break;
				case 'wpcw_enroll_course':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;
					if ( function_exists( 'WPCW_courses_getCourseDetails' ) ) {
						$course_detail = WPCW_courses_getCourseDetails( $result_course_id );
						if ( is_object( $course_detail ) ) {
							$course_detail = get_object_vars( $course_detail );
						}
						$context = array_merge( WordPress::get_user_context( $result_user_id ), $course_detail );
					}
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context;
			$context['response_type']  = 'live';
		}

		return $context;

	}

	/**
	 * Get WooCommerce Order Note list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_note_type_list( $data ) {

		$options = [];

		$options[] = [
			'label' => 'Customer',
			'value' => 'customer',
		];
		$options[] = [
			'label' => 'Private',
			'value' => 'internal',
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_customers_triggers_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];
		$term                      = isset( $data['search_term'] ) ? $data['search_term'] : '';
		global $wpdb;
		if ( 'customer_created' === $term ) {
			$customer_query = get_users(
				[
					'fields'  => 'ID',
					'role'    => 'customer',
					'orderby' => 'ID',
					'order'   => 'DESC',
					'number'  => 1,   
				]
			);
			$results        = $customer_query;
			if ( ! empty( $results ) ) {
				$customer      = new WC_Customer( $results[0] );
				$last_order    = $customer->get_last_order();
				$customer_data = [
					'id'               => $customer->get_id(),
					'email'            => $customer->get_email(),
					'first_name'       => $customer->get_first_name(),
					'last_name'        => $customer->get_last_name(),
					'username'         => $customer->get_username(),
					'last_order_id'    => is_object( $last_order ) ? $last_order->get_id() : null,
					'orders_count'     => $customer->get_order_count(),
					'total_spent'      => wc_format_decimal( $customer->get_total_spent(), 2 ),
					'avatar_url'       => $customer->get_avatar_url(),
					'billing_address'  => [
						'first_name' => $customer->get_billing_first_name(),
						'last_name'  => $customer->get_billing_last_name(),
						'company'    => $customer->get_billing_company(),
						'address_1'  => $customer->get_billing_address_1(),
						'address_2'  => $customer->get_billing_address_2(),
						'city'       => $customer->get_billing_city(),
						'state'      => $customer->get_billing_state(),
						'postcode'   => $customer->get_billing_postcode(),
						'country'    => $customer->get_billing_country(),
						'email'      => $customer->get_billing_email(),
						'phone'      => $customer->get_billing_phone(),
					],
					'shipping_address' => [
						'first_name' => $customer->get_shipping_first_name(),
						'last_name'  => $customer->get_shipping_last_name(),
						'company'    => $customer->get_shipping_company(),
						'address_1'  => $customer->get_shipping_address_1(),
						'address_2'  => $customer->get_shipping_address_2(),
						'city'       => $customer->get_shipping_city(),
						'state'      => $customer->get_shipping_state(),
						'postcode'   => $customer->get_shipping_postcode(),
						'country'    => $customer->get_shipping_country(),
					],
				];
				if ( is_object( $last_order ) && method_exists( $last_order, 'get_date_created' ) ) {
					$created_date = $last_order->get_date_created();
					if ( is_object( $created_date ) && method_exists( $created_date, 'getTimestamp' ) ) {
						$last_order_date                  = $created_date->getTimestamp();
						$customer_data['created_at']      = $last_order_date;
						$customer_data['last_order_date'] = $last_order_date;
					}
				}
				$order_sample_data         = $customer_data;
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $order_sample_data;
			} else {
				$order_sample_data         = json_decode( '{"id": 158,"email": "johnd@d.com","first_name": "john","last_name": "d","username": "johnd","last_order_id": 6604,"orders_count": 3,"total_spent": "45.00","avatar_url": "https:\/\/secure.gravatar.com\/avatar\/0f9c8dc78cff3ea4d447b2297c8f6803?s=96&d=mm&r=g","billing_address": {"first_name": "john","last_name": "d","company": "","address_1": "","address_2": "","city": "London","state": "TN","postcode": "PV1 QW2","country": "UK","email": "johnd@d.com","phone": "9878988766"},"shipping_address": {"first_name": "","last_name": "","company": "","address_1": "","address_2": "","city": "","state": "","postcode": "","country": ""},"created_at": 1697631243,"last_order_date": 1697631243}', true );
				$context['pluggable_data'] = $order_sample_data;
			}
		} elseif ( 'total_spend_reach' === $term ) {
			$total_spend_selected = isset( $data['filter']['total_spend']['value'] ) ? $data['filter']['total_spend']['value'] : '';
			$customers            = $wpdb->get_col(
				"SELECT DISTINCT meta_value  FROM $wpdb->postmeta
			WHERE meta_key = '_customer_user' AND meta_value > 0"
			);
			$target_customer      = null;
			foreach ( $customers as $customer ) {
				$total_spend = (int) wc_get_customer_total_spent( $customer );
				if ( $total_spend == $total_spend_selected ) {
					$target_customer = $customer;
					break; // Exit the loop once a matching customer is found.
				}
			}
			if ( $target_customer ) {
				$new_customer              = new WC_Customer( $target_customer );
				$new_customer_data         = [
					'id'          => $new_customer->get_id(),
					'email'       => $new_customer->get_email(),
					'first_name'  => $new_customer->get_first_name(),
					'last_name'   => $new_customer->get_last_name(),
					'username'    => $new_customer->get_username(),
					'order_count' => $new_customer->get_order_count(),
					'total_spend' => wc_format_decimal( $new_customer->get_total_spent(), 2 ),
				];
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $new_customer_data;
			} else {
				$sample_customer_data      = [
					'id'          => '101',
					'created_at'  => '1680675247',
					'email'       => 'john@d.com',
					'first_name'  => 'John',
					'last_name'   => 'D',
					'username'    => 'johnd',
					'order_count' => '3',
					'total_spend' => '22.00',
				];
				$context['pluggable_data'] = $sample_customer_data;
			}
		} elseif ( 'order_count_reach' === $term ) {
			$total_order_selected = isset( $data['filter']['order_count']['value'] ) ? $data['filter']['order_count']['value'] : '';
			$customers            = $wpdb->get_col(
				"SELECT DISTINCT meta_value  FROM $wpdb->postmeta
			WHERE meta_key = '_customer_user' AND meta_value > 0"
			);
			$target_customer      = null;
			foreach ( $customers as $customer ) {
				$args   = [
					'customer_id' => $customer,
					'limit'       => -1,
					'status'      => [ 'wc-completed' ],
				];
				$orders = wc_get_orders( $args );
				if ( ! empty( $orders ) ) {
					$total_order = (int) wc_get_customer_order_count( $customer );
					if ( $total_order == $total_order_selected ) {
						$target_customer = $customer;
						break; // Exit the loop once a matching customer is found.
					}
				}
			}
			if ( $target_customer ) {
				$new_customer              = new WC_Customer( $target_customer );
				$new_customer_data         = [
					'id'          => $new_customer->get_id(),
					'email'       => $new_customer->get_email(),
					'first_name'  => $new_customer->get_first_name(),
					'last_name'   => $new_customer->get_last_name(),
					'username'    => $new_customer->get_username(),
					'order_count' => $new_customer->get_order_count(),
				];
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $new_customer_data;
			} else {
				$sample_customer_data      = [
					'id'           => '101',
					'created_at'   => '1680675247',
					'email'        => 'john@d.com',
					'first_name'   => 'John',
					'last_name'    => 'D',
					'username'     => 'johnd',
					'orders_count' => '3',
				];
				$context['pluggable_data'] = $sample_customer_data;
			}
		}

		return $context;
	}

	/**
	 * Get Affiliate list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_affiliate_list( $data ) {
		$options = [];

		$args = [
			'meta_query' => [
				[
					'key'     => 'wafp_is_affiliate',
					'value'   => '1',
					'compare' => '=',
				],
			],
		];

		$affiliates = get_users( $args );

		foreach ( $affiliates as $user ) {
			$options[] = [
				'label' => $user->display_name,
				'value' => $user->ID,
			];
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Affiliate Source list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_affiliate_transaction_source_list( $data ) {
		$options = [];

		$sources = [
			'General',
			'MemberPress',
			'WooCommerce',
			'Easy Digital Downloads',
			'WPForms',
			'Formidable',
			'PayPal',
		];
		foreach ( $sources as $source ) {
			$options[] = [
				'label' => $source,
				'value' => str_replace( ' ', '_', strtolower( $source ) ),
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Easy Affiliate data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_easy_affiliate_last_data( $data ) {

		global $wpdb;
		$trigger = $data['search_term'];
		$context = [];

		if ( 'sale_recorded' === $trigger ) {
			$affiliate_id = $data['filter']['affiliate_id']['value'];
			if ( -1 === $affiliate_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wafp_transactions as postmeta JOIN {$wpdb->prefix}wafp_events as posts ON posts.evt_id=postmeta.id WHERE postmeta.status='complete' order by postmeta.id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wafp_transactions as postmeta JOIN {$wpdb->prefix}wafp_events as posts ON posts.evt_id=postmeta.id WHERE postmeta.status='complete' AND affiliate_id=%d order by postmeta.id DESC LIMIT 1", $affiliate_id ) );
			}
		} elseif ( 'sale_added' === $trigger ) {
			$orderby = 'signup_date';
			$order   = 'DESC';
			$paged   = 1;
			$search  = '';
			$perpage = 1;
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$result = \EasyAffiliate\Models\User::affiliate_list_table( $orderby, $order, $paged, $search, $perpage );
			if ( empty( $result['results'] ) ) {
				$result = [];
			}
		} elseif ( 'payout_made' === $trigger ) {
			$affiliate_id = $data['filter']['affiliate_id']['value'];
			if ( -1 === $affiliate_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wafp_payments as postmeta JOIN {$wpdb->prefix}wafp_events as posts ON posts.evt_id=postmeta.id WHERE postmeta.amount>0 order by postmeta.id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wafp_payments as postmeta JOIN {$wpdb->prefix}wafp_events as posts ON posts.evt_id=postmeta.id WHERE postmeta.amount>0 AND affiliate_id=%d order by postmeta.id DESC LIMIT 1", $affiliate_id ) );
			}
		}

		if ( ! empty( $result ) ) {
			switch ( $trigger ) {
				case 'sale_recorded':
					$result_affiliate_id = $result[0]->affiliate_id;
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$affiliate = \EasyAffiliate\Lib\ModelFactory::fetch( $result[0]->evt_id_type, $result[0]->evt_id );
					$affiliate = get_object_vars( $affiliate->rec );
					$context   = array_merge( WordPress::get_user_context( $result_affiliate_id ), $affiliate );
					break;
				case 'sale_added':
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$data = new \EasyAffiliate\Models\User( $result['results'][0]->ID );
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$context = get_object_vars( $data->rec );
					break;
				case 'payout_made':
					$result_affiliate_id = $result[0]->affiliate_id;
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$affiliate = \EasyAffiliate\Lib\ModelFactory::fetch( $result[0]->evt_id_type, $result[0]->evt_id );
					$affiliate = get_object_vars( $affiliate->rec );
					$context   = array_merge( WordPress::get_user_context( $result_affiliate_id ), $affiliate );
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context;
			$context['response_type']  = 'live';
		} elseif ( empty( $result ) ) {
			switch ( $trigger ) {
				case 'sale_added':
					$context = json_decode(
						'{"ID": 1,"first_name": "John","last_name": "D","user_login": "johnd","user_nicename": "johnd","user_email": "johnd@d.com","user_url": "","user_registered": "2023-10-13 13:39:16","user_activation_key": "","user_status": "0","display_name": "johnd","paypal_email": "johnd@d.com","address_one": "1street","address_two": "",
					"city": "london","state": "","zip": "","country": "UK","tax_id_us": "","tax_id_int": "","is_affiliate": "1","is_blocked": "","blocked_message": "","referrer": "0","notes": "","unsubscribed": ""}', 
						true
					);
					break;
				case 'sale_recorded':
					$context = json_decode( '{"wp_user_id": 73,"user_login": "johnd@yopmail.com","display_name": "john d","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["subscriber"],"id": "5","affiliate_id": "73","click_id": "0","item_id": "","item_name": "test","coupon": "","sale_amount": "10.00","refund_amount": "20.00","subscr_id": "","subscr_paynum": "0","ip_addr": "","cust_email": "","cust_name": "","trans_num": "test5","type": "commission","source": "general","order_id": "0","status": "complete","rebill": "0","created_at": "2023-10-11 09:18:41"}', true );
					break;
				case 'payout_made':
					$context = json_decode( '{"wp_user_id": 73,"user_login": "johnd@yopmail.com","display_name": "john d","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["subscriber"],"id": "5","affiliate_id": "73","click_id": "0","item_id": "","item_name": "test","coupon": "","sale_amount": "10.00","refund_amount": "20.00","subscr_id": "","subscr_paynum": "0","ip_addr": "","cust_email": "","cust_name": "","trans_num": "test5","type": "commission","source": "general","order_id": "0","status": "complete","rebill": "0","created_at": "2023-10-11 09:18:41"}', true );
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context;
			$context['response_type']  = 'sample';
		}

		return $context;

	}

	/**
	 * Get WooCommerce Subscriptions Coupon list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_woo_subscription_coupon_list( $data ) {
		$options = [];

		$coupon_codes = [];

		$query = new \WP_Query(
			[
				'post_type'      => 'shop_coupon',
				'posts_per_page' => -1,
				'no_found_rows'  => true,
				'meta_query'     => [
					[
						'key'     => '_is_aw_coupon',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		foreach ( $query->posts as $coupon ) {
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$code                  = wc_format_coupon_code( $coupon->post_title );
			$coupon_codes[ $code ] = $code;
		}

		foreach ( $coupon_codes as $code ) {

			$coupon = new \WC_Coupon( $code );

			if ( ! in_array( $coupon->get_discount_type(), [ 'recurring_fee', 'recurring_percent' ], true ) ) {
				unset( $coupon_codes[ $code ] );
			}
		}
		
		if ( ! empty( $coupon_codes ) ) {
			if ( is_array( $coupon_codes ) ) {
				foreach ( $coupon_codes as $coupon_code ) {
					$options[] = [
						'label' => $coupon_code,
						'value' => $coupon_code,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyBoss field.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bb_field_list( $data ) {
		global $wpdb;
		$options = [];

		$xprofile_fields = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_xprofile_fields WHERE parent_id = 0 ORDER BY field_order ASC" );
		if ( ! empty( $xprofile_fields ) ) {
			foreach ( $xprofile_fields as $xprofile_field ) {
				$options[] = [
					'label' => $xprofile_field->name,
					'value' => $xprofile_field->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Woocommerce Bookings Product List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wb_bookable_product_list( $data ) {
		$options = [];

		if ( ! class_exists( 'WC_Bookings_Admin' ) ) {
			return;
		}

		$products = WC_Bookings_Admin::get_booking_products();
		if ( ! empty( $products ) ) {
			if ( is_array( $products ) ) {
				foreach ( $products as $product ) {
					$options[] = [
						'label' => $product->get_name(),
						'value' => $product->get_id(),
					];
					$resources = $product->get_resources();
					if ( ! empty( $resources ) ) {
						foreach ( $resources as $resource ) {
							$options[] = [
								'label' => $resource->get_name(),
								'value' => $resource->get_id(),
							];
						}
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for WooCommerce Booking trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_booking_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];
		$term                      = isset( $data['search_term'] ) ? $data['search_term'] : '';
		global $wpdb;
		if ( 'booking_created' === $term ) {
			$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type='wc_booking' AND post_status='confirmed' order by ID DESC LIMIT 1" );
			
			if ( ! empty( $results ) ) {
				if ( class_exists( 'WC_Booking' ) ) {
					$booking             = new WC_Booking( $results[0]->ID );
					$person_counts       = $booking->get_person_counts();
					$bookable_product_id = $booking->get_product_id();
					if ( method_exists( $booking, 'get_data' ) ) {
						$booking          = $booking->get_data();
						$booking['start'] = gmdate( 'Y-m-d H:i:s', $booking['start'] );
						$booking['end']   = gmdate( 'Y-m-d H:i:s', $booking['end'] );
						if ( ! empty( $person_counts ) ) {
							$total_count = 0;
							foreach ( $person_counts as $key => $value ) {
								$total_count += $value;
							}
							$booking['total_person_counts'] = $total_count;
						}
						$booking['bookable_product'] = $bookable_product_id;
						$booking['bookable_product'] = $booking->get_product_id();
						$context['response_type']    = 'live';
						$context['pluggable_data']   = array_merge( $booking, WordPress::get_user_context( $booking['customer_id'] ) );
					}
				}
			} else {
				$booking_sample_data       = json_decode( '{"id": 6546,"all_day": false,"cost": "45.02","customer_id": 1,"date_created": 1696915500,"date_modified": 1696915863,"end": "2023-10-14 12:00:00","google_calendar_event_id": "0","order_id": 0,"order_item_id": 0,"parent_id": 0,"person_counts": {"6525": 1,"6526": 1},"product_id": 6527,"resource_id": 0,"start": "2023-10-14 10:00:00","status": "confirmed","local_timezone": "","meta_data": [{"id": 12163,"key": "_booking_all_day","value": "0"},{"id": 12164,"key": "_booking_cost","value": "45.02"},{"id": 12165,"key": "_booking_customer_id","value": "1"},{"id": 12166,"key": "_booking_order_item_id","value": "0"},{"id": 12167,"key": "_booking_parent_id","value": "0"},{"id": 12168,"key": "_booking_persons","value": {"6525": 1,"6526": 1}},{"id": 12169,"key": "_booking_product_id","value": "6527"},{"id": 12170,"key": "_booking_resource_id","value": "0"},{"id": 12171,"key": "_booking_start","value": "20231014100000"},{"id": 12172,"key": "_booking_end","value": "20231014120000"},{"id": 12173,"key": "_wc_bookings_gcalendar_event_id","value": "0"},{"id": 12175,"key": "_edit_lock","value": "1696915725:1"},{"id": 12176,"key": "_edit_last","value": "1"}],"total_person_counts": 2,"bookable_product": 6527,"wp_user_id": 1,"user_login": "johnd@yopmail.com","display_name": "john d","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["customer"]}', true );
				$context['pluggable_data'] = $booking_sample_data;
			}
		} elseif ( 'booking_status_changed' === $term ) {
			$to_status = isset( $data['filter']['to_status']['value'] ) ? $data['filter']['to_status']['value'] : -1;

			if ( -1 == $to_status ) {
				$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type='wc_booking' order by ID DESC LIMIT 1" );
			} else {
				$results = $wpdb->get_results(
					$wpdb->prepare(
						"
						SELECT *
						FROM {$wpdb->prefix}posts
						WHERE  `post_status` = %s
						AND  `post_type` LIKE %s
					",
						$to_status,
						'wc_booking'
					)
				);
			}
			
			if ( ! empty( $results ) ) {
				if ( class_exists( 'WC_Booking' ) ) {
					$booking             = new WC_Booking( $results[0]->ID );
					$person_counts       = $booking->get_person_counts();
					$bookable_product_id = $booking->get_product_id();
					if ( method_exists( $booking, 'get_data' ) ) {
						$booking          = $booking->get_data();
						$booking['start'] = gmdate( 'Y-m-d H:i:s', $booking['start'] );
						$booking['end']   = gmdate( 'Y-m-d H:i:s', $booking['end'] );
						if ( ! empty( $person_counts ) ) {
							$total_count = 0;
							foreach ( $person_counts as $key => $value ) {
								$total_count += $value;
							}
							$booking['total_person_counts'] = $total_count;
						}
						$booking['bookable_product'] = $bookable_product_id;
						$booking['from_status']      = $data['filter']['from_status']['value'];
						$booking['to_status']        = $booking['status'];
						$context['response_type']    = 'live';
						$context['pluggable_data']   = array_merge( $booking, WordPress::get_user_context( $booking['customer_id'] ) );
					}
				}
			} else {
				$booking_sample_data       = json_decode( '{"id": 6546,"all_day": false,"cost": "45.02","customer_id": 1,"date_created": 1696915500,"date_modified": 1696915863,"end": "2023-10-14 12:00:00","google_calendar_event_id": "0","order_id": 0,"order_item_id": 0,"parent_id": 0,"person_counts": {"6525": 1,"6526": 1},"product_id": 6527,"resource_id": 0,"start": "2023-10-14 10:00:00","status": "confirmed","to_status": "confirmed","from_status": "cancelled","local_timezone": "","meta_data": [{"id": 12163,"key": "_booking_all_day","value": "0"},{"id": 12164,"key": "_booking_cost","value": "45.02"},{"id": 12165,"key": "_booking_customer_id","value": "1"},{"id": 12166,"key": "_booking_order_item_id","value": "0"},{"id": 12167,"key": "_booking_parent_id","value": "0"},{"id": 12168,"key": "_booking_persons","value": {"6525": 1,"6526": 1}},{"id": 12169,"key": "_booking_product_id","value": "6527"},{"id": 12170,"key": "_booking_resource_id","value": "0"},{"id": 12171,"key": "_booking_start","value": "20231014100000"},{"id": 12172,"key": "_booking_end","value": "20231014120000"},{"id": 12173,"key": "_wc_bookings_gcalendar_event_id","value": "0"},{"id": 12175,"key": "_edit_lock","value": "1696915725:1"},{"id": 12176,"key": "_edit_last","value": "1"}],"total_person_counts": 2,"bookable_product": 6527,"wp_user_id": 1,"user_login": "johnd@yopmail.com","display_name": "john d","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["customer"]}', true );
				$context['pluggable_data'] = $booking_sample_data;
			}
		}
		return $context;
	}

	/**
	 * Get WooCommerce Booking status list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_woo_booking_status_list( $data ) {

		$options = [
			[
				'label' => 'Pending Confirmation',
				'value' => 'pending-confirmation',
			],
			[
				'label' => 'Unpaid',
				'value' => 'unpaid',
			],
			[
				'label' => 'Confirmed',
				'value' => 'confirmed',
			],
			[
				'label' => 'Paid',
				'value' => 'paid',
			],
			[
				'label' => 'Complete',
				'value' => 'complete',
			],
			[
				'label' => 'In Cart',
				'value' => 'in-cart',
			],
			[
				'label' => 'Cancelled',
				'value' => 'cancelled',
			],
		];      

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Woocommerce Memberships Status List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_membership_status_list( $data ) {
		$options = [];
		if ( ! function_exists( 'wc_memberships_get_user_membership_statuses' ) ) {
			return;
		}

		$statuses = wc_memberships_get_user_membership_statuses();
		if ( ! empty( $statuses ) ) {
			if ( is_array( $statuses ) ) {
				foreach ( $statuses as $status => $value ) {
					$status    = 0 === strpos( $status, 'wcm-' ) ? substr( $status, 4 ) : $status;
					$options[] = [
						'label' => $value['label'],
						'value' => $status,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Woocommerce Subscription data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_user_subscription_last_data( $data ) {
		
		$context = [];

		if ( ! function_exists( 'wcs_get_subscription' ) ) {
			return;
		}
		if ( ! function_exists( 'wcs_get_subscriptions_for_product' ) ) {
			return;
		}
		if ( ! function_exists( 'wcs_order_contains_renewal' ) ) {
			return;
		}
		if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
			return;
		}
		
		if ( 'trial_end' == $data['search_term'] ) {
			$subscription_id     = $data['filter']['subscription']['value'];
			$query_args          = [
				'post_type'      => 'shop_subscription',
				'orderby'        => 'ID',
				'order'          => 'DESC',
				'post_status'    => 'wc-active',
				'posts_per_page' => 1,
				'meta_query'     => [
					[
						'key'     => '_schedule_trial_end',
						'value'   => gmdate( 'Y-m-d H:i:s' ),
						'compare' => '<=',
					],
				],
				'post__in'       => [ $subscription_id ],
			];
			$query_result        = new WP_Query( $query_args );
			$subscription_orders = $query_result->get_posts();
	
			if ( ! empty( $subscription_orders ) ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$subscription = wcs_get_subscription( $subscription_orders[0]->ID );
				$user_id      = $subscription->get_user_id();
	
				$items       = $subscription->get_items();
				$product_ids = [];
				foreach ( $items as $item ) {
					$product_ids[] = $item->get_product_id();
				}
	
				$subscription_status     = $subscription->get_status();
				$subscription_start_date = $subscription->get_date_created();
	
				$context['subscription_data'] = [
					'status'            => $subscription_status,
					'start_date'        => $subscription_start_date,
					'trial_end_date'    => $subscription->get_date( 'schedule_trial_end' ),
					'next_payment_date' => $subscription->get_date( 'next_payment' ),
					'end_date'          => $subscription->get_date( 'schedule_end' ),
				];
				$context['user']              = WordPress::get_user_context( $user_id );
	
				foreach ( $product_ids as $val ) {
					$context['subscription']      = $val;
					$context['subscription_name'] = get_the_title( $val );
				}
				$context['pluggable_data'] = $context;
				$context['response_type']  = 'live';
			} else {
				$context                   = json_decode( '{"subscription_data": {"status": "active","start_date": {"date": "2023-05-18 12:36:53.000000","timezone_type": 1,"timezone": "+00:00"},"trial_end_date": "2023-10-20 03:31:04","next_payment_date": 0,"end_date": "2024-05-28 12:36:53"},"user": {"wp_user_id": 1,"user_login": "john@d.com","display_name": "john d","user_firstname": "john","user_lastname": "d","user_email": "john@d.com","user_role": ["customer","subscriber"]},"subscription": 5861,"subscription_name": "Demo Subscription"}', true );
				$context['pluggable_data'] = $context;
				$context['response_type']  = 'sample';
			}
		} elseif ( 'renewal_payment_failed' == $data['search_term'] ) {
			$subscription_item = $data['filter']['subscription_item']['value'];
			$subscription_ids  = wcs_get_subscriptions_for_product( $subscription_item );
			$ids               = [];          
			$related           = [];
			foreach ( $subscription_ids as $subscription ) {
				$ids[] = $subscription;
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$subscriptions = new WC_Subscription( $subscription );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$related[] = $subscriptions->get_related_orders();
			}

			$failed_payment_orders = [];
			foreach ( $related as $value ) {
				foreach ( $value as $val ) {
					if ( wcs_order_contains_renewal( $val ) ) {
						$order = wc_get_order( $val );
						if ( $order ) {
							/**
							 *
							 * Ignore line
							 *
							 * @phpstan-ignore-next-line
							 */
							$payment_status = $order->get_status();
							if ( 'failed' === $payment_status || 'wc-on-hold' === $payment_status ) {
								$failed_payment_orders[] = $val;
							}
						}
					}
				}
			}

			if ( ! empty( $failed_payment_orders ) ) {
				$failed                = array_rand( $failed_payment_orders );
				$related_subscriptions = wcs_get_subscriptions_for_order( $failed_payment_orders[ $failed ] );
				if ( ! empty( $related_subscriptions ) ) {
					$sub_id       = get_post_meta( $failed_payment_orders[ $failed ], '_subscription_renewal', true );
					$subscription = wcs_get_subscription( $sub_id );
		
					$items       = $subscription->get_items();
					$product_ids = [];
					foreach ( $items as $item ) {
						$product_ids[] = $item->get_product_id();
					}

					$context['user'] = WordPress::get_user_context( $subscription->get_user_id() );
					foreach ( [ 'renewal' ] as $order_type ) {
						foreach ( $subscription->get_related_orders( 'ids', $order_type ) as $order_id ) {
							$context['subscription_related_order'][] = $order_id;
						}
					}
					foreach ( $product_ids as $val ) {
						$context['subscription_item'] = $val;
						$context['subscription_name'] = get_the_title( $val );
					}
					$context['data']           = $subscription->get_data();
					$context['pluggable_data'] = $context;
					$context['response_type']  = 'live';
				} else {
					$context['pluggable_data'] = json_decode( '{"user": {"wp_user_id": 36,"user_login": "john@d.com","display_name": "test","user_firstname": "Test","user_lastname": "test","user_email": "john@d.com","user_role": ["wpamelia-customer","customer"]},"subscription_related_order": [6643],"subscription_item": 5962,"subscription_name": "Demo2 Subscription","data": {"id": 6642,"parent_id": 6641,"status": "on-hold","currency": "USD","version": "7.6.1","prices_include_tax": false,"date_created": {"date": "2023-10-20 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-20 08:45:02.000000","timezone_type": 1,"timezone": "+00:00"},"discount_total": "0","discount_tax": "0","shipping_total": "0","shipping_tax": "0","cart_tax": "0","total": "1.00","total_tax": "0","customer_id": 36,"order_key": "wc_order_6Pdirh5WDDiVA","billing": {"first_name": "bella","last_name": "test","company": "","address_1": "test","address_2": "","city": "test","state": "TN","postcode": "600144","country": "IN","email": "john@d.com","phone": "98675757"},"shipping": {"first_name": "bella","last_name": "test","company": "","address_1": "test","address_2": "","city": "Chennai","state": "TN","postcode": "600100","country": "IN","phone": ""},"payment_method": "","payment_method_title": "","transaction_id": "","customer_ip_address": "127.0.0.1","customer_user_agent": "Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/118.0.0.0 Safari\/537.36","created_via": "checkout","customer_note": "","date_completed": null,"date_paid": null,"cart_hash": "","order_stock_reduced": false,"download_permissions_granted": true,"new_order_email_sent": false,"recorded_sales": false,"recorded_coupon_usage_counts": false,"billing_period": "day","billing_interval": "1","suspension_count": 1,"requires_manual_renewal": true,"cancelled_email_sent": "","trial_period": "day","schedule_trial_end": {"date": "2023-10-21 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_next_payment": {"date": "2023-10-21 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_end": {"date": "2023-10-22 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_start": {"date": "2023-10-22 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"switch_data": [],"number": "6642","meta_data": [{"id": 13719,"key": "is_vat_exempt","value": "no"}],"line_items": {"142": []},"tax_lines": [],"shipping_lines": [],"fee_lines": [],"coupon_lines": []}}', true );
					$context['response_type']  = 'sample';
				}
			} else {
				$context['pluggable_data'] = json_decode( '{"user": {"wp_user_id": 36,"user_login": "john@d.com","display_name": "test","user_firstname": "Test","user_lastname": "test","user_email": "john@d.com","user_role": ["wpamelia-customer","customer"]},"subscription_related_order": [6643],"subscription_item": 5962,"subscription_name": "Demo2 Subscription","data": {"id": 6642,"parent_id": 6641,"status": "on-hold","currency": "USD","version": "7.6.1","prices_include_tax": false,"date_created": {"date": "2023-10-20 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-20 08:45:02.000000","timezone_type": 1,"timezone": "+00:00"},"discount_total": "0","discount_tax": "0","shipping_total": "0","shipping_tax": "0","cart_tax": "0","total": "1.00","total_tax": "0","customer_id": 36,"order_key": "wc_order_6Pdirh5WDDiVA","billing": {"first_name": "bella","last_name": "test","company": "","address_1": "test","address_2": "","city": "test","state": "TN","postcode": "600144","country": "IN","email": "john@d.com","phone": "98675757"},"shipping": {"first_name": "bella","last_name": "test","company": "","address_1": "test","address_2": "","city": "Chennai","state": "TN","postcode": "600100","country": "IN","phone": ""},"payment_method": "","payment_method_title": "","transaction_id": "","customer_ip_address": "127.0.0.1","customer_user_agent": "Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/118.0.0.0 Safari\/537.36","created_via": "checkout","customer_note": "","date_completed": null,"date_paid": null,"cart_hash": "","order_stock_reduced": false,"download_permissions_granted": true,"new_order_email_sent": false,"recorded_sales": false,"recorded_coupon_usage_counts": false,"billing_period": "day","billing_interval": "1","suspension_count": 1,"requires_manual_renewal": true,"cancelled_email_sent": "","trial_period": "day","schedule_trial_end": {"date": "2023-10-21 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_next_payment": {"date": "2023-10-21 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_end": {"date": "2023-10-22 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"schedule_start": {"date": "2023-10-22 08:44:25.000000","timezone_type": 1,"timezone": "+00:00"},"switch_data": [],"number": "6642","meta_data": [{"id": 13719,"key": "is_vat_exempt","value": "no"}],"line_items": {"142": []},"tax_lines": [],"shipping_lines": [],"fee_lines": [],"coupon_lines": []}}', true );
				$context['response_type']  = 'sample';
			}
		}
		return $context;
	}

	/**
	 * Get Masteriyo LMS Courses.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_masteriyo_lms_courses( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'mto-course',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$courses = get_posts( $args );

		$course_count = wp_count_posts( 'mto-course' )->publish;

		$options = [];
		if ( ! empty( $courses ) ) {
			if ( is_array( $courses ) ) {
				foreach ( $courses as $course ) {
					$options[] = [
						'label' => $course->post_title,
						'value' => $course->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $course_count > $limit && $course_count > $offset,
		];
	}

	/**
	 * Get Masteriyo LMS Lessons.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_masteriyo_lms_lessons( $data ) {

		$course_id = $data['dynamic'];
		$page      = $data['page'];
		$limit     = Utilities::get_search_page_limit();
		$offset    = $limit * ( $page - 1 );
		$options   = [];
		$args      = [
			'post_type'      => 'mto-lesson',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => '_course_id',
					'value'   => $course_id,
					'compare' => '=',
				],
			],
		];

		$lessons      = get_posts( $args );
		$lesson_count = wp_count_posts( 'mto-lesson' )->publish;
		if ( ! empty( $lessons ) ) {
			if ( is_array( $lessons ) ) {
				foreach ( $lessons as $lesson ) {
					$options[] = [
						'label' => $lesson->post_title,
						'value' => $lesson->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $lesson_count > $limit && $lesson_count > $offset,
		];
	}

	/**
	 * Get Masteriyo LMS Quiz.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_masteriyo_lms_quizs( $data ) {

		$course_id = $data['dynamic'];
		$page      = $data['page'];
		$limit     = Utilities::get_search_page_limit();
		$offset    = $limit * ( $page - 1 );
		$options   = [];
		$args      = [
			'post_type'      => 'mto-quiz',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'     => '_course_id',
					'value'   => $course_id,
					'compare' => '=',
				],
			],
		];

		$quizs      = get_posts( $args );
		$quiz_count = wp_count_posts( 'mto-quiz' )->publish;
		if ( ! empty( $quizs ) ) {
			if ( is_array( $quizs ) ) {
				foreach ( $quizs as $quiz ) {
					$options[] = [
						'label' => $quiz->post_title,
						'value' => $quiz->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $quiz_count > $limit && $quiz_count > $offset,
		];
	}

	/**
	 * Search Masteriyo LMS data.
	 *
	 * @param array $data data.
	 * @return array|void|mixed
	 */
	public function search_masteriyo_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		$post_id = -1;
		if ( 'course_completed' === $trigger ) {
			$post_id = $data['filter']['course_id']['value'];
		} elseif ( 'lesson_completed' === $trigger ) {
			$post_id = $data['filter']['lesson_id']['value'];
		} elseif ( 'quiz_completed' === $trigger || 'quiz_failed' === $trigger ) {
			$post_id = $data['filter']['quiz_id']['value'];
		}

		if ( 'course_completed' === $trigger || 'lesson_completed' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_user_activities as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.item_id WHERE postmeta.activity_status='completed' AND posts.post_type=%s order by postmeta.id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_user_activities as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.item_id WHERE postmeta.item_id = %s AND postmeta.activity_status='completed' AND posts.post_type=%s order by postmeta.id DESC LIMIT 1", $post_id, $post_type ) );
			}
		}

		if ( 'quiz_completed' === $trigger || 'quiz_failed' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_user_activities as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.item_id WHERE postmeta.activity_status='completed' AND postmeta.activity_type='quiz' AND posts.post_type=%s order by postmeta.user_item_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_user_activities as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.item_id WHERE postmeta.item_id=%s AND postmeta.activity_type='quiz' OR postmeta.activity_status='completed' AND posts.post_type=%s order by postmeta.id DESC LIMIT 1", $post_id, $post_type ) );
			}
			if ( ! empty( $result ) ) {
				$resultt = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_quiz_attempts WHERE quiz_id=%d AND user_id=%d order by id DESC LIMIT 1", $result[0]->item_id, $result[0]->user_id ) );
				
				if ( function_exists( 'masteriyo_get_quiz' ) ) {
					$quiz = masteriyo_get_quiz( $resultt[0]->quiz_id );
					if ( is_object( $quiz ) && method_exists( $quiz, 'get_pass_mark' ) ) {
						if ( 'quiz_completed' === $trigger && $resultt[0]->earned_marks < $quiz->get_pass_mark() ) {
							$result = [];
						} elseif ( 'quiz_failed' === $trigger && $resultt[0]->earned_marks > $quiz->get_pass_mark() ) {
							$result = [];
						}
					}
				}
			}
		}

		if ( ! empty( $result ) ) {
			$result_item_id = $result[0]->item_id;
			$result_user_id = $result[0]->user_id;
			$quiz_attempt   = '';
			if ( 'quiz_completed' === $trigger || 'quiz_failed' === $trigger ) {
				$resultt      = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}masteriyo_quiz_attempts WHERE quiz_id=%d AND user_id=%d order by id DESC LIMIT 1", $result[0]->item_id, $result[0]->user_id ) );
				$quiz_attempt = $resultt[0]->id;
			}
			switch ( $trigger ) {
				case 'course_completed':
					if ( function_exists( 'masteriyo_get_course' ) ) {
						$course                    = masteriyo_get_course( $result_item_id );
						$context_data              = array_merge(
							WordPress::get_user_context( $result_user_id ),
							$course->get_data()
						);
						$context_data['course_id'] = $result_item_id;
					}
					break;
				case 'lesson_completed':
					if ( function_exists( 'masteriyo_get_lesson' ) ) {
						$lesson                    = masteriyo_get_lesson( $result_item_id );
						$context_data              = array_merge(
							WordPress::get_user_context( $result_user_id ),
							$lesson->get_data()
						);
						$context_data['lesson_id'] = $result_item_id;
					}
					break;
				case 'quiz_completed':
				case 'quiz_failed':
					if ( function_exists( 'masteriyo_get_quiz' ) ) {
						$quiz         = masteriyo_get_quiz( $result_item_id );
						$context_data = WordPress::get_user_context( $result_user_id );
						if ( function_exists( 'masteriyo_get_quiz_attempt' ) ) {
							$attempt                   = masteriyo_get_quiz_attempt( $quiz_attempt );
							$context_data['quiz_id']   = $result_item_id;
							$context_data['course_id'] = $quiz->get_course_id();
							$context_data['quiz']      = $quiz->get_data();
							$context_data['attempt']   = $attempt->get_data();
						}
					}
					break;
				default:
					return;
			}
			if ( ! empty( $context_data ) ) {
				$context['pluggable_data'] = $context_data;
				$context['response_type']  = 'live';
			}
		} elseif ( empty( $result ) ) {
			switch ( $trigger ) {
				case 'course_completed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 1,"user_login": "admin","display_name": "admin","user_firstname": "test","user_lastname": "test","user_email": "john@d.com","user_role": ["customer"],"id": 6636,"name": "Modes Master Class","slug": "modes-master-class-2","date_created": {"date": "2023-10-20 06:09:15.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-21 15:22:29.000000","timezone_type": 1,"timezone": "+00:00"},"status": "publish","menu_order": 0,"featured": false,"catalog_visibility": "visible","description": "Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words.","short_description": "","post_password": "","author_id": 1,"parent_id": 0,"reviews_allowed": true,"date_on_sale_from": null,"date_on_sale_to": null,"price": "0","regular_price": "0","sale_price": "","price_type": "free","category_ids": [106,107],"tag_ids": [],"difficulty_id": 0,"featured_image": 6616,"rating_counts": [],"average_rating": "0","review_count": 0,"enrollment_limit": 0,"duration": 360,"access_mode": "open","billing_cycle": "","show_curriculum": true,"purchase_note": "","highlights": "<li>Suscipit tortor eget felis.<\/li><li>Curabitur arcu erat idimper.<\/li><li>Lorem ipsum dolor sit amet.<\/li>","is_ai_created": false,"is_creating": false,"meta_data": []},"response_type":"sample"}  ';
					break;
				case 'lesson_completed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 1,"user_login": "admin","display_name": "admin","user_firstname": "test","user_lastname": "test","user_email": "john@d.com","user_role": ["customer"],"id": 6636,"name": "Lesson 1","slug": "lesson-1","date_created": {"date": "2023-10-20 06:09:15.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-21 15:22:29.000000","timezone_type": 1,"timezone": "+00:00"},"status": "publish","menu_order": 0,"featured": false,"catalog_visibility": "visible","description": "new lesson","short_description": "","post_password": "","author_id": 1,"parent_id": 0,"course_id": 6594,"reviews_allowed": true,"featured_image": 6616,"rating_counts": [],"average_rating": "0","review_count": 0,"meta_data": [],"lesson_id": "6596"},"response_type":"sample"}  ';
					break;
				case 'quiz_completed':
				case 'quiz_failed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 18,"user_login": "john@yopmail.com","display_name": "johns john","user_firstname": "johns","user_lastname": "john","user_email": "john@d.com","user_role": ["customer"],"quiz_id": "6654","course_id": 6634,"quiz": {"id": 6654,"name": "New Quiz","slug": "new-quiz","date_created": {"date": "2023-10-23 16:36:14.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-23 17:10:42.000000","timezone_type": 1,"timezone": "+00:00"},"parent_id": 6630,"course_id": 6634,"author_id": 0,"menu_order": 4,"status": "publish","description": "","short_description": "","pass_mark": 2,"full_mark": 100,"duration": 0,"attempts_allowed": 0,"questions_display_per_page": 0,"meta_data": []},"attempt": {"id": 17,"course_id": 6634,"quiz_id": 6654,"user_id": "18","total_questions": 2,"total_answered_questions": 2,"total_marks": "100.00","total_attempts": 1,"total_correct_answers": 1,"total_incorrect_answers": 1,"earned_marks": "1.00","answers": {"6655": {"answered": "False","correct": false},"6656": {"answered": "False","correct": true}},"attempt_status": "attempt_ended","attempt_started_at": {"date": "2023-10-24 09:34:16.000000","timezone_type": 1,"timezone": "+00:00"},"attempt_ended_at": {"date": "2023-10-24 09:34:29.000000","timezone_type": 1,"timezone": "+00:00"},"meta_data": []}},"response_type":"sample"}';
					break;
				default:
					return;
			}
			$context = json_decode( $sample_data, true );
		}

		return $context;
	}

	/**
	 * Search learndash user added in group.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_pluggables_user_added_in_group( $data ) {
		$context = [];

		if ( ! function_exists( 'learndash_get_groups_user_ids' ) ) {
			return;
		}
		$term     = $data['search_term'];
		$group_id = $data['filter']['sfwd_group_id']['value'];
		
		if ( 'user_added_group' == $term ) {
			if ( -1 === $group_id ) {
				$args         = [
					'numberposts' => 1,
					'orderby'     => 'rand',
					'post_type'   => 'groups',
				];
				$posts        = get_posts( $args );
				$random_value = $posts[0]->ID;
				$group_users  = learndash_get_groups_user_ids( $random_value );
				$group_id     = $random_value;
			} else {
				$group_users = learndash_get_groups_user_ids( $group_id );
			}
			$users = get_users(
				[
					'meta_key' => 'learndash_group_' . $group_id . '_enrolled_at',
					'orderby'  => 'meta_value',
					'order'    => 'DESC',
					'number'   => 1,
					'include'  => $group_users,
				]
			);
		} elseif ( 'user_removed_group' == $term ) {
			if ( -1 === $group_id ) {
				$args         = [
					'numberposts' => 1,
					'orderby'     => 'rand',
					'post_type'   => 'groups',
				];
				$posts        = get_posts( $args );
				$random_value = $posts[0]->ID;
				$group_id     = $random_value;
			}
			$args  = [
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => 'group_' . $group_id . '_access_from',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'learndash_group_' . $group_id . '_enrolled_at',
						'compare' => 'EXISTS',
					],
				],
				'orderby'    => 'meta_value',
				'order'      => 'DESC',
				'number'     => 1,
			];
			$users = get_users( $args );
		} elseif ( 'leader_added_group' == $term || 'leader_removed_group' == $term ) {
			if ( -1 === $group_id ) {
				$args         = [
					'numberposts' => 1,
					'fields'      => 'ids',
					'orderby'     => 'rand',
					'post_type'   => 'groups',
				];
				$posts        = get_posts( $args );
				$random_key   = array_rand( $posts );
				$random_value = $posts[ $random_key ];
				$group_id     = $random_value;
			}
			$user_query_args = [
				'orderby'    => 'learndash_group_leaders_' . intval( $group_id ),
				'order'      => 'DESC',
				'meta_query' => [
					[
						'key'     => 'learndash_group_leaders_' . intval( $group_id ),
						'value'   => intval( $group_id ),
						'compare' => '=',
						'type'    => 'NUMERIC',
					],
				],
			];
			$users           = get_users( $user_query_args );
		}

		if ( ! empty( $users ) ) {
			$context                             = WordPress::get_user_context( $users[0]->ID );
			$context['sfwd_group_id']            = $group_id;
			$context['group_title']              = get_the_title( $group_id );
			$context['group_url']                = get_permalink( $group_id );
			$context['group_featured_image_id']  = get_post_meta( $group_id, '_thumbnail_id', true );
			$context['group_featured_image_url'] = get_the_post_thumbnail_url( $group_id );
			$context['response_type']            = 'live';
		} else {
			$context                             = WordPress::get_sample_user_context();
			$context['group_title']              = 'Test Group';
			$context['sfwd_group_id']            = 112;
			$context['group_url']                = 'https://example.com/test-group';
			$context['group_featured_image_id']  = 113;
			$context['group_featured_image_url'] = 'https://example.com/test-group-img';
			$context['response_type']            = 'sample';
		}

		$context['pluggable_data'] = $context;
		return $context;
	}

	/**
	 * Search learndash user enrolled in course.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_pluggables_user_enrolled_course( $data ) {
		global $wpdb;
		$context = [];

		if ( ! function_exists( 'ld_course_access_expires_on' ) ||
		! function_exists( 'learndash_group_enrolled_courses' ) ||
		! function_exists( 'learndash_get_lesson_list' ) ) {
			return;
		}
		$term      = $data['search_term'];
		$course_id = isset( $data['filter']['sfwd_course_id'] ) ? $data['filter']['sfwd_course_id']['value'] : '';
		$group_id  = isset( $data['filter']['sfwd_group_id'] ) ? $data['filter']['sfwd_group_id']['value'] : '';
		
		if ( 'course_enrolled' == $term ) {
			if ( -1 === $course_id ) {
				$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='access' AND activity.activity_status= %d order by activity_id DESC LIMIT 1", 0 ) );
			} else {
				$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='access' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d order by activity_id DESC LIMIT 1", 0, $course_id, $course_id ) );
			}
		} elseif ( 'course_unenrolled' == $term ) {
			if ( -1 === $course_id ) {
				$courses   = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='access' AND activity.activity_status= %d order by activity_id DESC LIMIT 1", 0 ) );
				$course_id = $courses[0]->course_id;
			}
			$args  = [
				'meta_query' => [
					'relation' => 'AND',
					[
						'key'     => 'course_' . $course_id . '_access_from',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'learndash_course_' . $course_id . '_enrolled_at',
						'compare' => 'EXISTS',
					],
				],
			];
			$users = get_users( $args );
		} elseif ( 'course_access_expired' == $term ) {
			if ( -1 === $course_id ) {
				$course_args   = [
					'post_type'      => 'sfwd-courses',
					'posts_per_page' => 1,
					'orderby'        => 'rand',
					'order'          => 'ASC',
					'post_status'    => 'publish',
				];
				$courses_posts = get_posts( $course_args );
				$course_id     = $courses_posts[0]->ID;
			}
			$args  = [
				'meta_query' => [
					[
						'key'     => 'learndash_course_expired_' . $course_id,
						'compare' => 'EXISTS',
					],
				],
			];
			$users = get_users( $args );
		} elseif ( 'group_course_completed' == $term ) {
			if ( -1 === $group_id ) {
				$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='group_progress' AND activity.activity_status= %d order by activity_id DESC LIMIT 1", 1 ) );
			} else {
				$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='group_progress' AND activity.activity_status= %d AND activity.post_id= %d order by activity_id DESC LIMIT 1", 1, $group_id ) );
			}
			$activity_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity_meta WHERE activity_id = %d", $courses[0]->activity_id ) );
		} elseif ( 'course_group_added' == $term ) {
			if ( -1 === $group_id ) {
				$args         = [
					'numberposts' => 1,
					'fields'      => 'ids',
					'orderby'     => 'rand',
					'post_type'   => 'groups',
				];
				$posts        = get_posts( $args );
				$random_key   = array_rand( $posts );
				$random_value = $posts[ $random_key ];
				$group_id     = $random_value;
			}
			$courses = learndash_group_enrolled_courses( $group_id );
		} elseif ( 'assignment_submitted' == $term ) {
			$course_id = isset( $data['filter']['sfwd-courses'] ) ? $data['filter']['sfwd-courses']['value'] : '';
			$lesson_id = $data['filter']['sfwd_lesson_topic_id']['value'];
			if ( -1 === $course_id && -1 === $lesson_id ) {
				$args = [
					'post_type'      => 'sfwd-assignment',
					'posts_per_page' => 1,
					'order'          => 'DESC',
					'post_status'    => 'publish',
				];
			} else {
				if ( -1 === $course_id ) {
					$courses    = get_posts(
						[
							'posts_per_page' => - 1,
							'post_type'      => 'sfwd-courses',
							'post_status'    => 'publish',
							'fields'         => 'ids',
						]
					);
					$course_key = array_rand( $courses );
					$course_id  = $courses[ $course_key ];
				}
				if ( -1 === $lesson_id ) {
					$args = [
						'post_type'      => 'sfwd-assignment',
						'posts_per_page' => 1,
						'order'          => 'DESC',
						'post_status'    => 'publish',
						'meta_query'     => [
							[
								'key'     => 'course_id',
								'value'   => $course_id,
								'compare' => '=',
							],
						],
					];
				} else {
					$args = [
						'post_type'      => 'sfwd-assignment',
						'posts_per_page' => 1,
						'order'          => 'DESC',
						'post_status'    => 'publish',
						'meta_query'     => [
							'relation' => 'AND',
							[
								'key'     => 'lesson_id',
								'value'   => $lesson_id,
								'compare' => '=',
							],
							[
								'key'     => 'course_id',
								'value'   => $course_id,
								'compare' => '=',
							],
						],
					];
				}
			}
			$assignments = get_posts( $args );
		} elseif ( 'assignment_graded' == $term ) {
			$course_id = isset( $data['filter']['sfwd-courses'] ) ? $data['filter']['sfwd-courses']['value'] : '';
			$lesson_id = $data['filter']['sfwd_lesson_topic_id']['value'];
			if ( -1 === $course_id && -1 === $lesson_id ) {
				$args = [
					'post_type'      => 'sfwd-assignment',
					'posts_per_page' => 1,
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'meta_query'     => [
						[
							'key'     => 'approval_status',
							'compare' => 'EXISTS',
						],
					],
				];
			} else {
				if ( -1 === $course_id ) {
					$courses    = get_posts(
						[
							'posts_per_page' => - 1,
							'post_type'      => 'sfwd-courses',
							'post_status'    => 'publish',
							'fields'         => 'ids',
						]
					);
					$course_key = array_rand( $courses );
					$course_id  = $courses[ $course_key ];
				}
				if ( -1 === $lesson_id ) {
					$lessons = learndash_get_lesson_list( $course_id, [ 'num' => 0 ] );
					if ( ! empty( $lessons ) ) {
						$random_key   = array_rand( $lessons );
						$random_value = $lessons[ $random_key ];
						$lesson_id    = $random_value->ID;
					}
				}
				$args = [
					'post_type'      => 'sfwd-assignment',
					'posts_per_page' => 1,
					'order'          => 'DESC',
					'post_status'    => 'publish',
					'meta_query'     => [
						'relation' => 'AND',
						[
							'key'     => 'lesson_id',
							'value'   => $lesson_id,
							'compare' => '=',
						],
						[
							'key'     => 'course_id',
							'value'   => $course_id,
							'compare' => '=',
						],
						[
							'key'     => 'approval_status',
							'compare' => 'EXISTS',
						],
					],
				];
			}
			$assignments = get_posts( $args );
		}
		
		if ( 'assignment_submitted' == $term || 'assignment_graded' == $term ) {
			if ( ! empty( $assignments ) ) {
				$context_data                     = WordPress::get_user_context( (int) $assignments[0]->post_author );
				$context_data['assignment_id']    = $assignments[0]->ID;
				$context_data['assignment_title'] = $assignments[0]->post_title;
				$context_data['assignment_url']   = get_post_meta( $assignments[0]->ID, 'file_link', true );
				$context_data['lesson_id']        = get_post_meta( $assignments[0]->ID, 'lesson_id', true );
				$context_data['course_id']        = get_post_meta( $assignments[0]->ID, 'course_id', true );
				$context_data['points']           = get_post_meta( $assignments[0]->ID, 'points', true );
				$context['response_type']         = 'live';
			} else {
				$context_data                     = WordPress::get_sample_user_context();
				$context_data['assignment_title'] = 'Test Assignment';
				$context_data['assignment_id']    = 112;
				$context_data['assignment_url']   = 'https://example.com/test_assignment.pdf';
				$context_data['lesson_id']        = 2;
				$context_data['course_id']        = 1;
				$context_data['points']           = '12';
				$context['response_type']         = 'sample';
			}
		} elseif ( 'course_unenrolled' == $term || 'course_access_expired' == $term ) {
			if ( ! empty( $users ) ) {
				$context_data                              = WordPress::get_user_context( $users[0]->ID );
				$context_data['sfwd_course_id']            = $course_id;
				$context_data['course_title']              = get_the_title( $course_id );
				$context_data['course_url']                = get_permalink( $course_id );
				$context_data['course_featured_image_id']  = get_post_meta( $course_id, '_thumbnail_id', true );
				$context_data['course_featured_image_url'] = get_the_post_thumbnail_url( $course_id );
				$timestamp                                 = ld_course_access_expires_on( $course_id, $users[0]->ID );
				$timestamp                                 = is_numeric( $timestamp ) ? (int) $timestamp : null;
				$date_format                               = get_option( 'date_format' );
				if ( is_string( $date_format ) ) {
					$context_data['course_access_expiry_date'] = wp_date( $date_format, $timestamp );
				}
				$context['response_type'] = 'live';
			} else {
				$context_data                              = WordPress::get_sample_user_context();
				$context_data['course_name']               = 'Test Course';
				$context_data['sfwd_course_id']            = 112;
				$context_data['course_url']                = 'https://example.com/test-course';
				$context_data['course_featured_image_id']  = 113;
				$context_data['course_featured_image_url'] = 'https://example.com/test-course-img';
				$context_data['course_access_expiry_date'] = '2023-10-20';
				$context['response_type']                  = 'sample';
			}
		} elseif ( 'course_group_added' == $term ) {
			if ( ! empty( $courses ) ) {
				$context_data['course_id']                 = $courses[0];
				$context_data['course_title']              = get_the_title( $courses[0] );
				$context_data['course_url']                = get_permalink( $courses[0] );
				$context_data['course_featured_image_id']  = get_post_meta( $courses[0], '_thumbnail_id', true );
				$context_data['course_featured_image_url'] = get_the_post_thumbnail_url( $courses[0] );
				$context_data['sfwd_group_id']             = $group_id;
				$context_data['group_name']                = get_the_title( $group_id );
				$context['response_type']                  = 'live';
			} else {
				$context_data                              = WordPress::get_sample_user_context();
				$context_data['course_name']               = 'Test Course';
				$context_data['sfwd_course_id']            = 112;
				$context_data['course_url']                = 'https://example.com/test-course';
				$context_data['course_featured_image_id']  = 113;
				$context_data['course_featured_image_url'] = 'https://example.com/test-course-img';
				$context_data['course_access_expiry_date'] = '2023-10-20';
				$context_data['sfwd_group_id']             = 12;
				$context_data['group_name']                = 'Test Group';
				$context['response_type']                  = 'sample';
			}
		} elseif ( 'group_course_completed' == $term ) {
			if ( ! empty( $courses ) && ! empty( $activity_meta ) ) {
				$context_data                             = WordPress::get_user_context( $courses[0]->user_id );
				$context_data['sfwd_group_id']            = $courses[0]->post_id;
				$context_data['group_title']              = get_the_title( $courses[0]->post_id );
				$context_data['group_url']                = get_permalink( $courses[0]->post_id );
				$context_data['group_featured_image_id']  = get_post_meta( $courses[0]->post_id, '_thumbnail_id', true );
				$context_data['group_featured_image_url'] = get_the_post_thumbnail_url( $courses[0]->post_id );
				$course_ids                               = null;
				foreach ( $activity_meta as $item ) {
					if ( 'course_ids' === $item->activity_meta_key ) {
						$course_ids = unserialize( $item->activity_meta_value );
						break;
					}
				}
				if ( ! empty( $course_ids ) && is_array( $course_ids ) ) {
					foreach ( $course_ids as $key => $course_id ) {
						if ( is_int( $course_id ) ) {
							$args  = [
								'include'    => [ $courses[0]->user_id ],
								'meta_query' => [
									[
										'key'     => 'course_completed_' . $course_id,
										'compare' => 'EXISTS',
									],
								],
							];
							$users = get_users( $args );
							if ( ! empty( $users ) ) {
								$context_data[ 'completed ' . $key ]['course_id']                 = $course_id;
								$context_data[ 'completed ' . $key ]['course_title']              = get_the_title( $course_id );
								$context_data[ 'completed ' . $key ]['course_url']                = get_permalink( $course_id );
								$context_data[ 'completed ' . $key ]['course_featured_image_id']  = get_post_meta( $course_id, '_thumbnail_id', true );
								$context_data[ 'completed ' . $key ]['course_featured_image_url'] = get_the_post_thumbnail_url( $course_id );
								$timestamp   = ld_course_access_expires_on( $course_id, $courses[0]->user_id );
								$timestamp   = is_numeric( $timestamp ) ? (int) $timestamp : null;
								$date_format = get_option( 'date_format' );
								if ( is_string( $date_format ) ) {
									$context_data[ 'completed ' . $key ]['course_access_expiry_date'] = wp_date( $date_format, $timestamp );
								}
							}
						}
					}
				}
				$context['response_type'] = 'live';
			} else {
				$context_data                             = WordPress::get_sample_user_context();
				$context_data['sfwd_group_id']            = 112;
				$context_data['group_title']              = 'Test Group';
				$context_data['group_url']                = 113;
				$context_data['group_featured_image_id']  = 11;
				$context_data['group_featured_image_url'] = 'https://example.com/test-group-img';
				$context_data['completed 0']              = [ 
					'course_id'                 => 10,
					'course_title'              => 'Test Course',
					'course_url'                => 'https://example.com/test-course',
					'course_featured_image_id'  => 14,
					'course_featured_image_url' => 'https://example.com/test-course-img',
				];
				$context['response_type']                 = 'sample';
			}
		} else {
			if ( ! empty( $courses ) ) {
				$context_data                              = WordPress::get_user_context( $courses[0]->user_id );
				$context_data['course_id']                 = $courses[0]->course_id;
				$context_data['course_title']              = get_the_title( $courses[0]->course_id );
				$context_data['course_url']                = get_permalink( $courses[0]->course_id );
				$context_data['course_featured_image_id']  = get_post_meta( $courses[0]->course_id, '_thumbnail_id', true );
				$context_data['course_featured_image_url'] = get_the_post_thumbnail_url( $courses[0]->course_id );
				$timestamp                                 = ld_course_access_expires_on( $courses[0]->course_id, $courses[0]->user_id );
				$timestamp                                 = is_numeric( $timestamp ) ? (int) $timestamp : null;
				$date_format                               = get_option( 'date_format' );
				if ( is_string( $date_format ) ) {
					$context_data['course_access_expiry_date'] = wp_date( $date_format, $timestamp );
				}
				if ( $courses[0]->post_id ) {
					$context_data['sfwd_group_id']            = $courses[0]->post_id;
					$context_data['group_title']              = get_the_title( $courses[0]->post_id );
					$context_data['group_url']                = get_permalink( $courses[0]->post_id );
					$context_data['group_featured_image_id']  = get_post_meta( $courses[0]->post_id, '_thumbnail_id', true );
					$context_data['group_featured_image_url'] = get_the_post_thumbnail_url( $courses[0]->post_id );
				}
				$context['response_type'] = 'live';
			} else {
				$context_data                              = WordPress::get_sample_user_context();
				$context_data['course_name']               = 'Test Course';
				$context_data['sfwd_course_id']            = 112;
				$context_data['course_url']                = 'https://example.com/test-course';
				$context_data['course_featured_image_id']  = 113;
				$context_data['course_featured_image_url'] = 'https://example.com/test-course-img';
				$context_data['course_access_expiry_date'] = '2023-10-20';
				$context['response_type']                  = 'sample';
			}
		}

		$context['pluggable_data'] = $context_data;
		return $context;
	}

	/**
	 * Search LearnDash Lesson Topic List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_lessons_topics_list( $data ) {
		$options   = [];
		$course_id = $data['dynamic']['sfwd-courses'];

		if ( ! function_exists( 'learndash_get_lesson_list' ) || ! function_exists( 'learndash_get_topic_list' ) ) {
			return [];
		}

		$lessons = learndash_get_lesson_list( $course_id, [ 'num' => 0 ] );
		foreach ( $lessons as $lesson ) {
			$options[] = [
				'label' => $lesson->post_title,
				'value' => $lesson->ID,
			];
			$topics    = learndash_get_topic_list( $lesson->ID, $course_id );
			foreach ( $topics as $topic ) {
				$options[] = [
					'label' => $topic->post_title,
					'value' => $topic->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search LearnDash Quiz List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_quiz_list( $data ) {
		$options   = [];
		$course_id = $data['dynamic']['sfwd-courses'];
		$lesson_id = $data['dynamic']['sfwd_lessons_topics'];

		if ( ! function_exists( 'learndash_get_course_quiz_list' ) || ! function_exists( 'learndash_get_lesson_quiz_list' ) ) {
			return [];
		}

		$quizzes = learndash_get_course_quiz_list( $course_id );
		$quizzes = array_merge( $quizzes, learndash_get_lesson_quiz_list( $lesson_id, null, $course_id ) );
		if ( ! empty( $quizzes ) ) {
			foreach ( $quizzes as $quiz ) {
				$options[] = [
					'label' => $quiz['post']->post_title,
					'value' => $quiz['post']->ID,
					
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search LearnDash Quiz Essay Question List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_quiz_essay_question_list( $data ) {
		$options = [];
		$quiz_id = $data['dynamic'];

		if ( ! function_exists( 'learndash_get_quiz_questions' ) ) {
			return [];
		}

		if ( 0 < $quiz_id ) {
			$quiz_question_ids = learndash_get_quiz_questions( $quiz_id );
			if ( ! empty( $quiz_question_ids ) ) {
				foreach ( $quiz_question_ids as $question_post_id => $question_pro_id ) {
					$question_type = get_post_meta( $question_post_id, 'question_type', true );
					if ( is_string( $question_type ) && 'essay' === $question_type ) {
						$title     = html_entity_decode( get_the_title( $question_post_id ), ENT_QUOTES, 'UTF-8' );
						$options[] = [
							'label' => $title,
							'value' => $question_post_id,
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search LearnDash Lessons List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_lessons_list( $data ) {
		$options   = [];
		$course_id = $data['dynamic']['sfwd-courses'];

		if ( ! function_exists( 'learndash_get_lesson_list' ) ) {
			return [];
		}

		$lessons = learndash_get_lesson_list( $course_id, [ 'num' => 0 ] );
		foreach ( $lessons as $lesson ) {
			$options[] = [
				'label' => $lesson->post_title,
				'value' => $lesson->ID,
			];
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}
	
	/**
	 * Search LearnDash Topics List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_topics_list( $data ) {
		$options   = [];
		$course_id = $data['dynamic']['sfwd-courses'];
		$lesson_id = $data['dynamic']['sfwd-lessons'];

		if ( ! function_exists( 'learndash_get_topic_list' ) ) {
			return [];
		}

		$topics = learndash_get_topic_list( $lesson_id, $course_id );
		foreach ( $topics as $topic ) {
			$options[] = [
				'label' => $topic->post_title,
				'value' => $topic->ID,
			];
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search LearnDash Assignments List.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_ld_assignments_list( $data ) {
		$options   = [];
		$course_id = $data['dynamic']['sfwd-courses'];
		$lesson_id = $data['dynamic']['sfwd_lesson_topic_id'];

		$args       = [
			'post_type'   => 'sfwd-assignment',
			'numberposts' => - 1,
		];
		$meta_query = [];
		if ( ! empty( $course_id ) ) {
			$meta_query[] = [
				'key'     => 'course_id',
				'value'   => (int) $course_id,
				'compare' => '=',
			];
		}
		if ( ! empty( $lesson_id ) ) {
			$meta_query[] = [
				'key'     => 'lesson_id',
				'value'   => (int) $lesson_id,
				'compare' => '=',
			];
		}
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
			if ( count( $meta_query ) > 1 ) {
				$args['meta_query']['relation'] = 'AND';
			}
		}
		$assignments = get_posts( $args );
		foreach ( $assignments as $assignment ) {
			$options[] = [
				'label' => $assignment->post_title,
				'value' => $assignment->ID,
			];
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search post by post type.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_edd_prices( $data ) {
		$options     = [];
		$downlaod_id = $data['dynamic']['download_id'];

		$variable_prices = get_post_meta( $downlaod_id, 'edd_variable_prices', true );
		if ( ! empty( $variable_prices ) && is_array( $variable_prices ) ) {
			foreach ( $variable_prices as $price_id => $price ) {
				if ( isset( $price['name'] ) ) {
					$options[] = [
						'label' => $price['name'] . '(' . $price['amount'] . ')',
						'value' => $price['index'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}
	
	/**
	 * GetPowerful Docs list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pfd_docs_list( $data ) {

		$course_id = $data['dynamic'];
		$page      = $data['page'];
		$limit     = Utilities::get_search_page_limit();
		$offset    = $limit * ( $page - 1 );
		$options   = [];
		$args      = [
			'post_type'      => 'docs',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$docs       = get_posts( $args );
		$docs_count = wp_count_posts( 'docs' )->publish;
		if ( ! empty( $docs ) ) {
			if ( is_array( $docs ) ) {
				foreach ( $docs as $doc ) {
					$options[] = [
						'label' => $doc->post_title,
						'value' => $doc->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $docs_count > $limit && $docs_count > $offset,
		];
	}

	/**
	 * Search Powerful Docs last data.
	 *
	 * @param array $data data.
	 * @return array|void|mixed
	 */
	public function search_pfd_feedback_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		$post_id = $data['filter']['doc_id']['value'];

		if ( -1 === $post_id ) {
			$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pfd_feedbacks order by id DESC LIMIT 1" );
		} else {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}pfd_feedbacks WHERE doc_id=%s order by id DESC LIMIT 1", $post_id ) );
		}

		if ( ! empty( $result ) ) {
			$context_data                     = [
				'feedback' => $result[0]->feedback,
				'comment'  => $result[0]->comment,
				'doc_id'   => $result[0]->doc_id,
				'time'     => $result[0]->time,
			];
			$context_data['doc_name']         = get_the_title( $result[0]->doc_id );
			$context_data['doc_link']         = get_the_permalink( $result[0]->doc_id );
			$author_id                        = get_post_field( 'post_author', $result[0]->doc_id );
			$email                            = get_the_author_meta( 'user_email', intval( '"' . $author_id . '"' ) );
			$context_data['doc_author_email'] = $email;
			$context['pluggable_data']        = $context_data;
			$context['response_type']         = 'live';
		} elseif ( empty( $result ) ) {
			$sample_data = '{"pluggable_data":{"feedback": "yes","comment": "helped me out!!","doc_id": "6689","time": "2023-11-09 11:56:48","doc_name": "First doc","doc_link": "https://example.com","doc_author_email": "john@example.com"},"response_type":"sample"}';
			$context     = json_decode( $sample_data, true );
		}
		return $context;
	}

	/**
	 * WooCommerce Coupon Discount type list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_woo_coupon_discount_type_list( $data ) {
		$options = [];
		
		$discount_types = wc_get_coupon_types();

		if ( ! empty( $discount_types ) ) {
			if ( is_array( $discount_types ) ) {
				foreach ( $discount_types as $key => $value ) {
					$options[] = [
						'label' => $value,
						'value' => $key,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * WooCommerce Product list along with variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_woo_product_variation_list( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		if ( ! function_exists( 'wc_get_products' ) ) {
			return;
		}
		$products = wc_get_products(
			[
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);


		$prod_count = wp_count_posts( 'product' )->publish;

		if ( ! empty( $products ) ) {
			if ( is_array( $products ) ) {
				foreach ( $products as $product ) {
					$options[] = [
						'label' => $product->get_name(),
						'value' => $product->get_id(),
					];
					if ( $product->is_type( 'variable' ) ) {
						$variations = Utilities::get_product_variations( $product->get_id() );
						foreach ( $variations['result'] as $variation ) {
							$options[] = [
								'label' => $variation->post_title,
								'value' => $variation->ID,
							];
						}
					}
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $prod_count > $limit && $prod_count > $offset,
		];
	}

	/**
	 * WooCommerce Product list along with variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_woo_coupon_list( $data ) {
		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$coupons      = get_posts(
			[
				'posts_per_page' => - 1,
				'orderby'        => 'name',
				'order'          => 'asc',
				'post_type'      => 'shop_coupon',
				'post_status'    => 'publish',
			]
		);
		$coupon_count = wp_count_posts( 'shop_coupon' )->publish;

		if ( ! empty( $coupons ) ) {
			if ( is_array( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					$code      = wc_format_coupon_code( $coupon->post_title );
					$options[] = [
						'label' => $code,
						'value' => $code,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $coupon_count > $limit && $coupon_count > $offset,
		];
	}

	/**
	 * Prepare LatePoint Bookings List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_bookings_list( $data ) {

		if ( ! class_exists( 'OsBookingHelper' ) ) {
			return [];
		}

		$bookings = OsBookingHelper::get_bookings_for_select();
		$options  = [];

		if ( ! empty( $bookings ) ) {
			foreach ( $bookings as $key => $booking ) {
				$options[] = [
					'label' => $booking['label'],
					'value' => $booking['value'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare LatePoint Services List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_services_list( $data ) {

		if ( ! class_exists( 'OsServiceHelper' ) ) {
			return [];
		}

		$services = OsServiceHelper::get_services_list();
		$options  = [];

		if ( ! empty( $services ) ) {
			foreach ( $services as $key => $service ) {
				$options[] = [
					'label' => $service['label'],
					'value' => $service['value'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare LatePoint Agents List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_agents_list( $data ) {

		if ( ! class_exists( 'OsAgentHelper' ) ) {
			return [];
		}

		$agent_ids_for_service = OsAgentHelper::get_agent_ids_for_service_and_location( $data['dynamic'] );
		$agents                = OsAgentHelper::get_agents_list();
		$options               = [];

		if ( ! empty( $agents ) ) {
			foreach ( $agents as $key => $agent ) {
				if ( in_array( $agent['value'], $agent_ids_for_service ) ) {
					$options[] = [
						'label' => $agent['label'],
						'value' => $agent['value'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare LatePoint Agents WP User List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_user_agents_list( $data ) {

		if ( ! class_exists( 'OsWpUserHelper' ) ) {
			return [];
		}
		$agents  = OsWpUserHelper::get_wp_users_for_select( [ 'role' => 'latepoint_agent' ] );
		$options = [];
		if ( ! empty( $agents ) ) {
			$options = $agents;
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare LatePoint Statuses List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_statuses_list( $data ) {

		if ( ! class_exists( 'OsBookingHelper' ) ) {
			return [];
		}

		$statuses = OsBookingHelper::get_statuses_list();
		$options  = [];

		if ( ! empty( $statuses ) ) {
			foreach ( $statuses as $key => $label ) {
				$options[] = [
					'label' => $label,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare LatePoint Customers List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_lp_customers_list( $data ) {

		if ( ! class_exists( 'OsCustomerHelper' ) ) {
			return [];
		}

		$customers = OsCustomerHelper::get_customers_for_select();
		$options   = [];

		if ( ! empty( $customers ) ) {
			foreach ( $customers as $key => $customer ) {
				$options[] = [
					'label' => $customer['label'],
					'value' => $customer['value'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare SureForms Forms List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_sureforms_form_list( $data ) {

		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forms      = get_posts(
			[
				'posts_per_page' => - 1,
				'orderby'        => 'name',
				'order'          => 'asc',
				'post_type'      => 'sureforms_form',
				'post_status'    => 'publish',
			]
		);
		$form_count = wp_count_posts( 'sureforms_form' )->publish;

		if ( ! empty( $forms ) ) {
			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$title     = html_entity_decode( get_the_title( $form->ID ), ENT_QUOTES, 'UTF-8' );
					$options[] = [
						'label' => $title,
						'value' => $form->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];
	}

	/**
	 * Prepare Academy Course List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_ac_lms_courses( $data ) {

		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$courses      = get_posts(
			[
				'posts_per_page' => - 1,
				'orderby'        => 'name',
				'order'          => 'asc',
				'post_type'      => 'academy_courses',
				'post_status'    => 'publish',
			]
		);
		$course_count = wp_count_posts( 'academy_courses' )->publish;

		if ( ! empty( $courses ) ) {
			if ( is_array( $courses ) ) {
				foreach ( $courses as $course ) {
					$options[] = [
						'label' => get_the_title( $course->ID ),
						'value' => $course->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $course_count > $limit && $course_count > $offset,
		];
	}

	/**
	 * Prepare Academy Lesson List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_ac_lms_lessons( $data ) {

		$options = [];

		if ( ! class_exists( '\Academy\Helper' ) ) {
			return [];
		}

		$curriculums = \Academy\Helper::get_course_curriculum( $data['dynamic'] );
		
		if ( ! empty( $curriculums ) ) {
			foreach ( $curriculums as $topic ) {
				if ( isset( $topic['topics'] ) && is_array( $topic['topics'] ) ) {
					foreach ( $topic['topics'] as $lesson ) {
						if ( isset( $lesson['type'] ) && 'lesson' === $lesson['type'] ) {
							$options[] = [
								'label' => $lesson['name'],
								'value' => $lesson['id'],
							];
						}
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Academy Quiz List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_ac_lms_quiz( $data ) {

		$options = [];

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$quizs      = get_posts(
			[
				'posts_per_page' => - 1,
				'orderby'        => 'name',
				'order'          => 'asc',
				'post_type'      => 'academy_quiz',
				'post_status'    => 'publish',
			]
		);
		$quiz_count = wp_count_posts( 'academy_quiz' )->publish;

		if ( ! empty( $quizs ) ) {
			if ( is_array( $quizs ) ) {
				foreach ( $quizs as $quiz ) {
					$options[] = [
						'label' => get_the_title( $quiz->ID ),
						'value' => $quiz->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $quiz_count > $limit && $quiz_count > $offset,
		];
	}

	/**
	 * Search Academy LMS data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_ac_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( ! class_exists( '\Academy\Helper' ) ) {
			return [];
		}

		$course_id = -1;
		$lesson_id = -1;
		$quiz_id   = -1;
		if ( 'ac_lms_course_completed' === $trigger ) {
			$course_id = $data['filter']['course']['value'];
		} elseif ( 'ac_lms_lesson_completed' === $trigger ) {
			$course_id = $data['filter']['course']['value'];
			$lesson_id = $data['filter']['lesson']['value'];
		} elseif ( 'ac_lms_quiz_completed' === $trigger || 'ac_lms_quiz_failed' === $trigger ) {
			$quiz_id = $data['filter']['quiz']['value'];
		} elseif ( 'ac_lms_enrolled_course' === $trigger ) {
			$course_id = $data['filter']['course']['value'];
		}

		$users = get_users(
			[
				'fields'   => 'ID',
				'meta_key' => 'is_academy_student',
			]
		);
		if ( ! empty( $users ) ) {
			$user_random_key = array_rand( $users );
			$user_id         = $users[ $user_random_key ];
		}

		if ( 'ac_lms_course_completed' === $trigger ) {
			if ( -1 === $course_id ) {
				$result = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$wpdb->prefix}comments
				as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.comment_post_ID WHERE postmeta.comment_type='course_completed' AND posts.post_type=%s order by postmeta.comment_ID DESC LIMIT 1",
						$post_type 
					) 
				);
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.comment_post_ID WHERE postmeta.comment_post_ID = %s AND postmeta.comment_type='course_completed' AND posts.post_type=%s order by postmeta.comment_ID DESC LIMIT 1", $course_id, $post_type ) );
			}
		} elseif ( 'ac_lms_lesson_completed' === $trigger ) {
			if ( -1 === $course_id ) {
				$courses     = get_posts(
					[
						'posts_per_page' => - 1,
						'post_type'      => 'academy_courses',
						'post_status'    => 'publish',
						'fields'         => 'ids',
					]
				);
				$course_id   = array_rand( $courses );
				$option_name = 'academy_course_' . $course_id . '_completed_topics';
			} else {
				$option_name = 'academy_course_' . $course_id . '_completed_topics';
			}
			if ( ! empty( $users ) ) {
				$meta = get_user_meta( $user_id, $option_name, true );
				if ( is_string( $meta ) ) {
					$saved_topics_lists = (array) json_decode( $meta, true );
					if ( -1 === $lesson_id ) {
						$result = $saved_topics_lists['lesson'];
					} else {
						if ( is_array( $saved_topics_lists['lesson'] ) ) {
							if ( array_key_exists( $lesson_id, $saved_topics_lists['lesson'] ) ) {
								$result = $saved_topics_lists['lesson'];
							}
						}
					}
				}
			}
		} elseif ( 'ac_lms_quiz_completed' === $trigger ) {
			if ( -1 === $quiz_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}academy_quiz_attempts WHERE attempt_status='passed' order by attempt_id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}academy_quiz_attempts WHERE quiz_id=%s AND attempt_status='passed' order by attempt_id DESC LIMIT 1", $quiz_id ) );
			}
		} elseif ( 'ac_lms_quiz_failed' === $trigger ) {
			if ( -1 === $quiz_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}academy_quiz_attempts WHERE attempt_status='failed' order by attempt_id DESC LIMIT 1" );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}academy_quiz_attempts WHERE quiz_id=%s AND attempt_status='failed' order by attempt_id DESC LIMIT 1", $quiz_id ) );
			}
		} elseif ( 'ac_lms_enrolled_course' === $trigger ) {
			if ( -1 === $course_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = %s order by ID DESC LIMIT 1", 'academy_enrolled' ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type = %s AND post_parent = %d order by ID DESC LIMIT 1", 'academy_enrolled', $course_id ) );
			}   
		}

		if ( ! empty( $result ) ) {
			switch ( $trigger ) {
				case 'ac_lms_course_completed':
					$data                        = WordPress::get_post_context( $result[0]->comment_post_ID );
					$context_data                = WordPress::get_user_context( $result[0]->user_id );
					$context_data['course_data'] = $data;
					$context_data['course']      = $result[0]->comment_post_ID;
					break;
				case 'ac_lms_enrolled_course':
					$data                            = WordPress::get_post_context( $result[0]->post_parent );
					$context_data                    = WordPress::get_user_context( $result[0]->post_author );
					$context_data['course_data']     = $data;
					$context_data['enrollment_data'] = $result[0];
					$context_data['course']          = $result[0]->post_parent;
					break;
				case 'ac_lms_lesson_completed':
					if ( -1 === $lesson_id ) {
						$key         = array_rand( $result );
						$lesson_data = \Academy\Helper::get_lesson( $key );
					} else {
						$lesson_data = \Academy\Helper::get_lesson( $lesson_id );
					}
					if ( is_object( $lesson_data ) ) {
						$lesson_data = get_object_vars( $lesson_data );
					}
					if ( ! empty( $users ) ) {
						$context_data = array_merge( $lesson_data, WordPress::get_user_context( $user_id ) );
					}
					$context_data['course_data'] = WordPress::get_post_context( $course_id );
					$context_data['lesson']      = $lesson_id;
					$context_data['course']      = $course_id;
					break;
				case 'ac_lms_quiz_completed':
				case 'ac_lms_quiz_failed':
					$context_data                         = WordPress::get_user_context( $result[0]->user_id );
					$context_data['quiz_data']            = WordPress::get_post_context( $result[0]->quiz_id );
					$context_data['quiz_attempt_details'] = $result;
					$context_data['quiz']                 = $result[0]->quiz_id;
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context_data;
			$context['response_type']  = 'live';
		} elseif ( empty( $result ) ) {
			switch ( $trigger ) {
				case 'ac_lms_course_completed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 73, "user_login": "abc@yopmail.com","display_name": "data data1", "user_firstname": "data","user_lastname": "data1","user_email": "abc@yopmail.com","user_role": [],"course_data": {"ID": 6949,"post_author": "1","post_date": "2023-11-29 05:37:50","post_date_gmt": "2023-11-29 05:37:50","post_content": "<!-- wp:paragraph -->\n<p>this is a business course.<\/p>\n<!-- \/wp:paragraph -->","post_title": "Business Course","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "open","post_password": "","post_name": "business-course","to_ping": "","pinged": "","post_modified": "2023-11-29 09:50:27","post_modified_gmt": "2023-11-29 09:50:27","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/course\/business-course\/","menu_order": 0,"post_type": "academy_courses","post_mime_type": "","comment_count": "0","filter": "raw"},"course": 1},"response_type":"sample"}';
					break;
				case 'ac_lms_lesson_completed':
					$sample_data = '{"pluggable_data":{"ID": "1","lesson_author": "1","lesson_date": "2023-11-29 07:01:03","lesson_date_gmt": "2023-11-29 07:01:03","lesson_title": "Lesson 1","lesson_name": "","lesson_content": "","lesson_excerpt": "","lesson_status": "publish","comment_status": "close","comment_count": "0","lesson_password": "","lesson_modified": "2023-11-29 07:01:03","lesson_modified_gmt": "2023-11-29 07:01:03","wp_user_id": 73,"user_login": "abc@yopmail.com","display_name": "data data1","user_firstname": "data","user_lastname": "data1","user_email": "abc@yopmail.com","user_role": [],"course_data": {"ID": 6949,"post_author": "1","post_date": "2023-11-29 05:37:50","post_date_gmt": "2023-11-29 05:37:50","post_content": "<!-- wp:paragraph -->\n<p>this is a business course.<\/p>\n<!-- \/wp:paragraph -->","post_title": "Business Course","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "open","post_password": "","post_name": "business-course","to_ping": "","pinged": "","post_modified": "2023-11-29 09:50:27","post_modified_gmt": "2023-11-29 09:50:27","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/course\/business-course\/","menu_order": 0,"post_type": "academy_courses","post_mime_type": "","comment_count": "0","filter": "raw"},"lesson": 1,"course":1},"response_type":"sample"}';
					break;
				case 'ac_lms_quiz_completed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 73,"user_login": "abc@yopmail.com","display_name": "data data1","user_firstname": "data","user_lastname": "data1","user_email": "abc@yopmail.com","user_role": [],"quiz_data": {"ID": 6960,"post_author": "1","post_date": "2023-11-29 09:49:42","post_date_gmt": "2023-11-29 09:49:42","post_content": "","post_title": "First Quiz","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "closed","post_password": "","post_name": "first-quiz","to_ping": "","pinged": "","post_modified": "2023-11-29 09:50:11","post_modified_gmt": "2023-11-29 09:50:11","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/?post_type=academy_quiz&#038;p=6960","menu_order": 0,"post_type": "academy_quiz","post_mime_type": "","comment_count": "0","filter": "raw"}, "quiz_attempt_details": [{"attempt_id": "2","course_id": "6949","quiz_id": "6960","user_id": "126","total_questions": "1","total_answered_questions": "1","total_marks": "20.00","earned_marks": "20.00","attempt_info": "{\"total_correct_answers\":1}","attempt_status": "passed","attempt_ip": "127.0.0.1","attempt_started_at": "2023-11-30 06:20:10","attempt_ended_at": "2023-11-30 06:20:10","is_manually_reviewed": null,"manually_reviewed_at": null}],"quiz":1},"response_type":"sample"}';
					break;
				case 'ac_lms_quiz_failed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 73,"user_login": "abc@yopmail.com","display_name": "data data1","user_firstname": "data","user_lastname": "data1","user_email": "abc@yopmail.com","user_role": [],"quiz_data": {"ID": 6960,"post_author": "1","post_date": "2023-11-29 09:49:42","post_date_gmt": "2023-11-29 09:49:42","post_content": "","post_title": "First Quiz","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "closed","post_password": "","post_name": "first-quiz","to_ping": "","pinged": "","post_modified": "2023-11-29 09:50:11","post_modified_gmt": "2023-11-29 09:50:11","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/?post_type=academy_quiz&#038;p=6960","menu_order": 0,"post_type": "academy_quiz","post_mime_type": "","comment_count": "0","filter": "raw"}, "quiz_attempt_details": [{"attempt_id": "2","course_id": "6949","quiz_id": "6960","user_id": "126","total_questions": "1","total_answered_questions": "1","total_marks": "20.00","earned_marks": "0.00","attempt_info": "{\"total_correct_answers\":0}","attempt_status": "failed","attempt_ip": "127.0.0.1","attempt_started_at": "2023-11-30 06:20:10","attempt_ended_at": "2023-11-30 06:20:10","is_manually_reviewed": null,"manually_reviewed_at": null}],"quiz":1},"response_type":"sample"}';
					break;
				case 'ac_lms_enrolled_course':
					$sample_data = '{"pluggable_data":{"course_data": {"ID": 6949,"post_author": "1","post_date": "2023-11-29 05:37:50","post_date_gmt": "2023-11-29 05:37:50","post_content": "<!-- wp:paragraph -->\n<p>this is a business course.<\/p>\n<!-- \/wp:paragraph -->","post_title": "Business Course","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "open","post_password": "","post_name": "business-course","to_ping": "","pinged": "","post_modified": "2023-11-29 09:50:27","post_modified_gmt": "2023-11-29 09:50:27","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/course\/business-course\/","menu_order": 0,"post_type": "academy_courses","post_mime_type": "","comment_count": "0","filter": "raw"},"enrollment_data": {"ID": "6971","post_author": "126","post_date": "2023-11-30 05:52:54","post_date_gmt": "2023-11-30 05:52:54","post_content": "","post_title": "Course Enrolled November 30, 2023 @ 5:52 am","post_excerpt": "","post_status": "completed","comment_status": "closed","ping_status": "closed","post_password": "","post_name": "course-enrolled-november-30-2023-552-am","to_ping": "","pinged": "","post_modified": "2023-11-30 05:52:54","post_modified_gmt": "2023-11-30 05:52:54","post_content_filtered": "","post_parent": "6949","guid": "https:\/\/suretriggers-wpnew.local\/?p=6971","menu_order": "0","post_type": "academy_enrolled","post_mime_type": "","comment_count": "0"},"course":1, "wp_user_id": 2,"user_login": "test","display_name": "test test","user_firstname": "test","user_lastname": "test", "user_email": "test@yopmail.com","user_role": ["academy_student"]},"response_type":"sample"}';
					break;
				default:
					return;
			}
			$context = (array) json_decode( $sample_data, true );
		}
		return $context;
	}

	/**
	 * Search myCred Point Type List.
	 *
	 * @param array $data Search Params.
	 * @return array
	 */
	public function search_mycred_point_type_list( $data ) {

		$options = [];

		if ( ! function_exists( 'mycred_get_types' ) ) {
			return [];
		}

		$posts = mycred_get_types();

		if ( ! empty( $posts ) ) {
			if ( is_array( $posts ) ) {
				foreach ( $posts as $key => $post ) {
					$options[] = [
						'label' => $post,
						'value' => $key,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}
	
	/**
	 * Prepare elementor forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_new_elementor_form_fields( $data ) {

		$fields         = [];
		$select_form_id = $data['dynamic'];
		global $wpdb;
		$post_metas = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value
		FROM $wpdb->postmeta pm
			LEFT JOIN $wpdb->posts p
				ON p.ID = pm.post_id
		WHERE p.post_type IS NOT NULL
		AND p.post_status = %s
		AND pm.meta_key = %s
		AND pm.`meta_value` LIKE %s",
				'publish',
				'_elementor_data',
				'%%form_fields%%'
			)
		);

		if ( ! empty( $post_metas ) ) {
			foreach ( $post_metas as $post_meta ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$inner_forms = Utilities::search_elementor_forms( json_decode( $post_meta->meta_value ) );
				if ( ! empty( $inner_forms ) ) {
					foreach ( $inner_forms as $form ) {
						$form_id = explode( '_', $select_form_id );
						if ( is_object( $form ) ) {
							if ( $form->id == $form_id[1] ) {
								if ( ! empty( $form->settings->form_fields ) ) {
									foreach ( $form->settings->form_fields as $field ) {
										$fields[] = [
											'value' => $field->custom_id,
											'text'  => ! empty( $field->field_label ) ? $field->field_label : 'unknown',
										];
									}
								}
							}
						}
					}
				}
			}
		}
		$options = [];
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $key => $value ) {
				$options[] = [
					'label' => $value['text'],
					'value' => $value['value'],
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}
	/**
	 * Get Fluent Booking Appointment Events.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_booking_calendars_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$calendars = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, title FROM {$wpdb->prefix}fcal_calendars WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d",
				[ 'active', $limit, $offset ]
			),
			OBJECT
		);

		$calendars_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $calendars ) ) {
			foreach ( $calendars as $calendar ) {
				$options[] = [
					'label' => $calendar->title,
					'value' => $calendar->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => $calendars_count > $limit && $calendars_count > $offset,
		];

	}
	
	/**
	 * Get Fluent Booking Appointment Events.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_booking_events_list( $data ) {

		global $wpdb;
		$page        = $data['page'];
		$limit       = Utilities::get_search_page_limit();
		$offset      = $limit * ( $page - 1 );
		$calendar_id = sanitize_text_field( $data['dynamic']['calender_id'] );
		if ( '-1' === $calendar_id ) {
			$events = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS id, title FROM {$wpdb->prefix}fcal_calendar_events WHERE status = %s ORDER BY id DESC LIMIT %d OFFSET %d",
					[ 'active', $limit, $offset ]
				),
				OBJECT
			);
		} else {
			$events = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT SQL_CALC_FOUND_ROWS id, title FROM {$wpdb->prefix}fcal_calendar_events WHERE status = %s AND calendar_id = %d ORDER BY id DESC LIMIT %d OFFSET %d",
					[ 'active', $calendar_id, $limit, $offset ]
				),
				OBJECT
			);
		}
	
		$events_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$options[] = [
					'label' => $event->title,
					'value' => $event->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => $events_count > $limit && $events_count > $offset,
		];

	}
	
	/**
	 * Get Fluent Booking last data.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_booking_last_data( $data ) {
		global $wpdb;
		$trigger     = $data['search_term'];
		$course_data = [];
		$lesson_data = [];

		$sample_data = '{"pluggable_data":{"booking":{"id":"7","hash":"dc641fd972e27be079945e3f998def39","calendar_id":"1","event_id":"1","group_id":"7","fcrm_id":null,"parent_id":null,"host_user_id":"1","person_user_id":"1","person_contact_id":null,"person_time_zone":"Asia/Calcutta","start_time":"2024-01-29 11:00:00","end_time":"2024-01-29 11:15:00","slot_minutes":"15","first_name":"Sure","last_name":"Test","email":"dev-email@wpengine.local","message":"","internal_note":null,"phone":"","country":null,"ip_address":"","browser":null,"device":null,"other_info":null,"location_details":{"type":"in_person_guest","description":"Location description."},"cancelled_by":"1","status":"completed","source":"web","booking_type":"scheduling","event_type":"single","payment_status":null,"payment_method":null,"source_url":"https://connector.com/fluent-booking/","source_id":null,"utm_source":"","utm_medium":"","utm_campaign":"","utm_term":"","created_at":"2024-01-29 06:46:22","updated_at":"2024-01-29 06:57:42","custom_fields":null},"event":{"id":"1","hash":"38c65708b797f5d6956070896f792b34","user_id":"1","calendar_id":"1","duration":"15","title":"One to one","slug":"15min","media_id":null,"description":"","settings":{"schedule_type":"weekly_schedules","weekly_schedules":{"sun":{"enabled":false,"slots":[]},"mon":{"enabled":true,"slots":[{"start":"03:30","end":"11:30"}]},"tue":{"enabled":true,"slots":[{"start":"03:30","end":"11:30"}]},"wed":{"enabled":true,"slots":[{"start":"03:30","end":"11:30"}]},"thu":{"enabled":true,"slots":[{"start":"03:30","end":"11:30"}]},"fri":{"enabled":true,"slots":[{"start":"03:30","end":"11:30"}]},"sat":{"enabled":false,"slots":[]}},"date_overrides":[],"range_type":"range_days","range_days":60,"range_date_between":["",""],"schedule_conditions":{"value":4,"unit":"hours"},"location_fields":{"conferencing":{"label":"Conferencing","options":{"google_meet":{"title":"Google Meet (Connect Google Meet First)","disabled":true,"location_type":"conferencing"},"ms_teams":{"title":"MS Teams (Connect Outlook First)","disabled":true,"location_type":"conferencing"}}},"in_person":{"label":"In Person","options":{"in_person_guest":{"title":"In Person (Attendee Address)"},"in_person_organizer":{"title":"In Person (Organizer Address)"}}},"phone":{"label":"Phone","options":{"phone_guest":{"title":"Attendee Phone Number"},"phone_organizer":{"title":"Organizer Phone Number"}}},"online":{"label":"Online","options":{"online_meeting":{"title":"Online Meeting"}}},"other":{"label":"Other","options":{"custom":{"title":"Custom"}}}},"team_members":[],"custom_redirect":{"enabled":false,"redirect_url":"","is_query_string":"no","query_string":""},"multi_duration":{"enabled":true,"default_duration":"15","available_durations":["15","30","45"]}},"availability_type":"existing_schedule","availability_id":"1","status":"active","type":"free","color_schema":"#0099ff","location_type":"","location_heading":"","location_settings":[{"type":"in_person_guest","title":"In Person (Attendee Address)","display_on_booking":""}],"event_type":"single","is_display_spots":"0","max_book_per_slot":"1","created_at":"2024-01-28 08:35:29","updated_at":"2024-01-28 08:36:06"}},"response_type":"sample"}'; //phpcs:ignore
		$context     = [
			'pluggable_data' => json_decode( $sample_data, true ), 
			'response_type'  => 'sample',
		];
		if ( 'fluent_booking_appointment_cancelled' === $trigger ) {
			$status = 'cancelled';
		} elseif ( 'fluent_booking_appointment_completed' === $trigger ) {
			$status = 'completed';
		}
		
		$event_id = (int) $data['filter']['event_id']['value'];
		if ( 'fluent_booking_appointment_cancelled' === $trigger || 'fluent_booking_appointment_completed' === $trigger ) {
			if ( $event_id > 0 ) {
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fcal_bookings WHERE event_id= %d AND status= %s ORDER BY id DESC LIMIT 1", $event_id, $status ) );
			} else {    
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fcal_bookings WHERE status= %s ORDER BY id DESC LIMIT 1", $status ) );
			}
		} elseif ( 'fluent_booking_new_appointment_booked' === $trigger ) {
			if ( $event_id > 0 ) {
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fcal_bookings WHERE event_id= %d AND status != %s ORDER BY id DESC LIMIT 1", $event_id, 'cancelled' ) );
			} else {
				$booking = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fcal_bookings WHERE status != %s ORDER BY id DESC LIMIT 1", 'cancelled' ) );
			}
		}
		
		if ( ! empty( $booking ) ) {
			$booking_meta = $wpdb->get_row( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}fcal_booking_meta WHERE booking_id= %d AND meta_key= 'custom_fields_data'", $booking->id ) );

			$booking_event               = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}fcal_calendar_events WHERE id= %d", $booking->event_id ) );
			$booking_data                = $booking;
			$booking_data->custom_fields = $booking_meta->value;
			$booking_array               = [
				'booking' => $booking_data,
				'event'   => $booking_event,
			];
			
			
			$booking_array_response    = self::recursive_unserialize( $booking_array );
			$context['pluggable_data'] = $booking_array_response;
			$context['response_type']  = 'live';
		}
		return $context;
	}

	/**
	 * Recursive unserilize.
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed
	 */
	public static function recursive_unserialize( $data ) {
		if ( is_array( $data ) ) {
			foreach ( $data as $key => $value ) {
				$data[ $key ] = self::recursive_unserialize( $value );
			}
			return $data;
		} elseif ( is_object( $data ) && 'stdClass' === get_class( $data ) ) {
			foreach ( $data as $property => $value ) {
				$data->$property = self::recursive_unserialize( $value );
			}
			return $data;
		} elseif ( is_string( $data ) && self::is_serialized( strval( $data ) ) ) {
			return unserialize( $data );
		} else {
			return $data;
		}
		
	}

	/**
	 * Check if string serialized.
	 *
	 * @param string $data data.
	 *
	 * @return bool
	 */
	public static function is_serialized( $data ) {
		$unserialized = unserialize( $data );
		if ( 'b:0;' === $data || false !== $unserialized ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Search Tutor LMS data.
	 *
	 * @param array $data data.
	 * @return array|void|mixed
	 */
	public function search_tutor_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( ! function_exists( 'tutor_utils' ) ) {
			return [];
		}

		$post_id = -1;
		if ( 'course_enrolled' === $trigger || 'tutor_courses_question' === $trigger ) {
			$post_id = $data['filter']['tutor_course']['value'];
		} elseif ( 'quiz_attempt_percentage' === $trigger || 'quiz_failed' === $trigger || 'quiz_passed' === $trigger ) {
			$post_id = $data['filter']['quiz_id']['value'];
		}

		if ( 'course_enrolled' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_status='completed' AND post_type=%s order by ID DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_parent = %s AND post_status='completed' AND post_type=%s order by ID DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'tutor_courses_question' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments as comment JOIN {$wpdb->prefix}posts as posts ON posts.ID=comment.comment_post_ID WHERE comment.comment_approved='approved' AND comment.comment_type='tutor_q_and_a' AND posts.post_type=%s order by comment.comment_ID DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments as comment JOIN {$wpdb->prefix}posts as posts ON posts.ID=comment.comment_post_ID WHERE comment.comment_post_ID = %s AND comment.comment_approved='approved' AND comment.comment_type='tutor_q_and_a' AND posts.post_type=%s order by comment.comment_ID DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'quiz_passed' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE quiz.attempt_status='attempt_ended' AND quiz.earned_marks >= quiz.total_marks AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE quiz.quiz_id = %s AND quiz.attempt_status='attempt_ended' AND quiz.earned_marks >= quiz.total_marks AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'quiz_failed' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE quiz.attempt_status='attempt_ended' AND quiz.earned_marks < quiz.total_marks AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE quiz.quiz_id = %s AND quiz.attempt_status='attempt_ended' AND quiz.earned_marks < quiz.total_marks AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'quiz_attempt_percentage' == $trigger ) {
			$condition_compare = $data['filter']['condition_compare']['value'];
			$percentage        = $data['filter']['percentage']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE earned_marks $condition_compare %d AND quiz.attempt_status='attempt_ended' AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $percentage, $post_type ) ); //phpcs:ignore
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}tutor_quiz_attempts as quiz JOIN {$wpdb->prefix}posts as posts ON posts.ID=quiz.quiz_id WHERE earned_marks $condition_compare %d AND quiz.quiz_id = %s AND quiz.attempt_status='attempt_ended' AND posts.post_type=%s order by quiz.attempt_id DESC LIMIT 1", $percentage, $post_id, $post_type ) ); //phpcs:ignore
			}
		}

		if ( ! empty( $result ) ) {
			switch ( $trigger ) {
				case 'course_enrolled':
					$result_item_id               = $result[0]->post_parent;
					$result_user_id               = $result[0]->post_author;
					$context_data                 = array_merge(
						WordPress::get_user_context( $result_user_id ),
						WordPress::get_post_context( $result_item_id )
					);
					$context_data['tutor_course'] = $result_item_id;
					break;
				case 'tutor_courses_question':
					$date                         = $result[0]->comment_date;
					$data                         = [
						'comment_post_ID'  => $result[0]->comment_post_ID,
						'comment_author'   => $result[0]->comment_author,
						'comment_date'     => $date,
						'comment_date_gmt' => get_gmt_from_date( $date ),
						'comment_content'  => $result[0]->comment_content,
						'comment_approved' => 'approved',
						'comment_agent'    => 'TutorLMSPlugin',
						'comment_type'     => 'tutor_q_and_a',
						'comment_parent'   => $result[0]->comment_parent,
						'user_id'          => $result[0]->user_id,
					];
					$context_data['tutor_course'] = $result[0]->comment_post_ID;
					$context_data['data']         = $data;
					break;
				case 'quiz_attempt_percentage':
				case 'quiz_failed':
				case 'quiz_passed':
					$attempt                    = tutor_utils()->get_attempt( $result[0]->attempt_id );
					$context_data               = WordPress::get_user_context( $result[0]->user_id );
					$context_data['quiz_id']    = $attempt->quiz_id;
					$context_data['attempt_id'] = $result[0]->attempt_id;
					$context_data['attempt']    = $attempt;
					break;
				default:
					return;
			}
			if ( ! empty( $context_data ) ) {
				$context['pluggable_data'] = $context_data;
				$context['response_type']  = 'live';
			}
		} elseif ( empty( $result ) ) {
			switch ( $trigger ) {
				case 'course_enrolled':
					$sample_data = '{"pluggable_data":{"wp_user_id": 1,"user_login": "admin","display_name": "admin","user_firstname": "test","user_lastname": "test","user_email": "john@d.com","user_role": ["customer"],"id": 6636,"name": "Modes Master Class","slug": "modes-master-class-2","date_created": {"date": "2023-10-20 06:09:15.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-10-21 15:22:29.000000","timezone_type": 1,"timezone": "+00:00"},"status": "publish","menu_order": 0,"featured": false,"catalog_visibility": "visible","description": "Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words.","short_description": "","post_password": "","author_id": 1,"parent_id": 0,"reviews_allowed": true,"date_on_sale_from": null,"date_on_sale_to": null,"price": "0","regular_price": "0","sale_price": "","price_type": "free","category_ids": [106,107],"tag_ids": [],"difficulty_id": 0,"featured_image": 6616,"rating_counts": [],"average_rating": "0","review_count": 0,"enrollment_limit": 0,"duration": 360,"access_mode": "open","billing_cycle": "","show_curriculum": true,"purchase_note": "","highlights": "<li>Suscipit tortor eget felis.<\/li><li>Curabitur arcu erat idimper.<\/li><li>Lorem ipsum dolor sit amet.<\/li>","is_ai_created": false,"is_creating": false,"meta_data": []},"response_type":"sample"}  ';
					break;
				case 'tutor_courses_question':
					$sample_data = '{"pluggable_data":{"tutor_course": "74","data": {"comment_post_ID": "74","comment_author": "admin","comment_date": "2024-02-12 08:52:45","comment_date_gmt": "2024-02-12 08:52:45","comment_content": "asdsd","comment_approved": "approved","comment_agent": "TutorLMSPlugin","comment_type": "tutor_q_and_a","comment_parent": "0","user_id": "1"}},"response_type":"sample"}';
					break;
				case 'quiz_attempt_percentage':
				case 'quiz_failed':
				case 'quiz_passed':
					$sample_data = '{"pluggable_data":{"wp_user_id": 1,"user_login": "john","display_name": "john","user_firstname": "john","user_lastname": "d","user_email": "johnd@gmail.com","user_role": ["administrator","tutor_instructor"],"quiz_id": "77","attempt_id": "1","attempt": {"attempt_id": "1","course_id": "74","quiz_id": "77","user_id": "1","total_questions": "1","total_answered_questions": "1","total_marks": "10.00","earned_marks": "10.00","attempt_info": "a:9:{s:10:\"time_limit\";a:3:{s:10:\"time_value\";s:1:\"0\";s:9:\"time_type\";s:7:\"minutes\";s:18:\"time_limit_seconds\";i:0;}s:13:\"feedback_mode\";s:5:\"retry\";s:16:\"attempts_allowed\";s:2:\"10\";s:13:\"passing_grade\";s:2:\"10\";s:24:\"max_questions_for_answer\";s:2:\"10\";s:20:\"question_layout_view\";s:0:\"\";s:15:\"questions_order\";s:4:\"rand\";s:29:\"short_answer_characters_limit\";s:3:\"200\";s:34:\"open_ended_answer_characters_limit\";s:3:\"500\";}","attempt_status":"attempt_ended","attempt_ip": "::1","attempt_started_at": "2024-02-12 09:05:15","attempt_ended_at": "2024-02-12 09:05:18","is_manually_reviewed": null,"manually_reviewed_at": null}},"response_type":"sample"}';
					break;
				default:
					return;
			}
			$context = json_decode( $sample_data, true );
		}

		return $context;
	}

	/**
	 * Get Asgorus Forum list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_asgorus_forums_list( $data ) {
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return [];
		}
		$category = $data['dynamic'];
		if ( is_array( $category ) ) {
			$category_id = $category['forum_category'];
		} else {
			$category_id = $category;
		}
		$asgaros_forum = new AsgarosForum();
		$forums        = $asgaros_forum->get_forums( $category_id );
		$options       = [];
		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum ) {
				$options[] = [
					'label' => $forum->name,
					'value' => $forum->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Asgorus Categories list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_asgorus_categories_list( $data ) {
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return [];
		}
		$asgaros_forum = new AsgarosForum();
		$categories    = (array) $asgaros_forum->content->get_categories();
		$options       = [];
		
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$options[] = [
					'label' => $category->name,
					'value' => $category->term_id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Asgorus Topic lists
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_asgorus_topic_list( $data ) {
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return [];
		}
		global $wpdb;
		$forum_id      = $data['dynamic'];
		$asgaros_forum = new AsgarosForum();
		$sql           = 'SELECT name,id  FROM ' . $wpdb->prefix . 'forum_topics WHERE parent_id = %d AND closed = 0 ORDER BY id';
		$topics      = $wpdb->get_results( $wpdb->prepare( $sql, $forum_id ), ARRAY_A );// @phpcs:ignore
		$options       = [];
		
		if ( ! empty( $topics ) ) {
			foreach ( $topics as $topic ) {
				$options[] = [
					'label' => $topic['name'],
					'value' => $topic['id'],
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Asgorus Topic Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_asgaros_topic_last_data( $data ) {
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return [];
		}
		$asgaros_forum = new AsgarosForum();
		$context       = [];
		global $wpdb;
		$forum_id = $data['filter']['forum_id']['value'];

		if ( -1 == $forum_id ) {
			$results = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'forum_topics WHERE closed = 0 ORDER BY id DESC LIMIT 1', ARRAY_A );
		} else {
			$forum   = $forum_id;
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'forum_topics WHERE parent_id = %d AND closed = 0 ORDER BY id DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $forum ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$sql_post                              = 'SELECT * FROM ' . $wpdb->prefix . 'forum_posts WHERE parent_id = %d ORDER BY id DESC LIMIT 1';
			$results_post                          = $wpdb->get_results( $wpdb->prepare( $sql_post, $results[0]['id'] ), ARRAY_A ); // @phpcs:ignore
			$context['pluggable_data']['forum_id'] = $results[0]['parent_id'];
			$context['pluggable_data']['topic_id'] = $results[0]['id'];
			$context['pluggable_data']['post_id']  = $results_post[0]['id'];
			$context['pluggable_data']['forum']    = $asgaros_forum->content->get_forum( $results[0]['parent_id'] );
			$context['pluggable_data']['topic']    = $asgaros_forum->content->get_topic( $results[0]['id'] );
			$context['pluggable_data']['post']     = $asgaros_forum->content->get_post( $results_post[0]['id'] );
			
			$context['pluggable_data']['author'] = WordPress::get_user_context( $results[0]['author_id'] );
			$context['response_type']            = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"forum_id":"1","topic_id":"2","forum":{"id":"1","name":"First Forum","parent_id":"64","parent_forum":"0","description":"My first forum.","icon":"fas fa-comments","sort":"1","forum_status":"normal","slug":"first-forum"},"topic":{"id":"2","parent_id":"1","author_id":"1","views":"2","name":"other topic","sticky":"0","closed":"0","approved":"1","slug":"other-topic"},"user":{"wp_user_id":1,"user_login":"suredev","display_name":"SureDev","user_firstname":"Sure","user_lastname":"Dev","user_email":"dev-suretest@suretriggers.com","user_role":["administrator"]}},"response_type":"sample"}', true );// @phpcs:ignore
		}
		return $context;
	}

	/**
	 * Get Asgorus Topic Reply Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_asgaros_reply_last_data( $data ) {
		if ( ! class_exists( 'AsgarosForum' ) ) {
			return [];
		}
		$asgaros_forum = new AsgarosForum();
		$context       = [];
		global $wpdb;
		$forum_id = $data['filter']['forum_id']['value'];
		$topic_id = $data['filter']['forum_id']['value'];
		if ( -1 == $topic_id ) {
			$results = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'forum_posts ORDER BY id DESC LIMIT 1', ARRAY_A );
		} else {
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'forum_posts WHERE parent_id = %d ORDER BY id DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $topic_id ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$topic_id                              = $results[0]['parent_id'];
			$post_id                               = $results[0]['id'];
			$context['pluggable_data']['forum_id'] = $forum_id;
			$context['pluggable_data']['topic_id'] = $topic_id;
			$context['pluggable_data']['post_id']  = $post_id;
			$context['pluggable_data']['forum']    = $asgaros_forum->content->get_forum( $forum_id );
			$context['pluggable_data']['topic']    = $asgaros_forum->content->get_topic( $topic_id );
			$context['pluggable_data']['post']     = $asgaros_forum->content->get_post( $post_id );
			
			$context['pluggable_data']['author'] = WordPress::get_user_context( $results[0]['author_id'] );
			$context['response_type']            = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"forum_id":"1","topic_id":"2","forum":{"id":"1","name":"First Forum","parent_id":"64","parent_forum":"0","description":"My first forum.","icon":"fas fa-comments","sort":"1","forum_status":"normal","slug":"first-forum"},"topic":{"id":"2","parent_id":"1","author_id":"1","views":"2","name":"other topic","sticky":"0","closed":"0","approved":"1","slug":"other-topic"},"user":{"wp_user_id":1,"user_login":"suredev","display_name":"SureDev","user_firstname":"Sure","user_lastname":"Dev","user_email":"dev-suretest@suretriggers.com","user_role":["administrator"]}},"response_type":"sample"}', true );// @phpcs:ignore
		}
		return $context;
	}

	/**
	 * Get WPLoyalty Points Awarded Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_wp_loyalty_points_awarded_customer( $data ) {
		
		$context = [];
		global $wpdb;
		if ( ! class_exists( 'Wlr\App\Helpers\Base' ) ) {
			return [];
		}
		$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'wlr_earn_campaign_transaction WHERE transaction_type = %s ORDER BY id DESC LIMIT 1';
		$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'credit' ), ARRAY_A );// @phpcs:ignore

		if ( ! empty( $results ) ) {
			$context['pluggable_data']['user_email']    = $results[0]['user_email'];
			$context['pluggable_data']['points_earned'] = $results[0]['points'];
			$context['pluggable_data']['action_type']   = $results[0]['action_type'];
			$base_helper                                = new \Wlr\App\Helpers\Base();
			$user                                       = $base_helper->getPointUserByEmail( $results[0]['user_email'] );
			$points_sql                                 = 'SELECT * FROM ' . $wpdb->prefix . 'wlr_expire_points 
				WHERE user_email = %s ORDER BY id DESC LIMIT 1';
			$points_results      = $wpdb->get_results( $wpdb->prepare( $points_sql, $results[0]['user_email'] ), ARRAY_A );// @phpcs:ignore
			$context['pluggable_data']['user']          = $user;
			if ( ! empty( $points_results ) ) {
				$expire_date = $points_results[0]['expire_date'];
				$timestamp   = is_numeric( $expire_date ) ? (int) $expire_date : null;
				$date_format = get_option( 'date_format' );
				if ( is_string( $date_format ) ) {
					$context['pluggable_data']['point_expiry_date'] = wp_date( $date_format, $timestamp );
				}
			}
			$context['response_type'] = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"user_email": "johnd@yopmail.com","points_earned": "4","action_type": "point_for_purchase","user": {"id": "11","user_email": "johnd@yopmail.com","refer_code": "REF-Q5Z-ZFW","points": "17","used_total_points": "0","earn_total_point": "19","birth_date": "0","level_id": "0","is_banned_user": "0","is_allow_send_email": "1","birthday_date": "0000-00-00","last_login": "0","created_date": "1710304765"},"point_expiry_date": "April 27, 2024"},"response_type":"sample"}', true );// @phpcs:ignore
		}
		return $context;
	}

	/**
	 * Get WPLoyalty Campaign Type List
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_loyalty_action_type_list( $data ) {
		$options = [];
		if ( ! class_exists( 'Wlr\App\Helpers\Woocommerce' ) ) {
			return [];
		}
		
		$woocommerce_helper = new \Wlr\App\Helpers\Woocommerce();
		$action_types       = $woocommerce_helper->getActionTypes();
		
		if ( ! empty( $action_types ) ) {
			foreach ( $action_types as $key => $type ) {
				$options[] = [
					'label' => $type,
					'value' => $key,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_slicewp_last_data( $data ) {
		global $wpdb;
		
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();
		
		
		if ( ! function_exists( 'slicewp_get_affiliate' ) ) {
			return [];
		}

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( in_array( $term, [ 'slicewp_new_affiliate', 'slicewp_update_affiliate' ], true ) ) {
			$affiliate                 = [
				'id'            => 14,
				'user_id'       => 25,
				'date_created'  => '2024-03-14 12:35:50',
				'date_modified' => '2024-03-14 12:36:20',
				'payment_email' => 'testcustomer12@gmail.com',
				'website'       => '',
				'status'        => 'active',
				'parent_id'     => 0,
			];
			$context['pluggable_data'] = array_merge( $affiliate, [ 'user' => $user_data ] );
			
			if ( ( ! empty( $data['filter'] ) && '-1' === $data['filter']['affiliate_id']['value'] ) || empty( $data['filter'] ) ) {
				$query             = $wpdb->prepare(
					"
					SELECT *
					FROM {$wpdb->prefix}slicewp_affiliates
					WHERE status = %s
					ORDER BY id DESC
					LIMIT 1",
					'active'
				);
				$affiliate_results = $wpdb->get_row( $query ); //phpcs:ignore
				
			} else {
				$affiliate_id      = $data['filter']['affiliate_id']['value'];
				$query             = $wpdb->prepare(
					"
					SELECT *
					FROM {$wpdb->prefix}slicewp_affiliates
					WHERE status = %s AND id = %d
					ORDER BY id DESC
					LIMIT 1",
					'active',
					$affiliate_id
				);
				$affiliate_results = $wpdb->get_row( $query ); //phpcs:ignore
				
			}
			

			

			if ( ! empty( $affiliate_results ) ) {
				$context['pluggable_data'] = (array) $affiliate_results;
			
				$user_data                         = WordPress::get_user_context( $affiliate_results->user_id );
				$context['pluggable_data']['user'] = $user_data;
				$context['response_type']          = 'live';
			}       
		} elseif ( in_array( $term, [ 'slicewp_new_commission', 'slicewp_update_commission' ], true ) ) {
				$commission                = [
					'id'            => 14,
					'user_id'       => 25,
					'date_created'  => '2024-03-14 12:35:50',
					'date_modified' => '2024-03-14 12:36:20',
					'payment_email' => 'testcustomer12@gmail.com',
					'website'       => '',
					'status'        => 'active',
					'parent_id'     => 0,
				];
				$context['pluggable_data'] = array_merge( $commission, [ 'user' => $user_data ] );
				$affiliate_id              = $data['filter']['affiliate_id']['value'];
				if ( -1 === $data['filter']['commission_id']['value'] ) {
					$query              = $wpdb->prepare(
						"
					SELECT *
					FROM {$wpdb->prefix}slicewp_commissions WHERE affiliate_id=%d ORDER BY id DESC limit 1", 
						$affiliate_id
					);
					$commission_results = $wpdb->get_row( $query ); //phpcs:ignore
				} else {
					$commission_id      = $data['filter']['commission_id']['value'];
					$query              = $wpdb->prepare(
						"
					SELECT *
					FROM {$wpdb->prefix}slicewp_commissions WHERE id= %d AND  affiliate_id=%d ORDER BY id DESC limit 1", 
						$commission_id, 
						$affiliate_id
					);
					$commission_results = $wpdb->get_row( $query ); //phpcs:ignore
				}
				
	
				
	
				if ( ! empty( $commission_results ) ) {
					$context['pluggable_data']         = (array) $commission_results;
					$affiliate                         = slicewp_get_affiliate( $commission_results->affiliate_id );
					$user_id                           = $affiliate->get( 'user_id' );
					$user_data                         = WordPress::get_user_context( $user_id );
					$context['pluggable_data']['user'] = $user_data;
					$context['response_type']          = 'live';
				}
		}

		return $context;
	}

	
	/**
	 * Get Ninja Tables Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ninja_tables_last_data( $data ) {
		$context = [];
		global $wpdb;
		$table_id = isset( $data['filter']['table_id']['value'] ) ? $data['filter']['table_id']['value'] : -1;
	
		if ( -1 == $table_id ) {
			$results = $wpdb->get_row( 'SELECT * FROM ' . $wpdb->prefix . 'ninja_table_items ORDER BY id DESC LIMIT 1', ARRAY_A );
		} else {
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'ninja_table_items WHERE table_id = %d ORDER BY id DESC LIMIT 1';
			$results = $wpdb->get_row( $wpdb->prepare( $sql, $table_id ), ARRAY_A );// @phpcs:ignore
		}
	
		if ( ! empty( $results ) ) {
			$results['value']                   = json_decode( $results['value'], true );
			$context['pluggable_data']          = $results;
			$context['pluggable_data']['owner'] = WordPress::get_user_context( $results['owner_id'] );
			$context['response_type']           = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"id":"24","position":null,"table_id":"17484","owner_id":"29","attribute":"value","settings":null,"value":{"id":"3","name":"Suretriggers","class":"Dev","gender":"Female"},"created_at":"2024-03-21 13:11:25","updated_at":"2024-03-21 13:11:25","owner":{"wp_user_id":29,"user_login":"testingdsd","display_name":"suretest","user_firstname":"Suretrigger","user_lastname":"Dev","user_email":"johndoe@email.com","user_role":["editor"]}},"response_type":"sample"}', true );
		}
		return $context;
	}

	/**
	 * Get Ninja Tables Fields
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ninja_table_fields( $data ) {
	
		$context = [];
		global $wpdb;
		$table_id = isset( $data['dynamic'] ) ? $data['dynamic'] : -1;
		$fields   = [];
		if ( $table_id > 0 ) {
			$fields = get_post_meta( $table_id, '_ninja_table_columns', true );
		}

		return (array) $fields;
	}

	/**
	 * Get Late Point Booking Fields
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_late_point_booking_fields( $data ) {
		if ( ! class_exists( 'LatePointAddonCustomFields' ) ) {
			
			return;
		}
		global $wpdb;
		$booking_fields = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}latepoint_settings WHERE name = %s", 'custom_fields_for_booking' ) );
		$fields         = [];
		if ( ! empty( $booking_fields ) ) {
			$fields = json_decode( $booking_fields->value, true );
		}
		return (array) $fields;
	}

	/** Search Voxel fields for action.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_voxel_custom_fields( $data ) {
		$post_type_fields = [];
		$post_type        = $data['post_type'];
		if ( ! class_exists( '\Voxel\Post_Type' ) ) {
			return [];
		}
		$fields = \Voxel\Post_Type::get( $post_type )->get_fields();
		foreach ( $fields as $key => $field ) {
			if ( 'step-general' == $key ) {
				continue;
			}
			$field_props              = $field->get_props();
			$post_type_fields[ $key ] = $field_props;
		}
		$context['fields'] = $post_type_fields;
		return $context;
	}

	/**
	 * Get Voxel Post Types lists
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_voxel_posttype_list( $data ) {
		$options          = [];
		$voxel_post_types = [];
		if ( class_exists( '\Voxel\Post_Type' ) ) {
			$voxel_post_types = \Voxel\Post_Type::get_voxel_types();
		}
		
		if ( ! empty( $voxel_post_types ) ) {
			foreach ( $voxel_post_types as $key => $voxel_post_type ) {
				$post_type = get_post_type_object( $key );
				if ( $post_type ) {
					if ( in_array( $post_type->name, [ 'collection' ], true ) ) {
						continue;
					}
					$options[] = [
						'label' => esc_attr( $post_type->labels->singular_name ),
						'value' => $post_type->name,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Voxel Users Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_users_triggers_last_data( $data ) {
		
		global $wpdb;
		$context = [];
		$term    = $data['search_term'];
		if ( 'direct_message_received' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}voxel_messages WHERE receiver_type LIKE %s AND sender_type LIKE %s ORDER BY id DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'user', 'user' ), ARRAY_A );// @phpcs:ignore
		}
		if ( 'direct_message_received' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']['sender']   = WordPress::get_user_context( $results[0]['sender_id'] );
				$context['pluggable_data']['receiver'] = WordPress::get_user_context( $results[0]['receiver_id'] );
				$context['pluggable_data']['content']  = $results[0]['content'];
				$context['response_type']              = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"sender": {"wp_user_id": 1,"user_login": "admin","display_name": "Arian","user_firstname": "john","user_lastname": "d","user_email": "johnd@gmail.com","user_role": ["subscriber"]}},"receiver": {"wp_user_id": 101,"user_login": "benni","display_name": "Benni Ben","user_firstname": "Benni","user_lastname": "Ben","user_email": "benni@mailinator.com","user_role": ["subscriber"]},"content": "new message"},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}


	/**
	 * Get Voxel Membership Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_membership_triggers_last_data( $data ) {
		
		global $wpdb;
		$context = [];
		$term    = $data['search_term'];
		if ( ! class_exists( 'Voxel\Stripe' ) ) {
			return [];
		}
		if ( 'plan_activated' === $term || 'plan_canceled' === $term ) {
			$meta_key = \Voxel\Stripe::is_test_mode() ? 'voxel:test_plan' : 'voxel:plan';
			$sql      = "SELECT
				m.user_id AS id,
				u.user_login AS title,
				u.user_email AS email,
				m.meta_value AS details,
				JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) AS plan,
				CAST( JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.amount' ) ) AS SIGNED ) AS amount,
				JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.status' ) ) AS status,
				CAST( JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.created' ) ) AS DATETIME ) AS created
			FROM {$wpdb->prefix}usermeta as m
			LEFT JOIN {$wpdb->prefix}users AS u ON m.user_id = u.ID
			WHERE m.meta_key = %s  AND JSON_UNQUOTE( JSON_EXTRACT( m.meta_value, '$.plan' ) ) != 'default'
			ORDER BY m.user_id DESC
			LIMIT 25 OFFSET 0";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $meta_key ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'user_registered' === $term ) {
			$sql     = "SELECT DISTINCT u.ID
			FROM {$wpdb->prefix}users u
			JOIN {$wpdb->prefix}usermeta um ON u.ID = um.user_id
			WHERE um.meta_key = 'voxel:profile_id' ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $sql, ARRAY_A );// @phpcs:ignore
		}

		if ( 'plan_canceled' === $term ) {
			if ( ! empty( $results ) ) {
				if ( 'cancelled' == $results[0]['status'] ) {
					$context['pluggable_data']            = WordPress::get_user_context( $results[0]['id'] );
					$context['pluggable_data']['details'] = json_decode( $results[0]['details'], true );
					$context['response_type']             = 'live';
				} else {
					$context = json_decode( '{"pluggable_data":{"wp_user_id": 101,"user_login": "benni","display_name": "John D","user_firstname": "johnd","user_lastname": "D","user_email": "johnd@gmail.com","user_role": ["subscriber"],"details": {"plan": "learningmembership","type": "subscription","subscription_id": "sub_1OwOMySHDFghoeM1sInxPrG7","price_id": "price_1OwOLJSHDFghoeM177Vf8kgt","status": "cancelled","trial_end": null,"current_period_end": 1711542948,"cancel_at_period_end": true,"amount": 800,"currency": "usd","interval": "week","interval_count": 1,"created": "2024-03-20 12:35:48","metadata": {"voxel:payment_for": "membership","voxel:plan": "learningmembership"}}},"response_type":"sample"}', true );// @phpcs:ignore
				}
			} else {
				$context = json_decode( '{"pluggable_data":{"wp_user_id": 101,"user_login": "benni","display_name": "John D","user_firstname": "johnd","user_lastname": "D","user_email": "johnd@gmail.com","user_role": ["subscriber"],"details": {"plan": "learningmembership","type": "subscription","subscription_id": "sub_1OwOMySHDFghoeM1sInxPrG7","price_id": "price_1OwOLJSHDFghoeM177Vf8kgt","status": "cancelled","trial_end": null,"current_period_end": 1711542948,"cancel_at_period_end": true,"amount": 800,"currency": "usd","interval": "week","interval_count": 1,"created": "2024-03-20 12:35:48","metadata": {"voxel:payment_for": "membership","voxel:plan": "learningmembership"}}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'plan_activated' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']            = WordPress::get_user_context( $results[0]['id'] );
				$context['pluggable_data']['details'] = json_decode( $results[0]['details'], true );
				$context['response_type']             = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"wp_user_id": 101,"user_login": "benni","display_name": "John D","user_firstname": "johnd","user_lastname": "D","user_email": "johnd@gmail.com","user_role": ["subscriber"],"details": {"plan": "learningmembership","type": "subscription","subscription_id": "sub_1OwOMySHDFghoeM1sInxPrG7","price_id": "price_1OwOLJSHDFghoeM177Vf8kgt","status": "active","trial_end": null,"current_period_end": 1711542948,"cancel_at_period_end": true,"amount": 800,"currency": "usd","interval": "week","interval_count": 1,"created": "2024-03-20 12:35:48","metadata": {"voxel:payment_for": "membership","voxel:plan": "learningmembership"}}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'user_registered' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data'] = WordPress::get_user_context( $results[0]['ID'] );
				$context['response_type']  = 'live';
			} else {
				$context['pluggable_data'] = WordPress::get_sample_user_context();
				$context['response_type']  = 'sample';
			}
		}
		return $context;
	}

	/**
	 * Get Voxel Orders Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_order_triggers_last_data( $data ) {
		
		global $wpdb;
		$context = [];
		$term    = $data['search_term'];

		if ( ! class_exists( 'Voxel\Product_Types\Orders\Order' ) ) {
			return [];
		}

		if ( 'order_placed' === $term || 'order_canceled' === $term ) {
			if ( 'order_placed' === $term ) {
				$sql = "SELECT * FROM {$wpdb->prefix}vx_orders ORDER BY id DESC LIMIT 1";
			} elseif ( 'order_canceled' === $term ) {
				$sql = "SELECT * FROM {$wpdb->prefix}vx_orders WHERE status = 'canceled' ORDER BY id DESC LIMIT 1";
			}
			
			$results      = $wpdb->get_results(  $sql, ARRAY_A );// @phpcs:ignore
			if ( ! empty( $results ) ) {
				// Get Order.
				$context['pluggable_data']['id']             = $results[0]['id'];
				$context['pluggable_data']['vendor_id']      = $results[0]['vendor_id'];
				$context['pluggable_data']['details']        = json_decode( $results[0]['details'], true );
				$context['pluggable_data']['payment_method'] = $results[0]['payment_method'];
				$context['pluggable_data']['status']         = $results[0]['status'];
				$context['pluggable_data']['created_at']     = $results[0]['created_at'];
				$context['pluggable_data']['subtotal']       = $results[0]['subtotal'];
				$context['pluggable_data']['total']          = $results[0]['total'];
				// Get order items.
				$order                                        = \Voxel\Product_Types\Orders\Order::find(
					[
						'id' => $results[0]['id'],
					] 
				);
				$order_items                                  = $order->get_items();
				$context['pluggable_data']['tax_amount']      = $order->get_tax_amount();
				$context['pluggable_data']['discount_amount'] = $order->get_discount_amount();
				$context['pluggable_data']['shipping_amount'] = $order->get_shipping_amount();
				$context['pluggable_data']['order_item_count'] = $order->get_item_count();
				foreach ( $order_items as $item ) {
					$addon_data = [];
					if ( is_object( $item ) && method_exists( $item, 'get_addons' ) ) {
						// Get addon details.
						$addons     = $item->get_addons();
						$addon_data = [];
		
						// Add the addons to the simple entry.
						if ( $addons && isset( $addons['summary'] ) ) {
							$addon_data = $addons['summary'];
						}
					}
					$context['pluggable_data']['order_items'][] = [
						'id'                    => $item->get_id(),
						'type'                  => $item->get_type(),
						'currency'              => $item->get_currency(),
						'quantity'              => $item->get_quantity(),
						'subtotal'              => $item->get_subtotal(),
						'product_id'            => $item->get_post()->get_id(),
						'product_label'         => $item->get_product_label(),
						'product_thumbnail_url' => $item->get_product_thumbnail_url(),
						'product_link'          => $item->get_product_link(),
						'description'           => $item->get_product_description(),
						'addon_data'            => $addon_data,
					];
					// If booking item, get booking details.
					if ( 'booking' === $item->get_type() ) {
						$details = $item->get_order_page_details();
						$context['pluggable_data']['order_items']['booking_type'] = $details['booking']['type'];
						if ( isset( $details['booking']['count_mode'] ) ) {
							$context['pluggable_data']['order_items']['booking_count_mode'] = $details['booking']['count_mode'];
						}
						if ( 'date_range' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_start_date'] = $details['booking']['start_date'];
							$context['pluggable_data']['order_items']['booking_end_date']   = $details['booking']['end_date'];
						} elseif ( 'single_day' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_date'] = $details['booking']['date'];
						} elseif ( 'timeslots' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_date']      = $details['booking']['date'];
							$context['pluggable_data']['order_items']['booking_slot_from'] = $details['booking']['slot']['from'];
							$context['pluggable_data']['order_items']['booking_slot_to']   = $details['booking']['slot']['to'];                         
						}
					}
				}
				// Get Customer.
				$context['pluggable_data']['customer'] = WordPress::get_user_context( $results[0]['customer_id'] );
				$context['response_type']              = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"id": "15","vendor_id": null,"details": {"cart": {"type": "direct_cart","items": {"6b39iruj": {"product": {"post_id": 9211,"field_key": "product"},"stock": {"quantity": 1}}}},"pricing": {"currency": "USD","subtotal": 10,"total": 10},"status": {"last_updated": "2024-05-30 06:52:05"}},"payment_method": "offline_payment","status": "pending_approval","created_at": "2024-05-30 06:50:19","subtotal": 10,"total": 10,"tax_amount": null,"discount_amount": null,"shipping_amount": null,"order_item_count": 1,"order_items": [{"id": 11,"type": "regular","currency": "USD","quantity": 1,"subtotal": 10,"product_id": 9211,"product_label": "Pro 1","product_thumbnail_url": null,"product_link": "https:\/\/example.com\/products\/pro-1\/","description": "","addon_data": []}],"customer": {"wp_user_id": 98,"user_login": "johnd","display_name": "johndoe","user_firstname": "john","user_lastname": "d","user_email": "johnd@example.com","user_role": ["customer"]}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'order_approved' === $term || 'order_declined' === $term ) {
			if ( 'order_approved' === $term ) {
				$sql = "SELECT * FROM {$wpdb->prefix}vx_orders WHERE status = 'completed' ORDER BY id DESC LIMIT 1";
			} elseif ( 'order_declined' === $term ) {
				$sql = "SELECT * FROM {$wpdb->prefix}vx_orders WHERE status = 'canceled' ORDER BY id DESC LIMIT 1";
			}
			$results      = $wpdb->get_results(  $sql, ARRAY_A );// @phpcs:ignore
			if ( ! empty( $results ) ) {
				// Get Order.
				$context['pluggable_data']['id']             = $results[0]['id'];
				$context['pluggable_data']['vendor_id']      = $results[0]['vendor_id'];
				$context['pluggable_data']['details']        = json_decode( $results[0]['details'], true );
				$context['pluggable_data']['payment_method'] = $results[0]['payment_method'];
				$context['pluggable_data']['status']         = $results[0]['status'];
				$context['pluggable_data']['created_at']     = $results[0]['created_at'];
				$context['pluggable_data']['subtotal']       = $results[0]['subtotal'];
				$context['pluggable_data']['total']          = $results[0]['total'];
				// Get order items.
				$order                                        = \Voxel\Product_Types\Orders\Order::find(
					[
						'id' => $results[0]['id'],
					] 
				);
				$order_items                                  = $order->get_items();
				$context['pluggable_data']['tax_amount']      = $order->get_tax_amount();
				$context['pluggable_data']['discount_amount'] = $order->get_discount_amount();
				$context['pluggable_data']['shipping_amount'] = $order->get_shipping_amount();
				$context['pluggable_data']['order_item_count'] = $order->get_item_count();
				foreach ( $order_items as $item ) {
					$addon_data = [];
					if ( is_object( $item ) && method_exists( $item, 'get_addons' ) ) {
						// Get addon details.
						$addons     = $item->get_addons();
						$addon_data = [];
		
						// Add the addons to the simple entry.
						if ( $addons && isset( $addons['summary'] ) ) {
							$addon_data = $addons['summary'];
						}
					}
					$context['pluggable_data']['order_items'][] = [
						'id'                    => $item->get_id(),
						'type'                  => $item->get_type(),
						'currency'              => $item->get_currency(),
						'quantity'              => $item->get_quantity(),
						'subtotal'              => $item->get_subtotal(),
						'product_id'            => $item->get_post()->get_id(),
						'product_label'         => $item->get_product_label(),
						'product_thumbnail_url' => $item->get_product_thumbnail_url(),
						'product_link'          => $item->get_product_link(),
						'description'           => $item->get_product_description(),
						'addon_data'            => $addon_data,
					];
					// If booking item, get booking details.
					if ( 'booking' === $item->get_type() ) {
						$details = $item->get_order_page_details();
						$context['pluggable_data']['order_items']['booking_type'] = $details['booking']['type'];
						if ( isset( $details['booking']['count_mode'] ) ) {
							$context['pluggable_data']['order_items']['booking_count_mode'] = $details['booking']['count_mode'];
						}
						if ( 'date_range' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_start_date'] = $details['booking']['start_date'];
							$context['pluggable_data']['order_items']['booking_end_date']   = $details['booking']['end_date'];
						} elseif ( 'single_day' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_date'] = $details['booking']['date'];
						} elseif ( 'timeslots' === $details['booking']['type'] ) {
							$context['pluggable_data']['order_items']['booking_date']      = $details['booking']['date'];
							$context['pluggable_data']['order_items']['booking_slot_from'] = $details['booking']['slot']['from'];
							$context['pluggable_data']['order_items']['booking_slot_to']   = $details['booking']['slot']['to'];                         
						}
					}
				}
				// Get Customer.
				$context['pluggable_data']['customer'] = WordPress::get_user_context( $results[0]['customer_id'] );
				// Get Vendor.
				$context['pluggable_data']['vendor'] = WordPress::get_user_context( $results[0]['vendor_id'] );
				$context['response_type']            = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"id": "15","vendor_id": null,"details": {"cart": {"type": "direct_cart","items": {"6b39iruj": {"product": {"post_id": 9211,"field_key": "product"},"stock": {"quantity": 1}}}},"pricing": {"currency": "USD","subtotal": 10,"total": 10},"status": {"last_updated": "2024-05-30 06:52:05"}},"payment_method": "offline_payment","status": "pending_approval","created_at": "2024-05-30 06:50:19","subtotal": null,"total": null,"tax_amount": null,"discount_amount": null,"shipping_amount": null,"order_item_count": 1,"order_items": [{"id": 11,"type": "regular","currency": "USD","quantity": 1,"subtotal": 10,"product_id": 9211,"product_label": "Pro 1","product_thumbnail_url": null,"product_link": "https:\/\/example.com\/products\/pro-1\/","description": "","addon_data": []}],"vendor": {"wp_user_id": 1,"user_login": "admin","display_name": "Arian","user_firstname": "arian","user_lastname": "d","user_email": "johnd@gmail.com","user_role": {"0": "administrator","7": "academy_instructor","8": "tutor_instructor"}},"customer": {"wp_user_id": 98,"user_login": "johnd","display_name": "johndoe","user_firstname": "john","user_lastname": "d","user_email": "johnd@example.com","user_role": ["customer"]}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}

	/**
	 * Get Voxel Timeline Comments Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_timeline_triggers_last_data( $data ) {
		
		global $wpdb;
		$context = [];
		$term    = $data['search_term'];
		$sql     = '';
		if ( 'new_comment_timeline' === $term ) {
			$sql = "SELECT * FROM {$wpdb->prefix}voxel_timeline_replies WHERE parent_id IS NULL ORDER BY id DESC LIMIT 1";
		} elseif ( 'comment_reply_timeline' === $term ) {
			$sql = "SELECT * FROM {$wpdb->prefix}voxel_timeline_replies WHERE parent_id IS NOT NULL ORDER BY id DESC LIMIT 1";
		}
		$results      = $wpdb->get_results( $sql, ARRAY_A );// @phpcs:ignore
		if ( 'new_comment_timeline' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']['comment_by'] = WordPress::get_user_context( $results[0]['user_id'] );
				$context['pluggable_data']['id']         = $results[0]['id'];
				$context['pluggable_data']['status_id']  = $results[0]['status_id'];
				$context['pluggable_data']['content']    = $results[0]['content'];
				$context['response_type']                = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"comment_by": {"wp_user_id": 1,"user_login": "admin","display_name": "Johnd","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": {"0": "administrator","7": "academy_instructor","8": "tutor_instructor"}},"id": "16","status_id": "5","content": "Nice"},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'comment_reply_timeline' === $term ) {
			if ( ! empty( $results ) ) {
				$comment_sql                             = "SELECT * FROM {$wpdb->prefix}voxel_timeline_replies WHERE id = %d";
				$comment_result      = $wpdb->get_results( $wpdb->prepare( $comment_sql, $results[0]['parent_id']), ARRAY_A );// @phpcs:ignore
				$context['pluggable_data']['replied_by'] = WordPress::get_user_context( $results[0]['user_id'] );
				$context['pluggable_data']['comment_by'] = WordPress::get_user_context( $comment_result[0]['user_id'] );
				$context['pluggable_data']['comment']    = $comment_result[0]['content'];
				$context['pluggable_data']['comment_id'] = $results[0]['parent_id'];
				$context['pluggable_data']['reply_id']   = $results[0]['id'];
				$context['pluggable_data']['reply']      = $results[0]['content'];
				$context['response_type']                = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"replied_by": {"wp_user_id": 101,"user_login": "johnd","display_name": "JohnD","user_firstname": "John","user_lastname": "D","user_email": "johnd@gmail.com","user_role": ["subscriber"]},"comment_by": {"wp_user_id": 1,"user_login": "admin","display_name": "Arian","user_firstname": "Arian","user_lastname": "D","user_email": "arian2@gmail.com","user_role": ["subscriber"]},"comment": "Nice","comment_id": "16","reply_id": "17","reply": "Nice too"},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}

	/**
	 * Get Voxel Timeline Comments Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_posts_triggers_last_data( $data ) {
		$context = [];
		global $wpdb;
		$post_type = 'post';
		$term      = $data['search_term'];
		if ( isset( $data['filter']['post_type']['value'] ) ) {
			$post_type = $data['filter']['post_type']['value'];
		}
		$results = [];
		if ( 'post_approved' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s AND post_status LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare($sql, $post_type, 'publish'), ARRAY_A );// @phpcs:ignore
		} elseif ( 'post_rejected' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s AND post_status LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare($sql, $post_type, 'rejected'), ARRAY_A );// @phpcs:ignore
		} elseif ( 'post_reviewed' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}voxel_timeline vt JOIN {$wpdb->prefix}posts p ON vt.post_id = p.ID JOIN {$wpdb->prefix}postmeta pm ON vt.post_id = pm.post_id
			WHERE pm.meta_key LIKE '%voxel:review_stats%' AND p.post_type LIKE %s AND vt.details IS NOT NULL AND vt.details LIKE %s";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $post_type, '%rating%' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'post_submitted' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare($sql, $post_type), ARRAY_A );// @phpcs:ignore
		} elseif ( 'post_updated' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY post_modified DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $post_type ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'collection_post_submitted' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'collection' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'collection_post_updated' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY post_modified DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'collection' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'new_wall_post_by_user' === $term ) {
			$sql     = "SELECT vt.* FROM  {$wpdb->prefix}voxel_timeline vt JOIN {$wpdb->prefix}posts p ON vt.post_id = p.ID JOIN  {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID WHERE p.post_type LIKE %s AND vt.details IS NOT NULL AND vt.details NOT LIKE %s ORDER BY vt.id DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $post_type, '%rating%' ), ARRAY_A );// @phpcs:ignore
		}
		if ( 'post_approved' === $term || 'post_rejected' === $term || 'post_submitted' === $term || 'post_updated' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']         = WordPress::get_post_context( $results[0]['ID'] );
				$context['pluggable_data']['post'] = Voxel::get_post_fields( $results[0]['ID'] );
				$context['response_type']          = 'live';
			} else {
				$context['pluggable_data'] = [
					'ID'                    => 557,
					'post'                  => 557,
					'post_author'           => 1,
					'post_date'             => '2022-11-18 12:18:14',
					'post_date_gmt'         => '2022-11-18 12:18:14',
					'post_content'          => 'Test Post Content',
					'post_title'            => 'Test Post',
					'post_excerpt'          => '',
					'post_status'           => 'published',
					'comment_status'        => 'open',
					'ping_status'           => 'open',
					'post_password'         => '',
					'post_name'             => 'test-post',
					'to_ping'               => '',
					'pinged'                => '',
					'post_modified'         => '2022-11-18 12:18:14',
					'post_modified_gmt'     => '2022-11-18 12:18:14',
					'post_content_filtered' => '',
					'post_parent'           => 0,
					'guid'                  => 'https://example.com/test-post/',
					'menu_order'            => 0,
					'post_type'             => 'post',
					'post_mime_type'        => '',
					'comment_count'         => 0,
					'filter'                => 'raw',
				];
				$context['response_type']  = 'sample';
			}
		} elseif ( 'post_reviewed' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']                      = WordPress::get_post_context( $results[0]['post_id'] );
				$context['pluggable_data']['post']              = Voxel::get_post_fields( $results[0]['post_id'] );
				$context['pluggable_data']['review_content']    = $results[0]['content'];
				$context['pluggable_data']['review_created_at'] = $results[0]['created_at'];
				$context['pluggable_data']['review_details']    = json_decode( $results[0]['details'], true );
				$context['pluggable_data']['review_by']         = WordPress::get_user_context( $results[0]['user_id'] );
				$context['response_type']                       = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"ID": 8291,"post_author": "1","post_date": "2024-03-18 09:01:54","post_date_gmt": "2024-03-18 09:01:54","post_content": "<p>PizzaCrust - Since 2009! Whether it\u2019s our iconic Sandwiches, wooden baked pizzas, signature sauce, original fresh dough or toppings, we invest in bringing the freshest and best ingredients to bring you the tastiest meal.<\/p>","post_title": "Pizza Crust","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "closed","post_password": "","post_name": "sach-pizza","to_ping": "","pinged": "","post_modified": "2024-03-18 09:01:54","post_modified_gmt": "2024-03-18 09:01:54","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/suretriggers-wpnew.local\/places\/sach-pizza\/","menu_order": 0,"post_type": "places","post_mime_type": "","comment_count": "0","filter": "raw","review_content": "Nice one","review_created_at": "2024-04-04 12:59:22","review_details": {"rating": {"score": -1,"custom-660": -1,"custom-978": -1,"custom-271": -1}},"review_by": {"wp_user_id": 188,"user_login": "dev4","display_name": "dev4","user_firstname": "","user_lastname": "","user_email": "dev4@yopmail.com","user_role": ["subscriber"]}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'collection_post_submitted' === $term || 'collection_post_updated' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']               = Voxel::get_post_fields( $results[0]['ID'] );
				$context['pluggable_data']['collection'] = WordPress::get_post_context( $results[0]['ID'] );
				$context['response_type']                = 'live';
			} else {
				$context = json_decode(
					'{"pluggable_data":{"field_step-general": null,"field_title": "First Collection","field_items": [8909,8293],"collection": {"ID": 9142,"post_author": "35",
                "post_date": "2024-05-27 05:55:21","post_date_gmt": "2024-05-27 05:55:21","post_content": "This is a first collection","post_title": "First Collection","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "closed","post_password": "","post_name": "first-collection","to_ping": "","pinged": "","post_modified": "2024-05-27 05:55:21","post_modified_gmt": "2024-05-27 05:55:21","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/example.com\/collection\/first-collection\/","menu_order": 0,"post_type": "collection","post_mime_type": "","comment_count": "0","filter": "raw"}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'new_wall_post_by_user' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']['post'] = Voxel::get_post_fields( $results[0]['post_id'] );
				$user                              = get_userdata( $results[0]['user_id'] );
				if ( $user ) {
					$user_data                                      = (array) $user->data;
					$context['pluggable_data']['user_display_name'] = $user_data['display_name'];
					$context['pluggable_data']['user_name']         = $user_data['user_nicename'];
					$context['pluggable_data']['user_email']        = $user_data['user_email'];
					$context['pluggable_data']['user_id']           = $results[0]['user_id'];
				}
				if ( function_exists( 'Voxel\Timeline\prepare_status_json' ) && class_exists( 'Voxel\Timeline\Status' ) ) {
					// Get the status details.
					$args           = [
						'post_id'  => $results[0]['post_id'],
						'order_by' => 'created_at',
						'order'    => 'desc',
					];
					$statuses       = \Voxel\Timeline\Status::query( $args );
					$status_details = \Voxel\Timeline\prepare_status_json( $statuses[0] );
					foreach ( (array) $status_details as $key => $value ) {
						$context['pluggable_data']['wall_post'][ $key ] = $value;
					}
				}
				$context['response_type'] = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"post": {"step-general": null,"voxel:name": "johnd","voxel:avatar": [],"description": "","field_step-general": null,"field_voxel:name": "johnd","field_voxel:avatar": [],"field_description": ""},"user_display_name": "johnd","user_name": "johnd","user_email": "johnd@yopmail.com","user_id": "1","wall_post": {"content": "new wallpost","created_at": "2024-06-13 08:44:05","files": [{"source": "existing","id": 9331,"name": "download-1-1.png","alt": "","url": "https:\/\/example.com\/wp-content\/uploads\/2024\/06\/download-1-1.png","preview": "https:\/\/example.com\/wp-content\/uploads\/2024\/06\/download-1-1.png","type": "image\/png"}]}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}

	/**
	 * Get Voxel Profiles Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_voxel_profiles_triggers_last_data( $data ) {
		
		global $wpdb;
		$context = [];
		$term    = $data['search_term'];

		if ( ! class_exists( 'Voxel\Timeline\Status' ) || ! function_exists( 'Voxel\Timeline\prepare_status_json' ) || ! class_exists( 'Voxel\Post_Type' ) ) {
				return;
		}

		if ( 'profile_created' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'profile_approved' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s AND post_status LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile', 'publish' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'profile_rejected' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s AND post_status LIKE %s ORDER BY ID DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile', 'rejected' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'profile_reviewed' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}voxel_timeline vt LEFT JOIN {$wpdb->prefix}posts pm
			ON vt.post_id = pm.ID
			WHERE pm.post_type LIKE %s AND vt.details IS NOT NULL ORDER BY vt.id DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'profile_updated' === $term ) {
			$sql     = "SELECT * FROM {$wpdb->prefix}posts WHERE post_type LIKE %s ORDER BY post_modified DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile' ), ARRAY_A );// @phpcs:ignore
		} elseif ( 'profile_new_wall_post' === $term ) {
			$sql     = "SELECT vt.* FROM  {$wpdb->prefix}voxel_timeline vt JOIN {$wpdb->prefix}posts p ON vt.post_id = p.ID JOIN  {$wpdb->prefix}postmeta pm ON pm.post_id = p.ID WHERE p.post_type LIKE 'profile' ORDER BY vt.id DESC LIMIT 1";
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'profile' ), ARRAY_A );// @phpcs:ignore
		}
		if ( 'profile_created' === $term || 'profile_approved' === $term || 'profile_rejected' === $term || 'profile_updated' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data'] = Voxel::get_post_fields( $results[0]['ID'] );
				$user                      = get_userdata( $results[0]['post_author'] );
				if ( $user ) {
					$user_data = (array) $user->data;
					$context['pluggable_data']['profile_display_name'] = $user_data['display_name'];
					$context['pluggable_data']['profile_email']        = $user_data['user_email'];
					$context['pluggable_data']['profile_user_id']      = $results[0]['post_author'];
					$context['pluggable_data']['profile_id']           = $results[0]['ID'];
				}
				$context['response_type'] = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"field_step-general": null,"field_voxel:name":"johnd","field_voxel:avatar": [],"field_description": "","profile_display_name": "johnd","profile_name": "johnd","profile_email": "johnd@yopmail.com","profile_user_id": "1"},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'profile_new_wall_post' === $term ) {
			if ( ! empty( $results ) ) {
				$context['pluggable_data']['profile'] = Voxel::get_post_fields( $results[0]['post_id'] );
				$user                                 = get_userdata( $results[0]['user_id'] );
				if ( $user ) {
					$user_data = (array) $user->data;
					$context['pluggable_data']['profile_display_name'] = $user_data['display_name'];
					$context['pluggable_data']['profile_name']         = $user_data['user_nicename'];
					$context['pluggable_data']['profile_email']        = $user_data['user_email'];
					$context['pluggable_data']['profile_user_id']      = $results[0]['user_id'];
				}
				// Get the status details.
				$args           = [
					'post_id'  => $results[0]['post_id'],
					'order_by' => 'created_at',
					'order'    => 'desc',
				];
				$statuses       = \Voxel\Timeline\Status::query( $args );
				$status_details = \Voxel\Timeline\prepare_status_json( $statuses[0] );
				foreach ( (array) $status_details as $key => $value ) {
					$context['pluggable_data']['wall_post'][ $key ] = $value;
				}
				$context['response_type'] = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"profile": {"step-general": null,"voxel:name": "johnd","voxel:avatar": [],"description": "","field_step-general": null,"field_voxel:name": "johnd","field_voxel:avatar": [],"field_description": ""},"profile_display_name": "johnd","profile_name": "johnd","profile_email": "johnd@yopmail.com","profile_user_id": "1""wall_post": {"content": "new wallpost","created_at": "2024-06-13 08:44:05","files": [{"source": "existing","id": 9331,"name": "download-1-1.png","alt": "","url": "https:\/\/example.com\/wp-content\/uploads\/2024\/06\/download-1-1.png","preview": "https:\/\/example.com\/wp-content\/uploads\/2024\/06\/download-1-1.png","type": "image\/png"}]}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		} elseif ( 'profile_reviewed' === $term ) {
			if ( ! empty( $results ) ) {
				// Get the review details.
				$args           = [
					'post_id'  => $results[0]['post_id'],
					'order_by' => 'created_at',
					'order'    => 'desc',
				];
				$statuses       = \Voxel\Timeline\Status::query( $args );
				$review_details = \Voxel\Timeline\prepare_status_json( $statuses[0] );
				foreach ( (array) $review_details as $key => $value ) {
					if ( 'user_can_edit' == $key || 'publisher' == $key || 'user_can_edit' == $key || 'user_can_moderate' == $key ) {
						continue;
					}
					if ( 'files' === $key ) {
						$value = wp_json_encode( $value );
					} elseif ( 'reviews' === $key ) {
						$review_ratings   = isset( $value['ratings'] ) && is_array( $value['ratings'] ) ? $value['ratings'] : [];
						$value['ratings'] = [];
						$type             = \Voxel\Post_Type::get( 'profile' );
					
						if ( ! empty( $review_ratings ) ) {
							$rating_levels = $type->reviews->get_rating_levels();
							$categories    = $type->reviews->get_categories();
					
							foreach ( $categories as $category ) {
								$category_key   = $category['key'];
								$category_label = strtolower( $category['label'] );
					
								if ( isset( $review_ratings[ $category_key ] ) && $category_label ) {
									foreach ( $rating_levels as $rating_level ) {
										if ( $review_ratings[ $category_key ] === $rating_level['score'] ) {
											$value['ratings'][ $category_label ] = $rating_level['label'];
											break;
										}
									}
								}
							}
						}
					} else {
						$key = 'review_' . $key;
					}
					$context['pluggable_data'][ $key ] = $value;
				}
				if ( isset( $context['pluggable_data']['review_user'] ) ) {
					unset( $context['pluggable_data']['review_user']['avatar'] );
				}
				$context['response_type'] = 'live';
			} else {
				$context = json_decode( '{"pluggable_data":{"review_id":32,"review_key":"152b0720","review_link":"https://example.com/newsfeed/?status_id=32","review_time":"3 hours ago","review_edit_time":null,"review_content":"new review","review_raw_content":"new review","files":null,"review_is_review":true,"review_user":{"exists":true,"name":"johnd","link":"https://example.com/members/johnd/","id":217},"review_post":{"exists":true,"title":"admin","link":"https://example.com/members/admin/","is_profile":false,"post_type":"profile"},"review_liked_by_user":false,"review_like_count":null,"review_reply_count":null,"review_replies":{"requested":false,"visible":false,"page":1,"loading":false,"hasMore":false,"list":[]},"reviews":{"score":0,"score_formatted":"3.0","mode":"numeric","ratings":{"rating":"Good"}}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}


	/**
	 * Get Late Point Customer Fields
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_late_point_customer_fields( $data ) {
		if ( ! class_exists( 'LatePointAddonCustomFields' ) ) {
			
			return;
		}
		global $wpdb;
		$booking_fields = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}latepoint_settings WHERE name = %s", 'custom_fields_for_customer' ) );
		$fields         = [];
		if ( ! empty( $booking_fields ) ) {
			$fields = json_decode( $booking_fields->value, true );
		}
		return (array) $fields;
	}

	/**
	 * Is multi person booking
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_late_point_multi_person_booking( $data ) {
		if ( ! class_exists( 'LatePointAddonGroupBookings' ) ) {
			
			return;
		}
		$service_id = isset( $data['dynamic'] ) ? $data['dynamic'] : '-1';
		
		if ( -1 == $service_id ) {
			return;
		}
		global $wpdb;
		$booking_details = $wpdb->get_row( $wpdb->prepare( "SELECT capacity_max,capacity_min FROM {$wpdb->prefix}latepoint_services WHERE id= %s", intval( $service_id ) ) );
		if ( ! empty( $booking_details ) ) {  
			return (array) $booking_details;
		}
		
	}

	/**
	 * Get Mail Mint lists
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mail_mint_contact_lists( $data ) {
		global $wpdb;   
	
		$lists   = $wpdb->get_results( $wpdb->prepare( 'SELECT title, id  FROM ' . $wpdb->prefix . 'mint_contact_groups WHERE type = %s', 'lists' ) );
		$options = [];
		
		if ( ! empty( $lists ) ) {
			foreach ( $lists as $list ) {
				$options[] = [
					'label' => $list->title,
					'value' => $list->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}
	
	/**
	 * Get Mail Mint tags
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mail_mint_contact_tags( $data ) {
		global $wpdb;   
	
		$lists   = $wpdb->get_results( $wpdb->prepare( 'SELECT title, id  FROM ' . $wpdb->prefix . 'mint_contact_groups WHERE type = %s ', 'tags' ) );
		$options = [];
		
		if ( ! empty( $lists ) ) {
			foreach ( $lists as $list ) {
				$options[] = [
					'label' => $list->title,
					'value' => $list->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/** Get Better Messages trigger Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_better_messages_triggers_data( $data ) {
		$context = [];
		global $wpdb;
		if ( ! function_exists( 'Better_Messages' ) ) {
			return [];
		}
		$sql     = "SELECT * FROM {$wpdb->prefix}bm_message_messages ORDER BY id DESC LIMIT 1";
		$results      = $wpdb->get_results( $sql, ARRAY_A );// @phpcs:ignore
		
		if ( ! empty( $results ) ) {
			$message = Better_Messages()->functions->get_message( $results[0]['id'] );
			if ( is_object( $message ) ) {
				$message = get_object_vars( $message );
			}
			$context['pluggable_data']           = $message;
			$context['pluggable_data']['sender'] = WordPress::get_user_context( $results[0]['sender_id'] );
			$context['response_type']            = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"id": "21","thread_id": "11","sender_id": "79","message": "New message","date_sent": "2024-04-09 07:08:35","sender": {"wp_user_id": 79,"user_login": "johnd@gmail.com","display_name": "johnd@gmail.com","user_firstname": "John","user_lastname": "D","user_email": "johnd@gmail.com","user_role": ["customer"]}},"response_type":"sample"}', true );// @phpcs:ignore
		}
		return $context;
	}
  
	/**
	 * Get Appointment Hour Booking trigger Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_ahb_appointment_booked_triggers_last_data( $data ) {
		$context = [];
		global $wpdb;
		$sql     = "SELECT * FROM {$wpdb->prefix}cpappbk_messages ORDER BY id DESC LIMIT 1";
		$results      = $wpdb->get_results( $sql, ARRAY_A );// @phpcs:ignore
		
		if ( ! empty( $results ) ) {
			$context['pluggable_data'] = unserialize( $results[0]['posted_data'] );
			$context['response_type']  = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"final_price": "1.00","final_price_short": "1","request_timestamp": "04\/10\/2024 06:56:33","apps": [{"id": 1,"cancelled": "Pending","serviceindex": 0,"service": "Service 1","duration": 60,"price": 1,"date": "2024-04-13","slot": "10:00\/11:00","military": 0,"field": "fieldname1","quant": 1,"sid": ""}],"app_service_1": "Service 1","app_status_1": "Pending","app_duration_1": 60,"app_price_1": 1,"app_date_1": "04\/13\/2024","app_slot_1": "10:00\/11:00","app_starttime_1": "10:00 AM","app_endtime_1": "11:00 AM","app_quantity_1": 1,"formid": 1,"formname": "Form 1","referrer": "https:\/\/example.com\/wp-admin\/admin.php?page=cp_apphourbooking&amp;addbk=1&amp;cal=1&amp;r=0.7819147291131667","fieldname1": " - 04\/13\/2024 10:00 AM - 11:00 AM (Service 1)\n","email": "johnd@yopmail.com","username": "admin","itemnumber": 1},"response_type":"sample"}', true );// @phpcs:ignore
		}
		return $context;
	}

	/**
	 * Prepare mailmint contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_mail_mint_fetch_custom_fields( $data ) {

		$options = [
			[
				'label' => 'Yes',
				'value' => 'true',
			],
			[
				'label' => 'No',
				'value' => 'false',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Mail Mint custom fields
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mail_mint_custom_fields( $data ) {
		global $wpdb;   
		$fields = $wpdb->get_results( 'SELECT *  FROM ' . $wpdb->prefix . 'mint_custom_fields' );
	
		return (array) $fields;
		
	}

	/**
	 * Get Mail Mint lists
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_mail_mint_contacts( $data ) {
		if ( ! class_exists( 'Mint\MRM\DataBase\Models\ContactModel' ) ) {
			return [];
		}
		$contacts = ContactModel::get_all();
		$options  = [];
		if ( ! empty( $contacts ) ) {
			foreach ( $contacts['data'] as $contact ) {
				$options[] = [
					'label' => $contact['email'],
					'value' => $contact['id'],
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get mail mint Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mail_mint_last_data( $data ) {
		if ( ! class_exists( 'Mint\MRM\DataBase\Models\ContactModel' ) || ! class_exists( 'Mint\MRM\DataBase\Models\ContactGroupModel' ) ) {
			return [];
		}
		$context = [];
		global $wpdb;
		$contact_id = isset( $data['filter']['contact_id']['value'] ) ? $data['filter']['contact_id']['value'] : -1;
		$term       = $data['search_term'] ? $data['search_term'] : '';
		$type       = 'lists';
		if ( 'mail_mint_tags_added_to_contact' === $term ) {
			$type = 'tags';
		}
		$data = [];
		if ( -1 == $contact_id ) {
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT relation.id as rid, relation.contact_id, cgroups.* FROM {$wpdb->prefix}mint_contact_group_relationship AS relation JOIN {$wpdb->prefix}mint_contact_groups AS cgroups ON cgroups.id = relation.group_id WHERE cgroups.type = %s ORDER BY rid DESC Limit 1", $type ) );       
		} else {
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT relation.id as rid, relation.contact_id, cgroups.* FROM {$wpdb->prefix}mint_contact_group_relationship AS relation JOIN {$wpdb->prefix}mint_contact_groups AS cgroups ON cgroups.id = relation.group_id WHERE cgroups.type = %s AND relation.contact_id =%d ORDER BY rid DESC Limit 1", $type, $contact_id ) );
		}
		
		if ( ! empty( $result ) ) {
			$contact_id      = $result->contact_id;
			$data['contact'] = ContactModel::get( $contact_id );
			$data[ $type ]   = ContactGroupModel::get( $result->id );
		}
		if ( ! empty( $data ) ) {
			$context['pluggable_data'] = $data;
			$context['response_type']  = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"contact":{"id":"10","wp_user_id":"0","hash":"96eb6293345a2b54246a3214d2419948","email":"john2@test.com","first_name":"John","last_name":"Doe","scores":"0","source":"WebHook","status":"subscribed","stage":"","last_activity":null,"created_by":"0","created_at":"2024-04-09 10:28:56","updated_at":null,"meta_fields":{"dropdown":"New","radio":"One","checkbox":["One"],"status_changed":"subscribed"}},"' . $type . '":{"id":"3","title":"new","type":"tags","data":null,"created_at":"2024-04-09 11:23:11","updated_at":null}},"response_type":"sample"}', true );
		}
		return (array) $context;
	}
	
	
	/**
	 * Get Simple Schedule Appointment Types
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_simple_schedule_appointment_types( $data ) {
		global $wpdb;   
	
		$appointment_types = $wpdb->get_results( 'SELECT title, id  FROM ' . $wpdb->prefix . 'ssa_appointment_types' );
		$options           = [];
		
		if ( ! empty( $appointment_types ) ) {
			foreach ( $appointment_types as $appointment_type ) {
				$options[] = [
					'label' => $appointment_type->title,
					'value' => $appointment_type->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get mail mint Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ssa_last_data( $data ) {
		$context = [];
		global $wpdb;
		$appointment_type_id = isset( $data['filter']['appointment_type_id']['value'] ) ? $data['filter']['appointment_type_id']['value'] : -1;
		$term                = $data['search_term'] ? $data['search_term'] : '';
		$status              = 'booked';
		if ( 'ssa_appointment_cancelled' === $term ) {
			$status = 'canceled';
		}
		$data = [];
		if ( -1 == $appointment_type_id ) {
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ssa_appointments WHERE status = %s ORDER BY id DESC Limit 1", $status ), ARRAY_A );       
		} else {
			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ssa_appointments where appointment_type_id=%d AND status = %s ORDER BY id DESC Limit 1", $appointment_type_id, $status ), ARRAY_A );     
		}
		
		
		if ( ! empty( $result ) ) {
			$result_meta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ssa_appointment_meta where appointment_id=%d", $result['id'] ) );
			if ( ! empty( $result_meta ) ) {
				foreach ( $result_meta as $meta ) {
					$result[ $meta->meta_key ] = $meta->meta_value;
				}
			}
		}
		if ( ! empty( $result ) ) {
			
			$result['customer_information'] = json_decode( $result['customer_information'], true );
			$context['pluggable_data']      = $result;
			$context['response_type']       = 'live';
		} else {
			$context = json_decode( '{"pluggable_data":{"id":"4","appointment_type_id":"1","rescheduled_from_appointment_id":"0","rescheduled_to_appointment_id":"0","group_id":"0","author_id":"1","customer_id":"0","customer_information":{"Name":"John Doe","Email":"johndoe@email.com"},"customer_timezone":"Asia\/Calcutta","customer_locale":"en_US","start_date":"2024-04-22 03:30:00","end_date":"2024-04-22 04:00:00","title":"","description":"","payment_method":"","payment_received":"0.00","mailchimp_list_id":"","google_calendar_id":"","google_calendar_event_id":"","web_meeting_password":"","web_meeting_id":"","web_meeting_url":"","allow_sms":"","status":"booked","date_created":"2024-04-18 09:19:14","date_modified":"2024-04-18 09:19:14","expiration_date":"0000-00-00 00:00:00"},"response_type":"sample"}', true );
		}
		return (array) $context;
	}

	/**
	 * Get Simple Schedule Appointment Types
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_simple_schedule_appointments( $data ) {
		global $wpdb;   
	
		$appointment_types = $wpdb->get_results( 'SELECT title, id  FROM ' . $wpdb->prefix . 'ssa_appointment_types' );
		$options           = [];
		
		if ( ! empty( $appointment_types ) ) {
			foreach ( $appointment_types as $appointment_type ) {
				$options[] = [
					'label' => $appointment_type->title,
					'value' => $appointment_type->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Sensei LMS Lessons
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_sensei_lms_lessons( $data ) {
		if ( ! function_exists( 'Sensei' ) ) {
			return [];
		}
		$course_id = $data['dynamic'];
		$lessons   = \Sensei()->course->course_lessons( $course_id, 'publish', 'ids' );
		$options   = [];
		
		if ( ! empty( $lessons ) ) {
			foreach ( $lessons as $lesson ) {
				$options[] = [
					'label' => get_the_title( $lesson ),
					'value' => $lesson,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Sensei LMS Quiz List
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_sensei_lms_quiz( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$quizes       = get_posts(
			[
				'post_type'      => 'lesson',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => '_lesson_quiz',
						'compare' => 'EXISTS',
					],
					[
						'key'     => '_quiz_has_questions',
						'value'   => 1,
						'compare' => '=',
					],
				],
			]
		);
		$options      = [];
		$total_quizes = get_posts(
			[
				'post_type'      => 'lesson',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'     => '_lesson_quiz',
						'compare' => 'EXISTS',
					],
					[
						'key'     => '_quiz_has_questions',
						'value'   => 1,
						'compare' => '=',
					],
				],
			]
		);
		$count        = count( $total_quizes );
		if ( ! empty( $quizes ) ) {
			foreach ( $quizes as $quiz ) {
				$options[] = [
					'label' => get_the_title( $quiz ),
					'value' => $quiz,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get Sensei LMS trigger Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array|mixed|string
	 */
	public function search_sensei_lms_triggers_last_data( $data ) {
		$context = [];
		global $wpdb;
		$term = $data['search_term'];

		if ( ! function_exists( 'Sensei' ) ) {
			return;
		}

		if ( 'course_completed' == $term ) {
			$comment_type = 'sensei_course_status';
			$course_id    = $data['filter']['sensei_course']['value'];
			if ( -1 === $course_id ) {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = %s AND comment_type = %s ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'complete', $comment_type ), ARRAY_A );// @phpcs:ignore
			} else {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = %s AND comment_type = %s AND comment_post_ID = %d ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'complete', $comment_type, $course_id ), ARRAY_A );// @phpcs:ignore
			}
		} elseif ( 'course_enrolled' == $term ) {
			$comment_type = 'sensei_course_status';
			$course_id    = $data['filter']['sensei_course']['value'];
			if ( -1 === $course_id ) {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = %s AND comment_type = %s ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'in-progress', $comment_type ), ARRAY_A );// @phpcs:ignore
			} else {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = %s AND comment_type = %s AND comment_post_ID = %d ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'in-progress', $comment_type, $course_id ), ARRAY_A );// @phpcs:ignore
			}
		} elseif ( 'lesson_completed' == $term ) {
			$lesson_id    = $data['filter']['sensei_lesson']['value'];
			$course_id    = $data['filter']['sensei_course']['value'];
			$comment_type = 'sensei_lesson_status';
			if ( -1 === $lesson_id ) {
				$sql     = "SELECT comments.*, meta.* FROM {$wpdb->prefix}comments as comments
				LEFT JOIN {$wpdb->prefix}postmeta as meta
				ON comments.comment_post_ID = meta.post_id
				WHERE comments.comment_approved = %s 
				AND comments.comment_type = %s 
				AND meta.meta_key = %s AND
				meta.meta_value = %d
				ORDER BY comments.comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'complete', $comment_type, '_lesson_course', $course_id ), ARRAY_A );// @phpcs:ignore
			} else {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_approved = %s AND comment_type = %s AND comment_post_ID = %d ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, 'complete', $comment_type, $lesson_id ), ARRAY_A );// @phpcs:ignore
			}
		} elseif ( 'quiz_attempted' == $term || 'quiz_passes' == $term || 'quiz_fails' == $term ) {
			$quiz_id      = $data['filter']['sensei_quiz']['value'];
			$comment_type = 'sensei_lesson_status';
			if ( 'quiz_passes' == $term ) {
				$status = "( comment_approved = 'passed' )";
			} elseif ( 'quiz_fails' == $term ) {
				$status = "( comment_approved = 'failed' )";
			} else {
				$status = "( comment_approved = 'failed' OR comment_approved = 'passed' )";
			}
			if ( -1 === $quiz_id ) {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_type = %s AND $status ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, $comment_type ), ARRAY_A );// @phpcs:ignore
			} else {
				$sql     = "SELECT * FROM {$wpdb->prefix}comments WHERE comment_type = %s AND $status AND comment_post_ID = %d ORDER BY comment_ID DESC LIMIT 1";
				$results      = $wpdb->get_results( $wpdb->prepare( $sql, $comment_type, $quiz_id ), ARRAY_A );// @phpcs:ignore
			}
		} elseif ( 'quiz_completion' == $term ) {
			$quiz_id           = $data['filter']['sensei_quiz']['value'];
			$comment_type      = 'sensei_lesson_status';
			$condition_compare = $data['filter']['condition_compare']['value'];
			$percentage        = $data['filter']['percentage']['value'];
			if ( -1 === $quiz_id ) {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT comments.*, meta.meta_key, meta.meta_value FROM wp_comments AS comments JOIN  wp_commentmeta AS meta ON comments.comment_ID = meta.comment_id WHERE meta.meta_key LIKE %s AND meta.meta_value $condition_compare %d ORDER BY comments.comment_ID DESC LIMIT 1", 'grade', $percentage ), ARRAY_A ); //phpcs:ignore
			} else {
				$results = $wpdb->get_results( $wpdb->prepare( "SELECT comments.*, meta.meta_key, meta.meta_value FROM wp_comments AS comments JOIN  wp_commentmeta AS meta ON comments.comment_ID = meta.comment_id WHERE comments.comment_post_ID = %d AND meta.meta_key LIKE %s AND meta.meta_value $condition_compare %d ORDER BY comments.comment_ID DESC LIMIT 1", $quiz_id, 'grade', $percentage ), ARRAY_A ); //phpcs:ignore
			}
		}   

		if ( ! empty( $results ) ) {
			$post                      = get_post( $results[0]['comment_post_ID'] );
			$context['pluggable_data'] = WordPress::get_user_context( $results[0]['user_id'] );
			if ( 'course_completed' == $term || 'course_enrolled' == $term ) {
				$context['pluggable_data']['sensei_course'] = $results[0]['comment_post_ID'];
				if ( $post instanceof \WP_Post ) {
					$context['pluggable_data']['course_title'] = $post->post_title;
				}
			} elseif ( 'lesson_completed' == $term ) {
				$context['pluggable_data']['sensei_lesson'] = $results[0]['comment_post_ID'];
				if ( $post instanceof \WP_Post ) {
					$context['pluggable_data']['lesson_title'] = $post->post_title;
				}
			} elseif ( 'quiz_attempted' == $term || 'quiz_passes' == $term || 'quiz_fails' == $term || 'quiz_completion' == $term ) {
				$lesson_quiz = get_post_meta( $results[0]['comment_post_ID'], '_lesson_quiz', true );
				$submission  = \Sensei()->quiz_submission_repository->get( $lesson_quiz, $results[0]['user_id'] );
				if ( $post instanceof \WP_Post ) {
					$context['pluggable_data']['quiz_title'] = $post->post_title;
				}
				$context['pluggable_data']['quiz_status']              = $results[0]['comment_approved'];
				$context['pluggable_data']['quiz_data']['id']          = $submission->get_id();
				$context['sensei_quiz']                                = $submission->get_id();
				$context['pluggable_data']['quiz_data']['final_grade'] = $submission->get_final_grade();
				$context['pluggable_data']['quiz_data']['created_at']  = $submission->get_created_at();
			}
			$context['response_type'] = 'live';
		} else {
			if ( 'course_completed' == $term || 'course_enrolled' == $term ) {
				$context = json_decode( '{"pluggable_data":{"wp_user_id": 48,"user_login": "john@yopmail.com","display_name": "john@yopmail.com","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["subscriber"],"sensei_course": "7500","course_title": "Course 1"},"response_type":"sample"}', true );// @phpcs:ignore
			} elseif ( 'lesson_completed' == $term ) {
				$context = json_decode( '{"pluggable_data":{"wp_user_id": 48,"user_login": "john@yopmail.com","display_name": "john@yopmail.com","user_firstname": "john","user_lastname": "d","user_email": "johnd@yopmail.com","user_role": ["subscriber"],"sensei_lesson": "7502","lesson_title": "Lesson 1"},"response_type":"sample"}', true );// @phpcs:ignore
			} elseif ( 'quiz_attempted' == $term || 'quiz_passes' == $term || 'quiz_fails' == $term || 'quiz_completion' == $term ) {
				$context = json_decode( '{"pluggable_data":{"wp_user_id":2,"user_login":"johnd","display_name":"john d","user_firstname":"john","user_lastname":"d","user_email":"johnd@email.com","user_role":[],"quiz_title":"Lesson 2","quiz_status":"passed","quiz_data":{"id":27,"final_grade":100,"created_at":{"date":"2024-05-07 06:54:31.000000","timezone_type":1,"timezone":"+00:00"}}},"response_type":"sample"}', true );// @phpcs:ignore
			}
		}
		return $context;
	}
	
	/**
	 * Get SurelyWP Services - SureCart Addons Last Data
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_services_sc_triggers_last_data( $data ) {
		$context = [];
		global $wpdb;
		$term = $data['search_term'] ? $data['search_term'] : '';
		$data = [];
		
		if ( 'new_service_created' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_status LIKE 'service_created' ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'requirement_submitted' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_requirements ORDER BY requirement_id DESC Limit 1", ARRAY_A );
		} elseif ( 'message_sent' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_messages ORDER BY message_id DESC Limit 1", ARRAY_A );
		} elseif ( 'message_final_delivery_sent' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_messages WHERE is_final_delivery = 1 ORDER BY message_id DESC Limit 1", ARRAY_A );
		} elseif ( 'customer_request_revision' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_status LIKE 'service_start' ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'customer_approves_final_delivery' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_status LIKE 'service_complete' ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'delivery_date_changed' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE delivery_date IS NOT NULL ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'service_cancel' === $term || 'service_marked_canceled' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_status LIKE 'service_canceled' ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'service_completed' === $term || 'service_mark_completed' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_services WHERE service_status LIKE 'service_complete' ORDER BY service_id DESC Limit 1", ARRAY_A );
		} elseif ( 'contract_signed' === $term ) {
			$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}surelywp_sv_contracts ORDER BY contract_id DESC Limit 1", ARRAY_A );
		}
		if ( ! empty( $result ) ) {
			if ( 'new_service_created' === $term ) {
				$service_data              = [
					'service_setting_id' => $result['service_id'],
					'order_id'           => $result['order_id'],
					'product_id'         => $result['product_id'],
					'service_status'     => $result['service_status'],
					'delivery_date'      => $result['delivery_date'],
					'user_id'            => $result['user_id'],
				];
				$context['pluggable_data'] = $service_data;
			} elseif ( 'requirement_submitted' === $term ) {
				$requirement_data          = [
					'req_title' => $result['requirement_title'],
					'req_desc'  => $result['requirement_desc'],
				];
				$context['pluggable_data'] = $requirement_data;
			} elseif ( 'message_sent' === $term || 'message_final_delivery_sent' === $term ) {
				$message_data              = [
					'sender_id'            => $result['sender_id'],
					'receiver_id'          => $result['receiver_id'],
					'service_id'           => $result['service_id'],
					'message_text'         => $result['message_text'],
					'attachment_file_name' => $result['attachment_file_name'],
					'is_final_delivery'    => $result['is_final_delivery'],
				];
				$context['pluggable_data'] = $message_data;
			} elseif ( 'customer_request_revision' === $term || 'customer_approves_final_delivery' === $term || 'delivery_date_changed' === $term || 'service_cancel' === $term || 'service_marked_canceled' === $term || 'service_completed' === $term || 'service_mark_completed' === $term ) {
				$context['pluggable_data'] = $result;
			} elseif ( 'contract_signed' === $term ) {
				$contract_data             = [
					'service_id'       => $result['service_id'],
					'signature'        => $result['signature'],
					'contract_details' => $result['contract_details'],
				];
				$context['pluggable_data'] = $contract_data;
			}
			$context['response_type'] = 'live';
		} else {
			if ( 'new_service_created' === $term || 'customer_request_revision' === $term || 'customer_approves_final_delivery' === $term || 'delivery_date_changed' === $term ) {
				$context = json_decode( '{"pluggable_data":{"delivery_date": null,"order_id": "a3830048-9a43-4088-a78e-285537f16ecc","product_id": "f59f62cc-fd70-4007-8bcf-56d07f1ac871","service_setting_id": "2","service_status": "service_created","user_id": "84"},"response_type":"sample"}', true );
			} elseif ( 'requirement_submitted' === $term ) {
				$context = json_decode( '{"pluggable_data":{req_title: "Requirement Title",req_desc: "Requirement Description"},"response_type":"sample"}', true );
			} elseif ( 'message_sent' === $term || 'message_final_delivery_sent' === $term ) {
				$context = json_decode( '{"pluggable_data":{sender_id: "1",receiver_id: "2",service_id: "1",message_text: "Message Text",attachment_file_name: "Attachment File Name",is_final_delivery: "1"},"response_type":"sample"}', true );
			} elseif ( 'service_cancel' === $term ) {
				$context = json_decode( '{"pluggable_data":{"delivery_date": null,"order_id": "a3830048-9a43-4088-a78e-285537f16ecc","product_id": "f59f62cc-fd70-4007-8bcf-56d07f1ac871","service_setting_id": "2","service_status": "service_canceled","user_id": "84"},"response_type":"sample"}', true );
			} elseif ( 'service_marked_canceled' === $term || 'service_completed' === $term || 'service_mark_completed' === $term ) {
				$context = json_decode( '{"pluggable_data":{"delivery_date": null,"order_id": "a3830048-9a43-4088-a78e-285537f16ecc","product_id": "f59f62cc-fd70-4007-8bcf-56d07f1ac871","service_setting_id": "2","service_status": "service_completed","user_id": "84"},"response_type":"sample"}', true );
			} elseif ( 'contract_signed' === $term ) {
				$context = json_decode( '{"pluggable_data":{service_id: "1",signature: "Signature",contract_details: "Contract Details"},"response_type":"sample"}', true );
			}
		}
		return (array) $context;
	}
}

GlobalSearchController::get_instance();
