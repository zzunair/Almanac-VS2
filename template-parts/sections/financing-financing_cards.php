<?php
/** Financing / cards -- add/remove/reorder blocks -- + disclaimer. */
$cards = vss_repeater( 'financing', 'financing_cards', 'cards' );
if ( empty( $cards ) ) {
	return;
}
?>
<section class="tw-section" style="padding-bottom:0;">
	<div class="tw-container">
		<div class="tw-grid tw-grid--card">
			<?php foreach ( $cards as $card ) : ?>
				<div class="tw-card">
					<div class="tw-callout-title" style="margin-bottom:8px;font-size:0.9375rem;"><?php echo esc_html( $card['title'] ); ?></div>
					<p class="tw-callout-desc" style="font-size:0.8125rem;"><?php echo esc_html( $card['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
		<p style="font-family:inherit;font-size:0.75rem;color:var(--tw-text-faint);margin:20px 0 0;"><?php echo esc_html( vss_field( 'financing', 'financing_cards', 'disclaimer' ) ); ?></p>
	</div>
</section>
