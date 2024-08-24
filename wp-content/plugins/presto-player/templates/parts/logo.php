<?php if ($data['logo']) : ?>
    <img src="<?php echo esc_url($data['logo']); ?>" class="presto-player__logo is-bottom-right" style="width: <?php echo (int) $data['logo_width']; ?>px" />
<?php endif; ?>