<?php

namespace PrestoPlayer\Services;

use PrestoPlayer\Models\ReusableVideo;
use PrestoPlayer\Models\Video;

class VideoPostType {

	protected $post_type = 'pp_video_block';

	public function register() {
		global $wp_version;

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'init', array( $this, 'registerMetaSettings' ) );

		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			add_filter( 'allowed_block_types_all', array( $this, 'allowedTypes' ), 10, 2 );
		} else {
			add_filter( 'allowed_block_types', array( $this, 'allowedTypesDeprecated' ), 10, 2 );
		}

		add_filter( 'enter_title_here', array( $this, 'videoTitle' ) );

		// post type ui
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'postTypeColumns' ), 1 );
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'postTypeColumnContent' ), 10, 2 );

		// filter by tags
		add_action( 'restrict_manage_posts', array( $this, 'tagFilter' ) );
		add_action( 'parse_query', array( $this, 'tagQuery' ) );

		// force gutenberg here
		add_action( 'use_block_editor_for_post', array( $this, 'forceGutenberg' ), 999, 2 );

		// limit media hub posts
		add_filter( 'pre_get_posts', array( $this, 'limitMediaHubPosts' ) );

		// redirect to 404 if instant video page not published
		add_action( 'template_redirect', array( $this, 'maybeRedirectTo404' ) );

		// filter the single template.
		add_filter( 'single_template', array( $this, 'singleTemplate' ) );

		// script for instant video page dropdown on editor toolbar
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueEditorToolbarScript' ) );

		add_filter( 'post_thumbnail_id', array( $this, 'attachPoster' ), 10, 2 );

		add_filter( 'the_title', array( $this, 'filterVideoTitle' ), 10, 2 );

		add_filter( 'rest_prepare_' . $this->post_type, array( $this, 'addTitleField' ), 10, 3 );

		add_action( 'transition_post_status', array( $this, 'set_post_title' ), 10, 3 );
	}

	/**
	 * Limit media hub posts by author if cannot edit others posts
	 *
	 * @param  \WP_Query $query
	 * @return \WP_Query
	 */
	public function limitMediaHubPosts( $query ) {
		global $pagenow, $typenow;

		if ( 'edit.php' != $pagenow || ! $query->is_admin || $this->post_type !== $typenow ) {
			return $query;
		}

		if ( ! current_user_can( 'edit_others_posts' ) ) {
			$query->set( 'author', get_current_user_id() );
		}

		return $query;
	}

	/**
	 * Force gutenberg in case of classic editor
	 */
	public function forceGutenberg( $use, $post ) {
		if ( $this->post_type === $post->post_type ) {
			return true;
		}

		return $use;
	}

	/**
	 * Columns on all posts page
	 *
	 * @param  array $defaults
	 * @return array
	 */
	public function postTypeColumns( $defaults ) {
		$columns = array_merge(
			$defaults,
			array(
				'poster'    => esc_html__( 'Poster', 'presto-player' ),
				'title'     => $defaults['title'],
				'shortcode' => esc_html__( 'Shortcode', 'presto-player' ),
			)
		);

		$v = $columns['taxonomy-pp_video_tag'];
		unset( $columns['taxonomy-pp_video_tag'] );
		$columns['taxonomy-pp_video_tag'] = $v;

		$v = $columns['poster'];
		unset( $columns['poster'] );
		$columns['poster'] = $v;

		$v = $columns['date'];
		unset( $columns['date'] );
		$columns['date'] = $v;

		// return re-arranged columns.
		return array(
			'cb'         => $columns['cb'],
			'title'      => $columns['title'],
			'poster'     => $columns['poster'],
			'video_tags' => $columns['taxonomy-pp_video_tag'],
			'shortcode'  => $columns['shortcode'],
			'date'       => $columns['date'],
		);
	}

	/**
	 * Renders column content for custom post types.
	 *
	 * @param string $column_name The name of the column to render content for.
	 * @param int    $post_ID    The ID of the post for which to render the column content.
	 */
	public function postTypeColumnContent( $column_name, $post_ID ) {
		$output = '';
		switch ( $column_name ) {
			case 'shortcode':
				$output = $this->renderShortcodeColumn( $post_ID );
				break;
			case 'video_tags':
				$output = $this->renderVideoTagsColumn( $post_ID );
				break;
			case 'poster':
				$output = $this->renderTitleWithPosterColumn( $post_ID );
				break;
		}
		echo $output;
	}

	/**
	 * Renders the shortcode column content.
	 *
	 * @param int $post_ID The ID of the post for which to render the shortcode.
	 */
	public function renderShortcodeColumn( $post_ID ) {
		ob_start();
		?>
		<code>[presto_player id=<?php echo (int) $post_ID; ?>]</code>
		<?php
		return ob_get_clean();
	}

	/**
	 * Renders the video tags column content.
	 *
	 * @param int $post_ID The ID of the post for which to render the video tags.
	 * @return string buffer output string.
	 */
	public function renderVideoTagsColumn( $post_ID ) {
		ob_start();
		$tags = get_the_terms( $post_ID, 'pp_video_tag' );
		if ( is_array( $tags ) ) {
			foreach ( $tags as $key => $tag ) {
				$tags[ $key ] = '<a href="?post_type=pp_video_block&pp_video_tag=' . $tag->term_id . '">' . $tag->name . '</a>';
			}
			echo implode( ', ', $tags );
		}
		return ob_get_clean();
	}

	/**
	 * Renders the title with poster column content.
	 *
	 * @param int $post_ID The ID of the post for which to render the title with poster.
	 * @return string buffer output string.
	 */
	public function renderTitleWithPosterColumn( $post_ID ) {
		ob_start();
		$thumbnail = get_the_post_thumbnail(
			$post_ID,
			'',
			array(
				'style' => 'width: 75px; height: auto; aspect-ratio: 16/9; object-fit: cover; border-radius: 2px;',
			)
		);
		?>
		<div class='pp-container' style="display: flex; justify-content: flex-start;">
			<?php if ( '' !== $thumbnail ) : ?>
				<div class='pp-container__media-icon pp-container__media-icon--image'><?php echo $thumbnail; ?></div>
			<?php else : ?>
				<div class='pp-container__media-icon pp-container__media-icon--image' style="
					width: 75px;
					aspect-ratio: 16/9;
					box-sizing: border-box;
					background-color: #b8b8b8;
					border-radius: 2px;
					display: flex;
					justify-content: center;
					align-items: center;">
					<?php
					$svg_content = file_get_contents( PRESTO_PLAYER_PLUGIN_DIR . '/img/icon-white.svg' );

					// Define allowed SVG elements and attributes
					$svg_args = array(
						'svg'  => array(
							'width'   => true,
							'height'  => true,
							'viewbox' => true,
							'fill'    => true,
							'xmlns'   => true,
							'style'   => true,
						),
						'path' => array(
							'd'            => true,
							'fill'         => true,
							'fill-opacity' => true,
						),
					);

					// Merge the default allowed HTML tags with the SVG arguments
					$allowed_tags = array_merge( wp_kses_allowed_html( 'post' ), $svg_args );

					// Initialize the WP_HTML_Tag_Processor
					$processor = new \WP_HTML_Tag_Processor( $svg_content );

					// Find the SVG tag and set the width and height attributes
					if ( $processor->next_tag( 'svg' ) ) {
						$processor->set_attribute( 'width', '20px' );
						$processor->set_attribute( 'height', 'auto' );
					}

					echo wp_kses( $processor->get_updated_html(), $allowed_tags );
					?>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public function videoTitle( $title ) {
		$screen = get_current_screen();
		if ( $this->post_type == $screen->post_type ) {
			$title = __( 'Enter a title...', 'presto-player' );
		}
		return $title;
	}

	/**
	 * Allowed block types
	 *
	 * @param  array  $allowed_block_types
	 * @param  object $block_editor_content
	 * @return void
	 */
	public function allowedTypes( $allowed_block_types, $block_editor_content ) {
		if ( ! empty( $block_editor_content->post->post_type ) ) {
			if ( $block_editor_content->post->post_type === $this->post_type ) {
				return array(
					'presto-player/reusable',
					'presto-player/self-hosted',
					'presto-player/youtube',
					'presto-player/vimeo',
					'presto-player/bunny',
					'presto-player/audio',
				);
			}
		}

		return $allowed_block_types;
	}

	public function allowedTypesDeprecated( $allowed_block_types, $post ) {
		if ( $post->post_type !== $this->post_type ) {
			return $allowed_block_types;
		}

		return array(
			'presto-player/reusable',
			'presto-player/self-hosted',
			'presto-player/youtube',
			'presto-player/vimeo',
			'presto-player/bunny',
			'presto-player/audio',
		);
	}

	/**
	 * Register post type
	 *
	 * @return void
	 */
	public function init() {
		register_taxonomy(
			'pp_video_tag',
			$this->post_type,
			array(
				'labels'            => array(
					'name'          => _x( 'Media Tags', 'post type general name' ),
					'singular_name' => _x( 'Media Tag', 'post type singular name' ),
					'search_items'  => _x( 'Search Media Tags', 'admin menu' ),
					'popular_items' => _x( 'Popular Media Tags', 'add new on admin bar' ),
				),
				'label'             => __( 'Tag', 'presto-player' ),
				'public'            => false,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);

		register_post_type(
			$this->post_type,
			array(
				'labels'                => array(
					'name'                     => _x( 'Media Hub', 'post type general name', 'presto-player' ),
					'singular_name'            => _x( 'Media', 'post type singular name', 'presto-player' ),
					'menu_name'                => _x( 'Media', 'admin menu', 'presto-player' ),
					'name_admin_bar'           => _x( 'Media', 'add new on admin bar', 'presto-player' ),
					'add_new'                  => _x( 'Add New', 'Media', 'presto-player' ),
					'add_new_item'             => __( 'Add New Media', 'presto-player' ),
					'new_item'                 => __( 'New Media', 'presto-player' ),
					'edit_item'                => __( 'Edit Media', 'presto-player' ),
					'view_item'                => __( 'View Media', 'presto-player' ),
					'all_items'                => __( 'Media Hub', 'presto-player' ),
					'search_items'             => __( 'Search Media', 'presto-player' ),
					'not_found'                => __( 'No Media found.', 'presto-player' ),
					'not_found_in_trash'       => __( 'No Media found in Trash.', 'presto-player' ),
					'filter_items_list'        => __( 'Filter Media list', 'presto-player' ),
					'items_list_navigation'    => __( 'Media list navigation', 'presto-player' ),
					'items_list'               => __( 'Media list', 'presto-player' ),
					'item_published'           => __( 'Media published.', 'presto-player' ),
					'item_published_privately' => __( 'Media published privately.', 'presto-player' ),
					'item_reverted_to_draft'   => __( 'Media reverted to draft.', 'presto-player' ),
					'item_scheduled'           => __( 'Media scheduled.', 'presto-player' ),
					'item_updated'             => __( 'Media updated.', 'presto-player' ),
				),
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => 'edit.php?post_type=pp_video_block',
				'rewrite'               => array(
					'slug'       => 'media',
					'with_front' => false,
				),
				'show_in_rest'          => true,
				'rest_base'             => 'presto-videos',
				'rest_controller_class' => 'WP_REST_Blocks_Controller',
				'map_meta_cap'          => true,
				'supports'              => array(
					'title',
					'editor',
					'custom-fields',
				),
				'taxonomies'            => array( 'pp_video_tag' ),
				'template'              => array(
					array( 'presto-player/reusable-edit' ),
				),
				'template_lock'         => 'all',
			)
		);
	}

	/**
	 * Adds a tag filter dropdown
	 *
	 * @return void
	 */
	public function tagFilter() {
		global $typenow;

		$post_type = 'pp_video_block';
		$taxonomy  = 'pp_video_tag';

		if ( $typenow !== $post_type ) {
			return;
		}

		$selected      = isset( $_GET[ $taxonomy ] ) ? $_GET[ $taxonomy ] : '';
		$info_taxonomy = get_taxonomy( $taxonomy );

		wp_dropdown_categories(
			array(
				'show_option_all' => sprintf( __( 'Show all %s', 'textdomain' ), $info_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			)
		);
	}

	/**
	 * Modify admin query for tag
	 *
	 * @param  \WP_Query $query
	 * @return void
	 */
	public function tagQuery( $query ) {
		global $pagenow;

		$post_type = $this->post_type;
		$taxonomy  = 'pp_video_tag';

		$q_vars = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == $post_type && isset( $q_vars[ $taxonomy ] ) && is_numeric( $q_vars[ $taxonomy ] ) && $q_vars[ $taxonomy ] != 0 ) {
			$term                = get_term_by( 'id', $q_vars[ $taxonomy ], $taxonomy );
			$q_vars[ $taxonomy ] = $term->slug;
		}
	}

	/**
	 * Retrieves the video title from a media hub block for a given post ID.
	 *
	 * @param int $post_id The ID of the post from which to retrieve the video title.
	 *
	 * @return string|null The video title if found, null otherwise.
	 */
	public function getVideoTitleFromBlock( $post_id ) {
		$block = $this->getMediaHubBlock( get_post( $post_id ) );
		if ( isset( $block['attrs']['id'] ) ) {
			$video         = new Video( $block['attrs']['id'] );
			$attachment_id = $video->getAttachmentID();
			if ( ! empty( $attachment_id ) ) {
				$attachment_title = $video->getAttachmentPostTitle( $attachment_id );
				if ( ! empty( $attachment_title ) ) {
					return $attachment_title;
				}
			}
			$video_title = $video->getTitle();
			if ( ! empty( $video_title ) ) {
				return $video_title;
			}
		}
		return null;
	}

	/**
	 * Get the the title fallback.
	 *
	 * @param string $title   The title.
	 * @param int    $post_id The ID of the post.
	 *
	 * @return string The title fallback.
	 */
	public function getTitleFallback( $title, $post_id ) {
		if ( ! isset( $post_id ) ) {
			return $title;
		}
		// Include the translated block name in title if available.
		$block = ( new ReusableVideo( $post_id ) )->getAttributes();
		if ( $block ) {
			if ( ! empty( $block['name'] ) ) {
				$title = ( $block['name'] ?? '' ) . ' #' . $post_id;
			}
		}
		return $title;
	}

	/**
	 * Get the translated block name.
	 *
	 * @param int $blockName The block name.
	 *
	 * @return string The translated block name.
	 */
	public function getTranslatedBlockName( $blockName ) {
		if ( empty( $blockName ) ) {
			return '';
		}
		$translation_map = array(
			'presto-player/self-hosted' => __( 'Self-hosted', 'presto-player' ),
			'presto-player/audio'       => __( 'Audio', 'presto-player' ),
			'presto-player/vimeo'       => __( 'Vimeo', 'presto-player' ),
			'presto-player/youtube'     => __( 'Youtube', 'presto-player' ),
			'presto-player/bunny'       => __( 'Bunny', 'presto-player' ),
		);
		return $translation_map[ $blockName ];
	}

	/**
	 * Register the meta settings for the video block
	 *
	 * @return void
	 */
	public function registerMetaSettings() {
		register_post_meta(
			$this->post_type,
			'presto_player_instant_video_pages_enabled',
			array(
				'single'       => true,
				'type'         => 'boolean',
				'description'  => 'Enable Instant Video Pages',
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Redirect to 404 if the instant video page is disabled
	 *
	 * @return void
	 */
	public function maybeRedirectTo404() {
		global $post;
		if ( ! isset( $post ) ) {
			return;
		}
		if ( $this->post_type !== $post->post_type ) {
			return;
		}
		if ( current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		$media_hub_video = new ReusableVideo( $post->ID );
		if ( ! empty( $media_hub_video ) && empty( $media_hub_video->instantVideoPageEnabled() ) ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			get_template_part( 404 );
			exit();
		}
	}

	/**
	 * Attach the poster image URL to the video post.
	 *
	 * @param int     $id   The attachment ID.
	 * @param WP_Post $post The post object.
	 *
	 * @return int The attachment ID.
	 */
	public function attachPoster( $id, $post ) {
		if ( $this->post_type !== $post->post_type ) {
			return $id;
		}
		$block         = $this->getMediaHubBlock( $post );
		$poster        = ( ! empty( $block ) ) && isset( $block['attrs']['poster'] ) ? $block['attrs']['poster'] : '';
		$attachment_id = attachment_url_to_postid( $poster );
		return $attachment_id ? $attachment_id : $id;
	}

	/**
	 * Get the single template for the video block
	 *
	 * @param string $template The template file.
	 *
	 * @return string The template file
	 */
	public function singleTemplate( $template ) {
		global $post;

		if ( $this->post_type !== $post->post_type ) {
			return $template;
		}

		$theme_template = locate_template( array( 'single-presto-media.php' ) );
		if ( $theme_template ) {
			return $theme_template;
		}

		return PRESTO_PLAYER_PLUGIN_DIR . 'templates/single-presto-media.php';
	}

	/**
	 * Get the media hub block.
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return array|bool The media hub block array or false if block not found.
	 */
	public function getMediaHubBlock( $post ) {
		$blocks      = parse_blocks( $post->post_content );
		$first_block = wp_get_first_block( $blocks, 'presto-player/reusable-edit' );
		return isset( $first_block['innerBlocks'][0] ) ? $first_block['innerBlocks'][0] : false;
	}

	/**
	 * Register the editor toolbar script for
	 * instant video page dropdown on the editor toolbar.
	 *
	 * @return void
	 */
	public function enqueueEditorToolbarScript() {
		global $post_type;

		if ( $this->post_type !== $post_type ) {
			return;
		}

		$assets = include trailingslashit( PRESTO_PLAYER_PLUGIN_DIR ) . 'dist/toolbar.asset.php';
		wp_enqueue_script(
			'presto-player/toolbar/admin',
			trailingslashit( PRESTO_PLAYER_PLUGIN_URL ) . 'dist/toolbar.js',
			array_merge( array( 'jquery', 'regenerator-runtime' ), $assets['dependencies'] ?? array() ),
			$assets['version'],
			true
		);
	}

	/**
	 * Modify title for response.
	 *
	 * @param WP_REST_Response $response Response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  Request object.
	 *
	 * @return WP_REST_Response Response object with title property modified.
	 */
	public function addTitleField( $response, $post, $request ) {
		if ( $post->post_type !== $this->post_type ) {
			return $response;
		}
		if ( ! isset( $response->data['title'] ) ) {
			return $response;
		}
		if ( ! is_array( $response->data['title'] ) ) {
			return $response;
		}
		$response->data['title'] = get_the_title( $post->ID );
		return $response;
	}

	/**
	 * Update the post title to the video title on publish.
	 *
	 * @param string  $new_status new status of the post.
	 * @param string  $old_status old status of the post.
	 * @param WP_Post $post       Post object.
	 *
	 * @return void
	 */
	public function set_post_title( $new_status, $old_status, $post ) {
		if ( 'pp_video_block' !== $post->post_type ) {
			return;
		}
		if ( ! empty( $post->post_title ) ) {
			return;
		}
		$post_title = $this->getVideoTitleFromBlock( $post->ID );
		if ( empty( $post_title ) ) {
			return;
		}
		wp_update_post(
			array(
				'ID'         => $post->ID,
				'post_title' => $post_title,
			)
		);
	}

	/**
	 * Filters video post title to use the video's title
	 *
	 * @param string $title   The current title of the post.
	 * @param int    $post_id The ID of the post.
	 *
	 * @return string The filtered title, either the original title or the video's title if the original is empty.
	 */
	public function filterVideoTitle( $title, $post_id ) {
		if ( get_post_type( $post_id ) !== $this->post_type ) {
			return $title;
		}
		if ( ! empty( $title ) ) {
			return $title;
		}
		$videoTitle = $this->getVideoTitleFromBlock( $post_id );
		return $videoTitle ?? $this->getTitleFallback( $title, $post_id );
	}
}
