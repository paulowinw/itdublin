<figure class="wp-block-video presto-unauthorized" style="<?php echo esc_attr($data['styles']) ?>">
  <presto-video-curtain-ui>
    <span><?php _e('Please login for access.', 'presto-player') ?></span>
    <presto-player-button full type="primary" href="<?php echo esc_url(wp_login_url()); ?>">
      <?php _e('Login', 'presto-player'); ?>
    </presto-player-button>
  </presto-video-curtain-ui>
</figure>