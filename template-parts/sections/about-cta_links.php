<?php
/** About / CTA pill links -- add/remove/reorder blocks. */
$links = vss_repeater( 'about', 'cta_links', 'links' );
if ( empty( $links ) ) {
	return;
}
?>
<div class="tw-container" style="max-width:820px;padding-bottom:60px;">
	<div style="display:flex;flex-wrap:wrap;gap:16px;">
		<?php foreach ( $links as $link ) : ?>
			<a href="<?php echo esc_url( $link['href'] ); ?>" class="tw-btn tw-btn-outline" style="background:#fff;border:1px solid var(--tw-border);"><?php echo esc_html( $link['text'] ); ?></a>
		<?php endforeach; ?>
	</div>
</div>
