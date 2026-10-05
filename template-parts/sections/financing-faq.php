<?php
/** Financing / FAQ accordion -- uses native <details>/<summary>, no JS needed. */
$faqs = vss_repeater( 'financing', 'faq', 'items' );
if ( empty( $faqs ) ) {
	return;
}
?>
<section class="tw-section">
	<div class="tw-container" style="max-width:820px;">
		<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( vss_field( 'financing', 'faq', 'heading' ) ); ?></h2>
		<div class="tw-faq-list">
			<?php foreach ( $faqs as $i => $faq ) : ?>
				<details class="tw-faq-item" <?php echo 0 === $i ? 'open' : ''; ?>>
					<summary class="tw-faq-question"><?php echo esc_html( $faq['question'] ); ?></summary>
					<p class="tw-faq-answer"><?php echo esc_html( $faq['answer'] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div>
</section>
