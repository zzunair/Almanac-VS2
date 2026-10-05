<?php
/** About / feature cards -- add/remove/reorder blocks. */
$cards = vss_repeater( 'about', 'features', 'cards' );
if ( empty( $cards ) ) {
	return;
}
?>
<div class="tw-container" style="max-width:820px;">
	<div class="tw-grid tw-grid--3" style="margin-bottom:40px;">
		<?php foreach ( $cards as $card ) : ?>
			<div class="tw-card" style="padding:22px;">
				<div class="tw-callout-title" style="font-size:0.875rem;"><?php echo esc_html( $card['title'] ); ?></div>
				<p class="tw-callout-desc" style="font-size:0.8125rem;"><?php echo esc_html( $card['desc'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</div>
