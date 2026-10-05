<?php
/** Service / cards -- add/remove/reorder blocks. */
$cards = vss_repeater( 'service', 'service_cards', 'cards' );
if ( empty( $cards ) ) {
	return;
}
?>
<section class="tw-section" style="padding-bottom:28px;">
	<div class="tw-container tw-grid tw-grid--card">
		<?php foreach ( $cards as $card ) : ?>
			<div class="tw-card">
				<div class="tw-callout-title" style="margin-bottom:8px;font-size:0.9375rem;"><?php echo esc_html( $card['title'] ); ?></div>
				<p class="tw-callout-desc" style="font-size:0.8125rem;"><?php echo esc_html( $card['desc'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>
