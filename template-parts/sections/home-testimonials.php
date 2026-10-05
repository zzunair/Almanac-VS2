<?php
/** Home / Testimonials -- add/remove/reorder blocks. */
$testimonials = vss_repeater( 'home', 'testimonials', 'items' );
if ( empty( $testimonials ) ) {
	return;
}
?>
<section class="tw-section" style="background:var(--tw-bg-soft);">
	<div class="tw-container">
		<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( vss_field( 'home', 'testimonials', 'heading' ) ); ?></h2>
		<div class="tw-grid tw-grid--3">
			<?php foreach ( $testimonials as $testimonial ) : ?>
				<div class="tw-card tw-testimonial-card">
					<div class="tw-testimonial-stars">
						<?php
						$rating = max( 0, min( 5, (int) $testimonial['rating'] ) );
						for ( $i = 0; $i < 5; $i++ ) :
							?>
							<svg width="15" height="15" viewBox="0 0 24 24" fill="<?php echo $i < $rating ? 'var(--tw-accent)' : 'var(--tw-border)'; ?>"><path d="M12 2l3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z" /></svg>
						<?php endfor; ?>
					</div>
					<p class="tw-testimonial-quote">&ldquo;<?php echo esc_html( $testimonial['quote'] ); ?>&rdquo;</p>
					<div class="tw-testimonial-byline">
						<strong><?php echo esc_html( $testimonial['name'] ); ?></strong>
						<?php if ( $testimonial['location'] ) : ?>
							&middot; <?php echo esc_html( $testimonial['location'] ); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
