<?php
/** Financing / Promo Blocks -- add/remove/reorder blocks, each with an image, heading, and button. */
$blocks = vss_repeater( 'financing', 'promo_blocks', 'blocks' );
if ( empty( $blocks ) ) {
	return;
}
$heading = vss_field( 'financing', 'promo_blocks', 'heading' );
?>
<section class="tw-section">
	<div class="tw-container">
		<?php if ( $heading ) : ?>
			<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<div class="tw-grid tw-grid--card">
			<?php foreach ( $blocks as $block ) : ?>
				<div class="tw-promo-block">
					<?php if ( $block['image'] ) : ?>
						<img class="tw-promo-block-img" src="<?php echo esc_url( $block['image'] ); ?>" alt="<?php echo esc_attr( $block['heading'] ); ?>" />
					<?php endif; ?>
					<div class="tw-promo-block-body">
						<?php if ( $block['heading'] ) : ?><div class="tw-promo-block-heading"><?php echo esc_html( $block['heading'] ); ?></div><?php endif; ?>
						<?php if ( $block['body'] ) : ?><p class="tw-promo-block-desc"><?php echo esc_html( $block['body'] ); ?></p><?php endif; ?>
						<?php if ( $block['button_text'] && $block['button_link'] ) : ?>
							<a
								href="<?php echo esc_url( $block['button_link'] ); ?>"
								class="tw-btn tw-btn-outline"
								<?php echo ! empty( $block['open_new_tab'] ) ? ' target="_blank" rel="noopener"' : ''; ?>
							><?php echo esc_html( $block['button_text'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
