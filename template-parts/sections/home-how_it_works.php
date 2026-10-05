<?php
/** Home / How reservations work -- steps are add/remove/reorder blocks. */
$steps = vss_repeater( 'home', 'how_it_works', 'steps' );
if ( empty( $steps ) ) {
	return;
}
?>
<section class="tw-section">
	<div class="tw-container">
		<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( vss_field( 'home', 'how_it_works', 'heading' ) ); ?></h2>
		<div class="tw-grid tw-grid--3">
			<?php foreach ( $steps as $i => $step ) : ?>
				<div class="tw-card">
					<div class="tw-step-num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></div>
					<div class="tw-callout-title"><?php echo esc_html( $step['title'] ); ?></div>
					<p class="tw-callout-desc"><?php echo esc_html( $step['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
