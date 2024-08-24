<?php

/**
 * Template Name: single-pp_video_block
 * Description: custom template for media hub item.
 * Template Post Type: pp_video_block
 */

use PrestoPlayer\Models\ReusableVideo;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <?php
    $media_hub         = new ReusableVideo();
    $poster_url        = $media_hub->getPosterFromBlock();
    $style             = $poster_url ? "background-image: url(" .  esc_url($poster_url) . ");" : "";
    $video_width_style = "width: " . $media_hub->getInstantVideoWidth();
    ?>
    <div class="pp-content" style="<?php echo esc_attr($style); ?>">
        <header class="pp-header" style="<?php echo esc_attr($style); ?>">
            <!-- <div class="pp-header__back-icon">
                <?php /* if (!empty(wp_get_referer())) : */ ?>
                    <a id="pp-back-btn" href="<?php echo esc_url(wp_get_referer()); ?>" class="pp-header__header-icon">
                        <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.625 12.75L6.375 8.5L10.625 4.25" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </a>
                <?php /* endif; */ ?>
            </div> -->
            <h1 class="pp-header__title">
                <span class="pp-header__title-text">
                    <?php the_title(); ?>
                </span>
                <?php if (empty($media_hub->instantVideoPageEnabled())) : ?>
                    <span class="pp-status-tag"><?php esc_html_e('Unpublished', 'presto-player'); ?></span>
                <?php endif; ?>
            </h1>
            <!-- <div class="pp-header__home-icon">
                <a href="<?php /* echo esc_url(get_home_url()); */ ?>" id="pp-home-btn" class="pp-header__header-icon">
                    <svg width="17" height="17" viewBox="0 0 17 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M2.125 6.37502L8.5 1.41669L14.875 6.37502V14.1667C14.875 14.5424 14.7257 14.9027 14.4601 15.1684C14.1944 15.4341 13.8341 15.5834 13.4583 15.5834H3.54167C3.16594 15.5834 2.80561 15.4341 2.53993 15.1684C2.27426 14.9027 2.125 14.5424 2.125 14.1667V6.37502Z" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6.375 15.5833V8.5H10.625V15.5833" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            </div> -->
        </header>
        <main class="pp-video" id="post-<?php the_ID(); ?>" style="<?php esc_attr_e($video_width_style) ?>">
            <?php the_content(); ?>
        </main>
    </div>
    <?php wp_footer(); ?>
</body>
</html>