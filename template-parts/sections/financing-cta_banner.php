<?php
/** Financing / CTA banner. */
?>
<div class="tw-container" style="padding:28px 32px 56px;">
	<div class="tw-cta-banner" style="background:var(--tw-bg-soft);">
		<div>
			<div class="tw-cta-banner-title"><?php echo esc_html( vss_field( 'financing', 'cta_banner', 'heading' ) ); ?></div>
			<p class="tw-cta-banner-desc"><?php echo esc_html( vss_field( 'financing', 'cta_banner', 'body' ) ); ?></p>
		</div>
		<a href="<?php echo esc_url( vss_field( 'financing', 'cta_banner', 'button_link' ) ); ?>" class="tw-btn tw-btn-primary"><?php echo esc_html( vss_field( 'financing', 'cta_banner', 'button_text' ) ); ?></a>
	</div>
</div>
