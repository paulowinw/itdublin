<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Plugin;
use PrestoPlayer\Pro\Plugin as ProPlugin;

class ProCompatibility {

	protected $recommended_pro_version = '2.0.1';
	protected $required_pro_version    = '0.0.1';

	public function register() {
		add_action( 'admin_notices', array( $this, 'showRecommendedVersionNotice' ) );
	}

	public function hasVersion( $type = 'recommended' ) {
		if ( ! Plugin::isPro() ) {
			return true;
		}
		$version = $type === 'required' ? $this->required_pro_version : $this->recommended_pro_version;
		return ! version_compare( $version, ProPlugin::version(), '>' );
	}

	public function showRecommendedVersionNotice() {
		// has recommended version
		if ( $this->hasVersion( 'recommended' ) ) {
			return;
		}

		$notice_name = 'player_recommended_version_' . $this->recommended_pro_version;

		ob_start()
		?>
		<div class="notice notice-info">
			<p><strong>Presto Player</strong></p>
			<p><?php _e( 'Please update your Presto Player Pro plugin for compatibility with the Presto Player core plugin. This ensures you have access to new features and updates.', 'presto-player' ); ?></p>
			<p><?php printf( __( 'The recommeneded minimum pro version is <b>%s</b>.', 'presto-player' ), $this->recommended_pro_version ); ?></p>
			<p><a href="
			<?php
			echo esc_url(
				add_query_arg(
					array(
						'presto_action' => 'dismiss_notices',
						'presto_notice' => $notice_name,
					)
				)
			);
			?>
						"><?php _e( 'Dismiss Notice', 'presto-player' ); ?></a></p>
		</div>

		<?php
		echo ob_get_clean();
	}
}
