<?php
/**
 * WP-Polls core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WpPolls;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WpPolls
 */
class WpPolls extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WpPolls';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WP-Polls', 'suretriggers' );
		$this->description = __( 'WP-Polls is a WordPress polls plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wppolls.svg';

		parent::__construct();
	}

	/**
	 * Get Poll context data.
	 *
	 * @param string $selected_answers_ids Selected Answers IDs.
	 * @param int    $poll_id Poll ID.
	 *
	 * @return array
	 */
	public static function get_poll_context( $selected_answers_ids, $poll_id ) {
		$context            = [];
		$context['poll_id'] = $poll_id;

		global $wpdb;
		$poll_data = $wpdb->get_row(
        // phpcs:disable
			$wpdb->prepare(
				"
                SELECT
                    p.pollq_question AS question,
                    GROUP_CONCAT(a.polla_answers SEPARATOR ', ') AS answers,
                    p.pollq_timestamp AS start_date,
                    p.pollq_expiry AS end_date,
                    sa.selected_answers
                FROM {$wpdb->prefix}pollsq p
                LEFT JOIN {$wpdb->prefix}pollsa a ON p.pollq_id = a.polla_qid
                LEFT JOIN (
                    SELECT polla_qid, GROUP_CONCAT(polla_answers SEPARATOR ', ') AS selected_answers
                    FROM {$wpdb->prefix}pollsa
                    WHERE polla_aid IN ($selected_answers_ids)
                    GROUP BY polla_qid -- Group by polla_qid to avoid duplicate rows
                ) AS sa ON p.pollq_id = sa.polla_qid
                WHERE p.pollq_id = %d
                GROUP BY p.pollq_question, p.pollq_timestamp, p.pollq_expiry
            ",
				$poll_id
			)
        // phpcs:enable
		);

		if ( ! empty( $poll_data ) ) {
			$context['question']         = $poll_data->question;
			$context['answers']          = $poll_data->answers;
			$context['start_date']       = gmdate( 'Y-m-d H:i:s', $poll_data->start_date );
			$context['end_date']         = 0 == $poll_data->end_date ? 'Not set' : gmdate( 'Y-m-d H:i:s', $poll_data->end_date );
			$context['selected_answers'] = $poll_data->selected_answers;
		}

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return defined( 'WP_POLLS_VERSION' );
	}
}

IntegrationsController::register( WpPolls::class );
