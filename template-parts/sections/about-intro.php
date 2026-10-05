<?php
/** About / hero intro. */
?>
<section class="tw-section--soft">
	<div class="tw-container tw-hero tw-hero--page" style="display:block;max-width:820px;">
		<div class="tw-eyebrow"><?php echo esc_html( vss_field( 'about', 'intro', 'eyebrow' ) ); ?></div>
		<h1><?php echo esc_html( vss_field( 'about', 'intro', 'heading' ) ); ?></h1>
		<p><?php echo esc_html( vss_field( 'about', 'intro', 'body1' ) ); ?></p>
		<p style="margin-bottom:0;"><?php echo esc_html( vss_field( 'about', 'intro', 'body2' ) ); ?></p>
	</div>
</section>
