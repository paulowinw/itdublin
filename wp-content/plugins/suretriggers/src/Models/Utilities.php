<?php
/**
 * Automation Modal Class.
 * php version 5.6
 *
 * @category Model
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Models;

use WP_User_Query;
use WPForms_Form_Handler;

/**
 * Responsible for interacting with the database.
 *
 * Class Model
 *
 * @package SureTriggers\Models
 *
 * @psalm-suppress UndefinedClass Model
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Utilities extends Model {


	/**
	 * Find posts by title.
	 *
	 * @param array $data Search Data.
	 * @return array
	 */
	public static function find_posts_by_title( $data ) {
		/**
		 * Get object from base model
		 *
		 * @var Utilities $model Utilities Model.
		 */
		$model = self::init();

		$model->table = $model->db->posts;
		$where        = '1=1';

		global $wpdb;

		/**
		 * Get terms as string
		 *
		 * @var string $terms Terms string
		 */
		$terms     = esc_sql( $data['search_term'] );
		$dynamic   = esc_sql( $data['dynamic'] );
		$post_type = esc_sql( isset( $data['filter']['post_type'] ) ? sanitize_text_field( $data['filter']['post_type'] ) : 'post' );
		if ( 'post' === $post_type && ! empty( $dynamic['post_type'] ) ) {
			$post_type = esc_sql( $dynamic['post_type'] );
		}

		$page   = isset( $data['page'] ) ? absint( sanitize_text_field( $data['page'] ) ) : 1;
		$limit  = self::get_search_page_limit( $post_type );
		$offset = $limit * ( $page - 1 );

		if ( empty( $post_type ) || 'post_all' === $post_type ) {
			$where .= " AND p.post_type not in ( 'revision', 'attachment' )";
		} else {
			$where .= " AND p.post_type='{$post_type}' AND p.post_status NOT IN ( 'draft', 'pending' ,'trash','auto-draft')";
		}

		$where .= ' AND p.post_title LIKE %s';

		$sql_query = "SELECT p.ID, p.post_parent, p.post_type, p.post_title FROM {$model->table} p";

		if ( in_array( $post_type, [ 'sfwd-lessons', 'sfwd-topic' ], true ) && ! empty( $dynamic ) && $dynamic > 0 ) {
			$meta_key = 'sfwd-lessons' === $post_type ? 'course_id' : 'lesson_id';

			$sql_query .= " LEFT JOIN $wpdb->postmeta pm ON pm.post_id = p.ID";
			$where     .= " AND pm.meta_key = '{$meta_key}' AND pm.meta_value = {$dynamic}";
		}

		$sql_query .= " WHERE {$where}";

		$result = $model->db->get_results(
			$model->db->prepare( $sql_query . " LIMIT {$offset}, {$limit}", "%%{$terms}%%" ),
			ARRAY_A
		);

		$count_result = $model->db->get_results(
			$model->db->prepare( $sql_query, "%%{$terms}%%" )
		);
		$count        = count( $count_result );

		return [
			'results'  => $result,
			'has_more' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get search page limit.
	 *
	 * @param string $type search type.
	 * @since 1.0.0
	 */
	public static function get_search_page_limit( $type = 'options' ) {
		return apply_filters( 'ap_search_' . $type . '_limit', 10 );
	}

	/**
	 * Get Divi forms.
	 *
	 * @return array|object|void|null
	 * @psalm-suppress MixedAssignment
	 */
	public static function get_divi_forms() {
		/**
		 * Get object from base model
		 *
		 * @var Utilities $model Utilities Model.
		 */
		$model = self::init();

		$model->table = $model->db->posts;

		$sql_query = "SELECT `ID`, `post_content`, `post_title` FROM {$model->table} WHERE post_status = 'publish' AND post_type = 'page' AND post_content LIKE '%%et_pb_contact_form%%'";

		return $model->db->get_results(
			$model->db->prepare( $sql_query ),
			ARRAY_A
		);
	}

	/**
	 * Get UAG forms.
	 *
	 * @return array|object|void|null
	 * @psalm-suppress MixedAssignment
	 */
	public static function get_uag_forms() {
		/**
		 * Get object from base model
		 *
		 * @var Utilities $model Utilities Model.
		 */
		$model = self::init();

		$model->table = $model->db->posts;

		$sql_query = "SELECT `ID`, `post_content`, `post_title` FROM {$model->table} WHERE post_status = 'publish' AND post_content LIKE '%%wp-block-uagb-forms%%'";

		return $model->db->get_results(
			$model->db->prepare( $sql_query ),
			ARRAY_A
		);
	}

	/**
	 * Get users data from DB.
	 *
	 * @param array $data Data Array.
	 * @param int   $page Page Number.
	 * @return array
	 */
	public static function get_users( $data, $page ) {
		/**
		 * Get terms as string
		 *
		 * @var string $terms Terms String.
		 */
		$terms  = esc_sql( isset( $data['search_term'] ) ? $data['search_term'] : '' );
		$limit  = self::get_search_page_limit( 'users' );
		$offset = $limit * ( $page - 1 );

		$prepare_params = [
			'fields'         => [ 'ID', 'user_login' ],
			'offset'         => $offset,
			'search_columns' => [ 'user_login', 'user_email' ],
			'number'         => $limit,
			'search'         => ! empty( $terms ) ? '*' . $terms . '*' : '',
		];

		if ( is_array( $data['filter'] ) ) {
			foreach ( $data['filter'] as $filter_name => $filter ) {
				$prepare_params [ $filter_name ] = $filter;
			}
		}

		$user_search = new WP_User_Query( $prepare_params );

		$users = $user_search->get_results();
		$count = $user_search->get_total();

		return [
			'results'  => $users,
			'has_more' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get WPForms data.
	 *
	 * @param string $terms search string.
	 * @param int    $page Page Number.
	 * @param int    $form_id WPForm ID.
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_wpform_fields( $terms, $page, $form_id ) {
		/**
		 * Get terms as string
		 *
		 * @var string $terms Terms string
		 */
		$terms   = esc_sql( $terms );
		$limit   = self::get_search_page_limit();
		$offset  = $limit * ( $page - 1 );
		$results = [];

		if ( ! class_exists( 'WPForms_Form_Handler' ) ) {
			return;
		}
		$wpforms = new WPForms_Form_Handler();
		$form    = $wpforms->get( $form_id );
		/**
		 * Ignore false positive
		 *
		 * @psalm-suppress UndefinedFunction
		 */
		$form_content = wpforms_decode( $form->post_content );

		$wpform_fields = $form_content['fields'];
		$count         = count( $form_content['fields'] );

		/**
		 * Ignore false positive
		 *
		 * @psalm-suppress TooManyArguments
		 */
		$exclude_fields = apply_filters(
			'sure_trigger_wpforms_exclude_fields',
			[
				'pagebreak',
				'file-upload',
				'password',
				'divider',
				'entry-preview',
				'html',
				'stripe-credit-card',
				'authorize_net',
				'square',
			],
			$form_id
		);

		foreach ( $wpform_fields as $field ) {
			if ( in_array( (string) $field['type'], $exclude_fields, true ) ) {
				continue;
			}
			if ( empty( $terms ) ) {
				$results[] = $field;
			} elseif ( false !== stripos( $field['label'], $terms ) ) {
				$results[] = $field;
			}
		}

		return [
			'results'  => $results,
			'has_more' => $count > $limit && $count > $offset,
		];

	}

	/**
	 * Get Fluent Forms data.
	 *
	 * @param string $terms search string.
	 * @param int    $page Page Number.
	 * @param int    $form_id WPForm ID.
	 * @return array
	 * @since 1.0.0
	 */
	public static function get_fluentform_fields( $terms, $page, $form_id ) {
		/**
		 * Get terms as string
		 *
		 * @var string $terms Terms string
		 */
		$terms   = esc_sql( $terms );
		$limit   = self::get_search_page_limit();
		$offset  = $limit * ( $page - 1 );
		$results = [];

		if ( ! function_exists( 'wpFluent' ) ) {
			[
				'results'  => [],
				'has_more' => false,
			];
		}

		$form       = wpFluent()->table( 'fluentform_forms' )->find( $form_id );
		$field_data = json_decode( $form->form_fields, true );
		$count      = count( $field_data['fields'] );

		foreach ( $field_data['fields'] as $field ) {
			// check if the field has multiple inputs ...
			if ( isset( $field['fields'] ) ) {
				foreach ( $field['fields'] as $field_key => $sub_field ) {
					if (
						isset( $sub_field['settings'] )
						&& isset( $sub_field['settings']['label'] )
						&& isset( $sub_field['settings']['visible'] )
						&& true === $sub_field['settings']['visible']
					) {
						$results[] = [
							'value' => $field_key,
							'text'  => esc_html( $sub_field['settings']['label'] ),
						];
					}
				}
			} elseif ( isset( $field['element'] ) && 'container' === (string) $field['element'] && isset( $field['columns'] ) && is_array( $field['columns'] ) ) {
				$container_fields = $field['columns'];
				foreach ( $container_fields as $c_fields ) {
					foreach ( $c_fields['fields'] as $field_key => $sub_field ) {
						if ( isset( $sub_field['settings'] ) && isset( $sub_field['settings']['label'] ) ) {
							$results[] = [
								'value' => isset( $sub_field['attributes']['name'] ) ? $sub_field['attributes']['name'] : strtolower( $sub_field['settings']['label'] ),
								'text'  => esc_html( $sub_field['settings']['label'] ),
							];

						}
					}
				}
			} elseif ( isset( $field['attributes'] ) && isset( $field['attributes']['name'] ) ) {
				if ( isset( $field['attributes']['placeholder'] ) && ! empty( $field['attributes']['placeholder'] ) ) {
					$results[] = [
						'value' => $field['attributes']['name'],
						'text'  => esc_html( $field['attributes']['placeholder'] ),
					];
				} elseif ( isset( $field['settings'] ) && isset( $field['settings']['label'] ) && ! empty( $field['settings']['label'] ) ) {
					$results[] = [
						'value' => $field['attributes']['name'],
						'text'  => esc_html( $field['settings']['label'] ),
					];
				}
			}
		}

		return [
			'results'  => $results,
			'has_more' => $count > $limit && $count > $offset,
		];

	}

	/**
	 * Get terms
	 *
	 * @param string $terms search string.
	 * @param int    $page Page Number.
	 * @param array  $taxonomy Category type.
	 * @since 1.0.0
	 */
	public static function get_terms( $terms, $page, $taxonomy ) {
		$terms  = esc_sql( $terms );
		$limit  = self::get_search_page_limit( 'terms' );
		$offset = $limit * ( $page - 1 );

		$params = [
			'offset'     => $offset,
			'number'     => $limit,
			'search'     => ! empty( $terms ) ? $terms : '',
			'hide_empty' => false,
			'fields'     => 'all',
		];

		if ( ! empty( $taxonomy ) ) {
			$params['taxonomy'] = $taxonomy;
		}
		$result = get_terms( $params );

		$count_params = [
			'search'     => ! empty( $terms ) ? $terms : '',
			'hide_empty' => false,
			'fields'     => 'count',
		];

		if ( ! empty( $taxonomy ) ) {
			$count_params['taxonomy'] = $taxonomy;
		}
		$count = get_terms( $count_params );

		return [
			'result'   => $result,
			'has_more' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Product Subscription Variation.
	 *
	 * @param string $terms Search term.
	 * @param int    $page Page number.
	 * @since 1.0.0
	 */
	public static function get_subscription_variation( $terms, $page ) {

		$terms                 = esc_sql( $terms );
		$limit                 = self::get_search_page_limit( 'subscription' );
		$offset                = $limit * ( $page - 1 );
		$params                = [
			'type'   => [ 'variable-subscription' ],
			'limit'  => $limit,
			'offset' => $offset,
		];
		$subscription_products = wc_get_products( $params );

		$count_params = [
			'type' => [ 'variable-subscription' ],
		];

		/**
		 * Get wc product as countable array
		 *
		 * @var array $countable_products
		 */
		$countable_products = wc_get_products( $count_params );
		$count              = count( $countable_products );

		return [
			'result'   => $subscription_products,
			'has_more' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get variable products.
	 *
	 * @param string $terms Search term.
	 * @param int    $page Page number.
	 * @since 1.0.0
	 */
	public static function get_variable_products( $terms, $page ) {

		$terms  = esc_sql( $terms );
		$limit  = self::get_search_page_limit( 'variable_products' );
		$offset = $limit * ( $page - 1 );
		$params = [
			'status' => 'publish',
			'type'   => [ 'variable' ],
			'limit'  => $limit,
			'offset' => $offset,
			'sku'    => $terms,
		];

		$products = wc_get_products( $params );

		$count_params = [
			'status' => 'publish',
			'type'   => [ 'variable' ],
			'sku'    => $terms,
		];

		/**
		 * Get wc product as countable array
		 *
		 * @var array $countable_products
		 */
		$countable_products = wc_get_products( $count_params );
		$count              = count( $countable_products );

		return [
			'result'   => $products,
			'has_more' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Get selected product variations.
	 *
	 * @param int $parent parent id.
	 * @since 1.0.0
	 */
	public static function get_product_variations( $parent = '' ) {

		$params = [
			'post_status'    => 'publish',
			'post_parent'    => absint( $parent ),
			'post_type'      => 'product_variation',
			// Todo: Implement pagination.
			'posts_per_page' => 999, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
		];

		$products = get_posts( $params );

		return [
			'result'   => $products,
			'has_more' => false,
		];
	}

	/**
	 * Get learndash courses.
	 *
	 * @return array
	 */
	public static function get_product_courses() {

		$courses = get_posts(
			[

				'post_type' => 'product',
				'meta_key'  => '_related_course',
			]
		);

		return [
			'result'   => $courses,
			'has_more' => false,
		];
	}

	/**
	 * Get elementor forms.
	 *
	 * @return array
	 */
	public static function get_elementor_forms() {

		global $wpdb;
		$elementor_forms = [];
		$post_metas      = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value
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
				$inner_forms = self::search_elementor_forms( json_decode( $post_meta->meta_value ) );
				if ( ! empty( $inner_forms ) ) {
					foreach ( $inner_forms as $form ) {
						$elementor_forms[ $form->id ] = $form->settings->form_name;
					}
				}
			}
		}

		return $elementor_forms;
	}

	/**
	 * Search elementor forms.
	 *
	 * @param array $elements Search Forms.
	 * @return array[]
	 */
	public static function search_elementor_forms( $elements ) {
		$block_is_on_page = [];
		if ( ! empty( $elements ) ) {
			foreach ( $elements as $element ) {
				if ( 'widget' === $element->elType && 'form' === $element->widgetType ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
					$block_is_on_page[] = $element;
				}
				if ( ! empty( $element->elements ) ) {
					$inner_block_is_on_page = self::search_elementor_forms( $element->elements );
					if ( ! empty( $inner_block_is_on_page ) ) {
						$block_is_on_page = array_merge( $block_is_on_page, $inner_block_is_on_page );
					}
				}
			}
		}

		return $block_is_on_page;
	}

	/**
	 * Search elementor form fields.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function get_elementor_form_fields( $data ) {

		global $wpdb;
		$form_id               = $data['dynamic'];
		$elementor_form_fields = [];
		$post_metas            = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.meta_value
				FROM $wpdb->postmeta pm
					LEFT JOIN $wpdb->posts p
						ON p.ID = pm.post_id
				WHERE p.post_type IS NOT NULL
				AND p.post_status = %s
				AND pm.meta_key = %s
				AND pm.`meta_value` LIKE %s",
				'publish',
				'_elementor_data',
				'%%' . $form_id . '%%'
			)
		);

		if ( ! empty( $post_metas ) ) {
			foreach ( $post_metas as $post_meta ) {
				$inner_forms = self::search_elementor_forms( json_decode( $post_meta->meta_value ) );
				if ( ! empty( $inner_forms ) ) {
					foreach ( $inner_forms as $form ) {
						foreach ( $form->settings->form_fields as $form_field ) {
							$elementor_form_fields[ $form_field->custom_id ] = $form_field->field_label;
						}
					}
				}
			}
		}

		return $elementor_form_fields;
	}

	/**
	 * Get All orders IDs for a given product ID.
	 *
	 * @param  integer $product_id product id.
	 *
	 * @return array
	 */
	public function get_orders_ids_by_product_id( $product_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wc_orders';

		// Check if the table exists.
		$table_exists = $wpdb->get_var("SELECT COUNT(*) FROM information_schema.tables WHERE table_name = '$table_name' AND (SELECT COUNT(*) FROM $table_name) > 0"); // phpcs:ignore
		
		if ( $table_exists ) {
			
			$count = $wpdb->get_col(
				$wpdb->prepare(
					"
				SELECT order_items.order_id
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
				LEFT JOIN {$wpdb->prefix}wc_orders AS orders ON order_items.order_id = orders.id
				WHERE orders.type = 'shop_order'
				AND orders.status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
				AND order_items.order_item_type = 'line_item'
				AND order_item_meta.meta_key = '_product_id'
				AND order_item_meta.meta_value = %s
				ORDER BY order_items.order_id DESC",
					$product_id
				)
			);
			
		} else {
			$count = $wpdb->get_col(
				$wpdb->prepare(
					"
				SELECT order_items.order_id
				FROM {$wpdb->prefix}woocommerce_order_items as order_items
				LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
				LEFT JOIN {$wpdb->prefix}posts AS posts ON order_items.order_id = posts.ID
				WHERE posts.post_type = 'shop_order'
				AND posts.post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold')
				AND order_items.order_item_type = 'line_item'
				AND order_item_meta.meta_key = '_product_id'
				AND order_item_meta.meta_value = %s
				ORDER BY order_items.order_id DESC",
					$product_id
				)
			);
		}
		return $count;
	}


}
