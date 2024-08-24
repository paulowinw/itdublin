<?php foreach ($data['tracks'] as $track) : ?>
    <?php if (!empty($track['src'])) : ?>
        <track kind="captions" label="<?php echo !empty($track['label']) ? esc_attr($track['label']) : __('Captions', 'presto-player'); ?>" src="<?php echo esc_url($track['src']); ?>" srclang="<?php echo esc_attr(!empty($track['srcLang']) ? $track['srcLang'] : 'en'); ?>" />
    <?php endif; ?>
<?php endforeach; ?>