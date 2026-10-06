<?php
/** Home / Hero slideshow -- one slide visible at a time; add more via Customizer > Home Page > Hero Slideshow. */
$slides = vss_repeater( 'home', 'hero', 'slides' );
if ( empty( $slides ) ) {
	return;
}
$trust_text = vss_field( 'home', 'hero', 'trust_text' );
?>
<section class="tw-hero-dark" data-tw-slideshow>
	<div class="tw-hero-dark-row">
		<?php foreach ( $slides as $i => $slide ) : ?>
			<?php
			$layout     = ! empty( $slide['layout'] ) ? $slide['layout'] : 'split';
			$text_align = ! empty( $slide['text_align'] ) ? $slide['text_align'] : 'left';
			$heading_tag = 0 === $i ? 'h1' : 'h2';
			?>
			<?php if ( 'banner' === $layout ) : ?>
				<div
					class="tw-slide tw-hero-banner tw-hero-banner--align-<?php echo esc_attr( $text_align ); ?><?php echo 0 === $i ? ' is-active' : ''; ?>"
					<?php if ( $slide['image'] ) : ?>style="background-image:url('<?php echo esc_url( $slide['image'] ); ?>');"<?php endif; ?>
				>
					<div class="tw-hero-banner-overlay"></div>
					<div class="tw-container">
						<div class="tw-hero-banner-content">
							<?php if ( $slide['eyebrow'] ) : ?><div class="tw-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></div><?php endif; ?>
							<<?php echo $heading_tag; ?> class="tw-hero-heading"><?php echo esc_html( $slide['heading'] ); ?></<?php echo $heading_tag; ?>>
							<?php if ( $slide['body'] ) : ?><p class="tw-hero-body"><?php echo esc_html( $slide['body'] ); ?></p><?php endif; ?>
							<div class="tw-hero-buttons">
								<?php if ( $slide['button_text'] ) : ?>
									<a href="<?php echo esc_url( $slide['button_link'] ); ?>" class="tw-btn tw-btn-primary"><?php echo esc_html( $slide['button_text'] ); ?></a>
								<?php endif; ?>
								<?php if ( $slide['button2_text'] ) : ?>
									<a href="<?php echo esc_url( $slide['button2_link'] ); ?>" class="tw-btn tw-hero-btn-outline"><?php echo esc_html( $slide['button2_text'] ); ?></a>
								<?php endif; ?>
							</div>
							<?php if ( $trust_text ) : ?>
								<div class="tw-hero-trust">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--tw-accent)" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
									<?php echo esc_html( $trust_text ); ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php else : ?>
				<div class="tw-container">
					<div class="tw-slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
						<div class="tw-hero-copy">
							<?php if ( $slide['eyebrow'] ) : ?><div class="tw-eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></div><?php endif; ?>
							<<?php echo $heading_tag; ?> class="tw-hero-heading"><?php echo esc_html( $slide['heading'] ); ?></<?php echo $heading_tag; ?>>
							<?php if ( $slide['body'] ) : ?><p class="tw-hero-body"><?php echo esc_html( $slide['body'] ); ?></p><?php endif; ?>
							<div class="tw-hero-buttons">
								<?php if ( $slide['button_text'] ) : ?>
									<a href="<?php echo esc_url( $slide['button_link'] ); ?>" class="tw-btn tw-btn-primary"><?php echo esc_html( $slide['button_text'] ); ?></a>
								<?php endif; ?>
								<?php if ( $slide['button2_text'] ) : ?>
									<a href="<?php echo esc_url( $slide['button2_link'] ); ?>" class="tw-btn tw-hero-btn-outline"><?php echo esc_html( $slide['button2_text'] ); ?></a>
								<?php endif; ?>
							</div>
							<?php if ( $trust_text ) : ?>
								<div class="tw-hero-trust">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--tw-accent)" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
									<?php echo esc_html( $trust_text ); ?>
								</div>
							<?php endif; ?>
						</div>

						<div class="tw-hero-card">
							<?php if ( $slide['image'] ) : ?>
								<img src="<?php echo esc_url( $slide['image'] ); ?>" alt="<?php echo esc_attr( $slide['heading'] ); ?>" class="tw-hero-card-img"<?php echo 0 === $i ? ' fetchpriority="high"' : ' loading="lazy"'; ?> />
							<?php else : ?>
								<div class="tw-hero-card-pattern"></div>
								<svg width="140" height="140" viewBox="0 0 24 24" fill="none" stroke="var(--tw-accent)" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" class="tw-hero-card-icon"><circle cx="12" cy="12" r="9"/><path d="M8 13.5l2.5-5 2 3.5 1.5-2 2 3"/></svg>
							<?php endif; ?>
							<?php if ( $slide['highlight_text'] ) : ?>
								<div class="tw-hero-highlight">
									<span><?php echo esc_html( $slide['highlight_text'] ); ?></span>
									<?php if ( $slide['highlight_badge'] ) : ?>
										<span class="tw-badge is-instock"><?php echo esc_html( $slide['highlight_badge'] ); ?></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $slides ) > 1 ) : ?>
		<div class="tw-slideshow-nav tw-hero-nav">
			<button type="button" class="tw-slideshow-arrow tw-slideshow-prev" aria-label="Previous slide">&#8249;</button>
			<div class="tw-slideshow-dots">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<button type="button" class="tw-slideshow-dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>" aria-label="Go to slide <?php echo esc_attr( $i + 1 ); ?>"></button>
				<?php endforeach; ?>
			</div>
			<button type="button" class="tw-slideshow-arrow tw-slideshow-next" aria-label="Next slide">&#8250;</button>
		</div>
	<?php endif; ?>
</section>
