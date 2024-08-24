<?php

namespace PrestoPlayer\Integrations\Elementor;

use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use PrestoPlayer\Models\Preset;
use PrestoPlayer\Blocks\VimeoBlock;
use PrestoPlayer\Blocks\YouTubeBlock;
use PrestoPlayer\Blocks\SelfHostedBlock;
use Elementor\Modules\DynamicTags\Module as TagsModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * presto-player video widget.
 *
 * presto-player widget that displays a video player.
 *
 * @since 1.0.0
 */
class VideoWidget extends Widget_Base {

	private $is_premium = false;
	private $version    = '';

	public function setPremium( $pro ) {
		$this->is_premium = $pro;
	}

	public function setVersion( $version ) {
		$this->version = $version;
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve video widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'presto_video';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve video widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return __( 'Presto Video', 'presto-player' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve video widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-youtube';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the video widget belongs to.
	 *
	 * Used to determine where to display the widget in the editor.
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'basic' );
	}

	/**
	 * Get widget keywords.
	 *
	 * Retrieve the list of keywords the widget belongs to.
	 *
	 * @since 2.1.0
	 * @access public
	 *
	 * @return array Widget keywords.
	 */
	public function get_keywords() {
		return array( 'video', 'player', 'embed', 'youtube', 'vimeo' );
	}

	public function fake_toggle_html( $label = '' ) {
		return '<div class="elementor-control-muted_preview elementor-control-type-switcher elementor-label-inline elementor-control-separator-default">
					<div class="elementor-control-content">
						<div class="elementor-control-field">
							<label for="elementor-control-default-c513" class="elementor-control-title">' . esc_html( $label ) . ' <i class="eicon-pro-icon"></i></label>
							<div class="elementor-control-input-wrapper">
								<label class="elementor-switch elementor-control-unit-2">
									<input id="elementor-control-default-c513" type="checkbox" data-setting="muted_preview" class="elementor-switch-input" value="yes" disabled>
									<span class="elementor-switch-label" data-on="Yes" data-off="No"></span>
									<span class="elementor-switch-handle"></span>
								</label>
							</div>
						</div>
					</div>
				</div>';
	}

