<?php
/** Contact / hero intro. */
?>
<section class="tw-section--soft">
	<div class="tw-container tw-hero tw-hero--page" style="display:block;max-width:1000px;">
		<div class="tw-eyebrow"><?php echo esc_html( vss_field( 'contact', 'intro', 'eyebrow' ) ); ?></div>
		<h1><?php echo esc_html( vss_field( 'contact', 'intro', 'heading' ) ); ?></h1>
		<p style="margin-bottom:0;"><?php echo esc_html( vss_field( 'contact', 'intro', 'body' ) ); ?></p>
	</div>
</section>
