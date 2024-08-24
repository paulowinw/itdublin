<?php

namespace PrestoPlayer\Integrations\Lifter;

use PrestoPlayer\Models\Player;

/**
 * Lifter LMS integration.
 */
class Lifter {

	/**
	 * Register actions and filters.
	 *
	 * @return void
	 */
	public function register() {
		// wait for plugin to be loaded.
		add_action( 'plugins_loaded', array( $this, 'addFilters' ) );
		add_filter( 'presto_player_load_js', array( $this, 'maybeLoadScripts' ) );
	}

	/**
	 * Load scripts if it is a LifterLMS ( lesson,Quiz,Course ) page.
	 */
	public function maybeLoadScripts( $has_player ) {
		if ( function_exists( 'is_lifterlms' ) && is_lifterlms() ) {
			return true;
		}
		return $has_player;
	}

	/**
	 * Add filters needed for LifterLMS
	 */
	public function addFilters() {
		// plugins are not active
		if ( ! $this->lifterPluginsActive() ) {
			return;
		}

		// display video progress on student table.
		add_filter( 'llms_table_get_data_student-course', array( $this, 'displayVideoCompletionData' ), 11, 5 );

		// adding islesson attribute to our script.
		add_filter( 'presto-settings-block-js-options', array( $this, 'addLifterIsLesson' ) );

		// turn on autoplay and checks if it on lesson page or not.
		add_filter( 'presto_player/block/default_attributes', array( $this, 'maybeTurnOnAutoplay' ) );
	}

	/**
	 * Are the lifter plugins active?
	 *
	 * @return boolean
	 */
	public function lifterPluginsActive() {
		return is_plugin_active( 'lifterlms-advanced-videos/lifterlms-advanced-videos.php' ) && is_plugin_active( 'lifterlms/lifterlms.php' );
	}

	/**
	 * Is the lifterLMS autoplay on?
	 *
	 * @return boolean
	 */
	public function isLifterAutoplayOn() {
		return 'no' !== get_option( 'llms_av_prog_auto_play', 'no' );
	}

	/**
	 * Are we on a lesson page.
	 *
	 * @return boolean
	 */
	public function isLessonPage() {
		return function_exists( 'is_lesson' ) && is_lesson();
	}

	/**
	 * Override our player to turn on autoplay
	 * if enabled on LifterLMS settings.
	 *
	 * @param  array $attributes Block attributes.
	 * @return array
	 */
	public function maybeTurnOnAutoplay( $attributes ) {
		if ( $this->isLessonPage() && $this->isLifterAutoplayOn() ) {
			$attributes['autoplay'] = true;
		}

		return $attributes;
	}

	/**
	 * Send whether it's a lifter lesson
	 *
	 * @param  array $attributes Block attributes.
	 * @return array
	 */
	public function addLifterIsLesson( $attributes ) {
		if ( $this->isLessonPage() ) {
			$attributes['lifter']['isLesson'] = true;
		}

		return $attributes;
	}

	/**
	 * Does the post have a player?
	 *
	 * @param integer $id Post id.
	 * @return boolean
	 */
	public function hasPlayer( $id ) {
		return player::postHasPlayer( $id );
	}

	/**
	 * Get the student id.
	 *
	 * @return integer
	 */
	public function getStudentId() {
		return llms_filter_input( INPUT_GET, 'student_id', FILTER_SANITIZE_NUMBER_INT );
	}

	/**
	 * Render the progress bar
	 *
	 * @return void
	 */
	public function renderProgressBar( $table, $value ) {
		return $table->get_progress_bar_html( $value );
	}

	/**
	 * Has the student completed the video?
	 *
	 * @param integer $student_id Student ID
	 * @param integer $lesson_id Lesson ID
	 * @return boolean
	 */
	public function hasStudentHasCompletedVideo( $student_id, $lesson_id ) {
		return (bool) llms_av_has_user_completed_video( $student_id, $lesson_id );
	}

	/**
	 * Get the student's current progress.
	 *
	 * @param integer $student_id Student ID
	 * @param integer $lesson_id Lesson ID
	 * @return number
	 */
	public function getStudentProgress( $student_id, $lesson_id ) {
		$event = llms_av_get_last_user_video_progress_event( $student_id, $lesson_id );
		if ( $event ) {
			$duration = $event->get_meta( 'duration' );
			if ( $duration = $event->get_meta( 'duration' ) ) {
				$ts    = $event->get_meta( 'ts' ) ? $event->get_meta( 'ts' ) : 0;
				$value = LLMS()->grades()->round( ( $ts / $duration ) ) * 100;
				return $value;
			}
		}
		return null;
	}

	/**
	 * Displays video completion data on the
	 * student table.
	 *
	 * @param  string                     $value   Value to display.
	 * @param  string                     $key     Column name
	 * @param  \LLMS_Lesson               $lesson LifterLMS Lesson Object
	 * @param  mixed                      $context Context.
	 * @param  \LLMS_Table_Student_Course $table Table object.
	 * @return void
	 */
	public function displayVideoCompletionData( $value, $key, $lesson, $context, $table ) {
		// bail if not the video column.
		if ( 'video' !== $key ) {
			return $value;
		}

		if ( ! isset( $lesson ) ) {
			return $value;
		}

		// get the lesson id.
		$lesson_id = $lesson->post->ID;

		if ( empty( $lesson_id ) ) {
			return $value;
		}

		// Bail if lesson does not contain a Presto Player.
		if ( ! $this->hasPlayer( $lesson_id ) ) {
			return $value;
		}

		// get the student id.
		if ( ! $student_id = $this->getStudentId() ) {
			return $value;
		}

		// show the progress bar at 100% is user completed video.
		if ( $this->hasStudentHasCompletedVideo( $student_id, $lesson_id ) ) {
			return $this->renderProgressBar( $table, 100 );
		}

		// show progress bar if user has progress.
		if ( $progress = $this->getStudentProgress( $student_id, $lesson_id ) ) {
			return $this->renderProgressBar( $table, $progress );
		}

		// return default value.
		return $value;
	}
}