	/**
	 * Register video widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_video',
			array(
				'label' => __( 'Video', 'presto-player' ),
			)
		);

		$this->add_control(
			'video_type',
			array(
				'label'   => __( 'Source', 'presto-player' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'youtube',
				'options' => array(
					'youtube' => __( 'YouTube', 'presto-player' ),
					'vimeo'   => __( 'Vimeo', 'presto-player' ),
					'hosted'  => __( 'Self Hosted', 'presto-player' ),
				),
			)
		);

		$this->add_control(
			'youtube_url',
			array(
				'label'       => __( 'Link', 'presto-player' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'placeholder' => __( 'Enter your URL', 'presto-player' ) . ' (YouTube)',
				'default'     => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
				'label_block' => true,
				'condition'   => array(
					'video_type' => 'youtube',
				),
			)
		);

		$this->add_control(
			'vimeo_url',
			array(
				'label'       => __( 'Link', 'presto-player' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'placeholder' => __( 'Enter your URL', 'presto-player' ) . ' (Vimeo)',
				'default'     => 'https://vimeo.com/235215203',
				'label_block' => true,
				'condition'   => array(
					'video_type' => 'vimeo',
				),
			)
		);

		$this->add_control(
			'hosted_url',
			array(
				'label'      => __( 'Add/Select Video', 'presto-player' ),
				'type'       => Controls_Manager::MEDIA,
				'dynamic'    => array(
					'active'     => true,
					'categories' => array(
						TagsModule::MEDIA_CATEGORY,
					),
				),
				'media_type' => 'video',
				'condition'  => array(
					'video_type' => 'hosted',
				),
			)
		);

		$this->add_control(
			'external_url',
			array(
				'label'        => __( 'URL', 'presto-player' ),
				'type'         => Controls_Manager::URL,
				'autocomplete' => false,
				'options'      => false,
				'label_block'  => true,
				'show_label'   => false,
				'dynamic'      => array(
					'active'     => true,
					'categories' => array(
						TagsModule::POST_META_CATEGORY,
						TagsModule::URL_CATEGORY,
					),
				),
				'media_type'   => 'video',
				'placeholder'  => __( 'Enter your URL', 'presto-player' ),
				'condition'    => array(
					'video_type' => 'hosted',
					'insert_url' => 'yes',
				),
			)
		);

		$this->add_control(
			'video_options',
			array(
				'label'     => __( 'Video Options', 'presto-player' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		if ( ! $this->is_premium ) {
			$this->add_control(
				'important_note',
				array(
					'type'      => \Elementor\Controls_Manager::RAW_HTML,
					'raw'       => $this->fake_toggle_html( __( 'Muted Preview', 'presto-player' ) ),
					'condition' => array(
						'autoplay' => '',
					),
				)
			);
		} else {
			$this->add_control(
				'muted_preview',
				array(
					'label'     => __( 'Muted Preview', 'presto-player' ),
					'type'      => Controls_Manager::SWITCHER,
					'condition' => array(
						'autoplay' => '',
					),
				)
			);
		}

		$this->add_control(
			'autoplay',
			array(
				'label'     => __( 'Autoplay', 'presto-player' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => array(
					'muted_preview' => '',
				),
			)
		);

		$this->add_control(
			'play-inline',
			array(
				'label' => __( 'Play inline', 'presto-player' ),
				'type'  => Controls_Manager::SWITCHER,
			)
		);

		$this->add_control(
			'poster',
			array(
				'label' => __( 'Poster', 'presto-player' ),
				'type'  => Controls_Manager::MEDIA,

			)
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_video_style',
			array(
				'label' => __( 'Video Preset', 'presto-player' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$options = $this->get_preset_options();

		$this->add_control(
			'preset',
			array(
				'label'              => __( 'Select A Preset', 'presto-player' ),
				'type'               => Controls_Manager::SELECT,
				'options'            => $options['options'],
				'default'            => $options['default_id'],
				'frontend_available' => true,
			)
		);

		$this->end_controls_section();
	}

	protected function get_preset_options() {
		$presets = new Preset();
		$presets = $presets->all();

		$preset_options = array();
		$default_id     = 0;
		if ( ! empty( $presets ) ) {
			foreach ( $presets as $preset ) {
				if ( $preset->slug === 'default' ) {
					$default_id = $preset->id;
				}
				$preset_options[ $preset->id ] = $preset->name;
			}
		}

		return array(
			'options'    => $preset_options,
			'default_id' => $default_id,
		);
	}

	/**
	 * Render video widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		switch ( $settings['video_type'] ) {
			case 'hosted':
				$hosted = new SelfHostedBlock( $this->is_premium, $this->version );
				echo $hosted->html(
					array(
						'id'          => $settings['hosted_url']['id'],
						'src'         => $settings['hosted_url']['url'],
						'preset'      => $settings['preset'],
						'autoplay'    => $settings['autoplay'],
						'playsInline' => $settings['play-inline'],
						'poster'      => ! empty( $settings['poster']['url'] ) ? $settings['poster']['url'] : '',
						'chapters'    => array(),
					),
					''
				);
				break;
			case 'youtube':
				$youtube = new YouTubeBlock( $this->is_premium, $this->version );
				echo $youtube->html(
					array(
						'src'         => $settings['youtube_url'],
						'preset'      => $settings['preset'],
						'autoplay'    => $settings['autoplay'],
						'playsInline' => $settings['play-inline'],
						'poster'      => ! empty( $settings['poster']['url'] ) ? $settings['poster']['url'] : '',
						'chapters'    => array(),
					),
					''
				);
				break;
			case 'vimeo':
				$vimeo = new VimeoBlock( $this->is_premium, $this->version );
				echo $vimeo->html(
					array(
						'src'         => $settings['vimeo_url'],
						'preset'      => $settings['preset'],
						'autoplay'    => $settings['autoplay'],
						'playsInline' => $settings['play-inline'],
						'poster'      => ! empty( $settings['poster']['url'] ) ? $settings['poster']['url'] : '',
						'chapters'    => array(),
					),
					''
				);
				break;
		}

		// print_r($settings);
	}


	/**
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_params() {
		$settings = $this->get_settings_for_display();

		$video_params = array();

		foreach ( array( 'autoplay', 'loop', 'controls' ) as $option_name ) {
			if ( $settings[ $option_name ] ) {
				$video_params[ $option_name ] = '';
			}
		}

		if ( $settings['mute'] ) {
			$video_params['muted'] = 'muted';
		}

		if ( $settings['play_on_mobile'] ) {
			$video_params['playsinline'] = '';
		}

		if ( ! $settings['download_button'] ) {
			$video_params['controlsList'] = 'nodownload';
		}

		if ( $settings['poster']['url'] ) {
			$video_params['poster'] = $settings['poster']['url'];
		}

		return $video_params;
	}

	/**
	 * @param bool $from_media
	 *
	 * @return string
	 * @since 2.1.0
	 * @access private
	 */
	private function get_hosted_video_url() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['insert_url'] ) ) {
			$video_url = $settings['external_url']['url'];
		} else {
			$video_url = $settings['hosted_url']['url'];
		}

		if ( empty( $video_url ) ) {
			return '';
		}

		if ( $settings['start'] || $settings['end'] ) {
			$video_url .= '#t=';
		}

		if ( $settings['start'] ) {
			$video_url .= $settings['start'];
		}

		if ( $settings['end'] ) {
			$video_url .= ',' . $settings['end'];
		}

		return $video_url;
	}

	/**
	 *
	 * @since 2.1.0
	 * @access private
	 */
	private function render_hosted_video() {
		$video_url = $this->get_hosted_video_url();
		if ( empty( $video_url ) ) {
			return;
		}

		$video_params = $this->get_hosted_params();
		?>
		<video class="presto-player-video" src="<?php echo esc_url( $video_url ); ?>" <?php echo Utils::render_html_attributes( $video_params ); ?>></video>
		<?php
	}
}
