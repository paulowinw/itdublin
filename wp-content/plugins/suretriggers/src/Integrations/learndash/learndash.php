<?php
/**
 * LearnDash core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\LearnDash;

use SureTriggers\Integrations\Integrations;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\LearnDash
 */
class LearnDash extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'LearnDash';

	/**
	 * Get customer context data.
	 *
	 * @param object $course course.
	 *
	 * @return array
	 */
	public static function get_course_context( $course ) {
		$context['course_name']               = $course->post_title;
		$context['sfwd_course_id']            = $course->ID;
		$context['course_url']                = get_permalink( $course->ID );
		$context['course_featured_image_id']  = get_post_meta( $course->ID, '_thumbnail_id', true );
		$context['course_featured_image_url'] = get_the_post_thumbnail_url( $course->ID );

		return $context;
	}

	/**
	 * Get customer context data.
	 *
	 * @param object $lesson lesson.
	 *
	 * @return array
	 */
	public static function get_lesson_context( $lesson ) {
		$context['lesson_name']               = $lesson->post_title;
		$context['sfwd_lesson_id']            = $lesson->ID;
		$context['lesson_url']                = get_permalink( $lesson->ID );
		$context['lesson_featured_image_id']  = get_post_meta( $lesson->ID, '_thumbnail_id', true );
		$context['lesson_featured_image_url'] = get_the_post_thumbnail_url( $lesson->ID );

		return $context;
	}

	/**
	 * Get customer context data.
	 *
	 * @param object $topic topic.
	 *
	 * @return array
	 */
	public static function get_topic_context( $topic ) {
		$context['topic_name']               = $topic->post_title;
		$context['sfwd_topic_id']            = $topic->ID;
		$context['topic_url']                = get_permalink( $topic->ID );
		$context['topic_featured_image_id']  = get_post_meta( $topic->ID, '_thumbnail_id', true );
		$context['topic_featured_image_url'] = get_the_post_thumbnail_url( $topic->ID );

		return $context;
	}

	/**
	 * Get quiz questions and answers.
	 *
	 * @param int $quiz Quiz ID.
	 *
	 * @return array
	 */
	public static function get_quiz_questions_answers( $quiz ) {

		if ( ! class_exists( '\WpProQuiz_Model_QuestionMapper' ) || ! class_exists( '\WpProQuiz_Controller_Question' ) || ! function_exists( 'learndash_get_quiz_questions' ) || ! function_exists( 'learndash_get_post_type_slug' ) ) {
			return [];
		}

		$questions_ids    = learndash_get_quiz_questions( $quiz );
		$output_questions = [];
		if ( ! empty( $questions_ids ) ) {
			foreach ( $questions_ids as $question_id => $question_pro_id ) {
				$question_id     = absint( $question_id );
				$question_pro_id = absint( $question_pro_id );

				$question_post = get_post( $question_id );
				if ( ( ! $question_post ) || ( ! is_a( $question_post, 'WP_Post' ) ) || ( learndash_get_post_type_slug( 'question' ) !== $question_post->post_type ) ) {
					continue;
				}

				// Get answers from question.
				$question_mapper = new \WpProQuiz_Model_QuestionMapper();

				if ( ! empty( $question_pro_id ) ) {
					$question_model = $question_mapper->fetch( $question_pro_id );
				} else {
					$question_model = $question_mapper->fetch( null );
				}

				if ( ( empty( $question_model->getId() ) ) || ( $question_model->getId() !== $question_pro_id ) ) {
					continue;
				}

				$question_data       = $question_model->get_object_as_array();
				$controller_question = new \WpProQuiz_Controller_Question();

				if ( $question_model && is_a( $question_model, 'WpProQuiz_Model_Question' ) ) {
					$answers_data = $controller_question->setAnswerObject( $question_model );
				} else {
					$answers_data = $controller_question->setAnswerObject();
				}

				$processed_answers = [];
				foreach ( $answers_data as $answer_type => $answers ) {
					foreach ( $answers as $answer ) {
						$processed_answers[ $answer_type ][] = [
							'answer'  => $answer->getAnswer(),
							'points'  => $answer->getPoints(),
							'correct' => $answer->isCorrect(),
							'type'    => 'answer',
						];
					}
				}

				// Output question's data and answers.
				$output_questions['question'] = [
					'ID'            => $question_id,
					'post_content'  => $question_data['_question'],
					'type'          => $question_post->post_type,
					'question_type' => $question_data['_answerType'],
					'points'        => $question_data['_points'],
					'answers'       => $processed_answers,
				];
			}
		}

		return $output_questions;
	}

	/**
	 * Return user pluggable info.
	 *
	 * @param int $user_id User ID.
	 * @return array User Data.
	 */
	public static function get_user_pluggable_data( $user_id ) {
		$user = get_user_by( 'ID', $user_id );

		return [
			'ID'           => $user->data->ID,
			'display_name' => $user->data->display_name,
			'email'        => $user->data->user_email,
			'login'        => $user->data->user_login,
		];
	}

	/**
	 * Return group pluggable info.
	 *
	 * @param int $group_id Group ID.
	 * @return array Group Data.
	 */
	public static function get_group_pluggable_data( $group_id ) {
		$group = get_post( (int) $group_id, ARRAY_A );

		return [
			'ID'      => $group['ID'],
			'title'   => $group['post_title'],
			'content' => $group['post_content'],
			'status'  => $group['post_status'],
			'GUID'    => $group['guid'],
		];
	}

	/**
	 * Return course pluggable info.
	 *
	 * @param int $course_id Course ID.
	 * @return array Course Data.
	 */
	public static function get_course_pluggable_data( $course_id ) {
		$course = get_post( (int) $course_id, ARRAY_A );

		return [
			'ID'                 => $course['ID'],
			'title'              => $course['post_title'],
			'URL'                => get_permalink( $course['ID'] ),
			'status'             => $course['post_status'],
			'featured_image_id'  => get_post_meta( $course['ID'], '_thumbnail_id', true ),
			'featured_image_url' => get_the_post_thumbnail_url( $course['ID'] ),
		];
	}

	/**
	 * Purchase course context.
	 *
	 * @param object $order order.
	 * @return array
	 */
	public static function get_purchase_course_context( $order ) {
		$context     = [];
		$items       = $order->get_items();
		$product_ids = [];

		foreach ( $items as $item ) {
			if ( ! empty( get_post_meta( $item->get_product_id(), '_related_course', true ) ) ) {
				$product                                        = wc_get_product( $item->get_product_id() );
				$product_ids[ $item->get_product_id() ]['ID']   = $item->get_product_id();
				$product_ids[ $item->get_product_id() ]['name'] = $product->get_name();
			}
		}

		$purchased_course_name = implode(
			', ',
			array_map(
				function ( $entry ) {
					return $entry['name'];
				},
				$product_ids
			)
		);
		$purchased_course_id   = implode(
			', ',
			array_map(
				function ( $entry ) {
					return $entry['ID'];
				},
				$product_ids
			)
		);

		$purchase_details               = $order->get_data();
		$context['course_product_id']   = empty( $purchased_course_id ) ? 0 : $purchased_course_id;
		$context['course_product_name'] = $purchased_course_name;
		$context['currency']            = $purchase_details['currency'];
		$context['total_amount']        = $purchase_details['total'];
		$context['first_name']          = $purchase_details['billing']['first_name'];
		$context['last_name']           = $purchase_details['billing']['last_name'];
		$context['email']               = $purchase_details['billing']['email'];
		$context['phone']               = $purchase_details['billing']['phone'];

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'SFWD_LMS' );
	}
}

IntegrationsController::register( LearnDash::class );
