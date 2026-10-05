<?php
/** Home / Service, Financing, Delivery callouts -- add/remove/reorder blocks. Alternates dark/orange cards. */
$items = vss_repeater( 'home', 'callouts', 'items' );
if ( empty( $items ) ) {
	return;
}
?>
<section class="tw-section--tight">
	<div class="tw-container tw-grid tw-grid--callout">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php $variant = 0 === $i % 2 ? 'is-dark' : 'is-accent'; ?>
			<?php if ( $item['link'] ) : ?>
				<a href="<?php echo esc_url( $item['link'] ); ?>" class="tw-callout-card <?php echo esc_attr( $variant ); ?>">
					<?php if ( ! empty( $item['image'] ) ) : ?><img class="tw-callout-img" src="<?php echo esc_url( $item['image'] ); ?>" alt="" /><?php endif; ?>
					<div class="tw-callout-title"><?php echo esc_html( $item['title'] ); ?></div>
					<p class="tw-callout-desc"><?php echo esc_html( $item['desc'] ); ?></p>
				</a>
			<?php else : ?>
				<div class="tw-callout-card <?php echo esc_attr( $variant ); ?>">
					<?php if ( ! empty( $item['image'] ) ) : ?><img class="tw-callout-img" src="<?php echo esc_url( $item['image'] ); ?>" alt="" /><?php endif; ?>
					<div class="tw-callout-title"><?php echo esc_html( $item['title'] ); ?></div>
					<p class="tw-callout-desc"><?php echo esc_html( $item['desc'] ); ?></p>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</section>
