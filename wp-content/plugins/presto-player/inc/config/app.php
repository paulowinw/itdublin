<?php

return array(
	/*
	|--------------------------------------------------------------------------
	| Autoloaded Components
	|--------------------------------------------------------------------------
	|
	| The component classes listed here will be automatically loaded on the
	| request to your application.
	|
	*/
	'components'     => array(
		// seeder
		\PrestoPlayer\Seeds\Seeder::class,

		// files
		\PrestoPlayer\Files::class,
		\PrestoPlayer\Attachment::class,

		// blocks
		\PrestoPlayer\Blocks\SelfHostedBlock::class,
		\PrestoPlayer\Blocks\YouTubeBlock::class,
		\PrestoPlayer\Blocks\VimeoBlock::class,
		\PrestoPlayer\Blocks\ReusableVideoBlock::class,
		\PrestoPlayer\Blocks\ReusableEditBlock::class,
		\PrestoPlayer\Blocks\AudioBlock::class,
		\PrestoPlayer\Blocks\MediaHubBlock::class,

		// block services
		\PrestoPlayer\Services\Blocks\YoutubeBlockService::class,
		\PrestoPlayer\Services\Blocks\VimeoBlockService::class,

		// integrations
		\PrestoPlayer\Integrations\Kadence::class,
		\PrestoPlayer\Integrations\Divi\Divi::class,
		\PrestoPlayer\Integrations\Elementor\Elementor::class,
		\PrestoPlayer\Integrations\BeaverBuilder\BeaverBuilder::class,
		\PrestoPlayer\Integrations\LearnDash\LearnDash::class,
		\PrestoPlayer\Integrations\Tutor\Tutor::class,
		\PrestoPlayer\Integrations\Lifter\Lifter::class,

		// services
		\PrestoPlayer\Services\Migrations::class,
		\PrestoPlayer\Services\Translation::class,
		\PrestoPlayer\Services\Player::class,
		\PrestoPlayer\Services\Shortcodes::class,
		\PrestoPlayer\Services\Menu::class,
		\PrestoPlayer\Services\Scripts::class,
		\PrestoPlayer\Services\Blocks::class,
		\PrestoPlayer\Services\Settings::class,
		\PrestoPlayer\Services\VideoPostType::class,
		\PrestoPlayer\Services\ReusableVideos::class,
		\PrestoPlayer\Services\AdminNotices::class,
		\PrestoPlayer\Services\ProCompatibility::class,
		\PrestoPlayer\Services\Compatibility::class,
		\PrestoPlayer\Services\AjaxActions::class,

		// api
		\PrestoPlayer\Services\API\RestPresetsController::class,
		\PrestoPlayer\Services\API\RestAudioPresetsController::class,
		\PrestoPlayer\Services\API\RestSettingsController::class,
		\PrestoPlayer\Services\API\RestVideosController::class,
	),

	/*
	|--------------------------------------------------------------------------
	| Autoloaded Pro Components
	|--------------------------------------------------------------------------
	|
	| The component classes listed here will be automatically loaded
	| if another plugin adds to this filter
	|
	*/
	'pro_components' => apply_filters( 'presto_player_pro_components', array() ),
);
