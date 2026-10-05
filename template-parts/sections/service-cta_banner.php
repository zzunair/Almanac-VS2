<?php
/** Service / CTA banner. */
?>
<div class="tw-container" style="padding-bottom:56px;">
	<div class="tw-cta-banner tw-card">
		<div>
			<div class="tw-cta-banner-title"><?php echo esc_html( vss_field( 'service', 'cta_banner', 'heading' ) ); ?></div>
			<p class="tw-cta-banner-desc"><?php echo esc_html( vss_field( 'service', 'cta_banner', 'body' ) ); ?></p>
		</div>
		<a href="<?php echo esc_url( vss_field( 'service', 'cta_banner', 'button_link' ) ); ?>" class="tw-btn tw-btn-primary"><?php echo esc_html( vss_field( 'service', 'cta_banner', 'button_text' ) ); ?></a>
	</div>
</div>
