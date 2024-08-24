<?php if (in_array($data['provider'], ['self-hosted', 'bunny'])) : ?>
    <?php if (!$data['is_hls']) : ?>
        <video controls preload="none">
            <source src="<?php echo esc_attr($data['src']); ?>" />
        </video>
    <?php endif; ?>
<?php endif; ?>

<?php if ('youtube' === $data['provider'] && empty($data['preset']['lazy_load_youtube']) && !empty($data['blockAttributes']['video_id'])) : ?>
    <div class="presto-iframe-fallback-container">
        <iframe style="width: 100%" title="Youtube Video" class="presto-fallback-iframe" id="presto-iframe-fallback-<?php echo (int) $presto_player_instance; ?>" data-src="https://www.youtube.com/embed/<?php echo esc_attr($data['blockAttributes']['video_id']); ?>?iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1" allowfullscreen allowtransparency allow="autoplay"></iframe>
    </div>
<?php endif; ?>

<?php if ('vimeo' === $data['provider'] && !empty($data['blockAttributes']['video_id'])) : ?>
    <div class="presto-iframe-fallback-container">
        <iframe style="width: 100%" title="Vimeo Video" class="presto-fallback-iframe" id="presto-iframe-fallback-<?php echo (int) $presto_player_instance; ?>" data-src="https://player.vimeo.com/video/<?php echo esc_attr($data['blockAttributes']['video_id']); ?>?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media" allowfullscreen allowtransparency allow="autoplay"></iframe>
    </div>
<?php endif; ?>