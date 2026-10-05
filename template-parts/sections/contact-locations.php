<?php
/** Contact / Store Locations -- add/remove/reorder blocks, one card per location. */
$locations = vss_repeater( 'contact', 'locations', 'items' );
if ( empty( $locations ) ) {
	return;
}
$heading = vss_field( 'contact', 'locations', 'heading' );
?>
<section class="tw-section">
	<div class="tw-container">
		<?php if ( $heading ) : ?>
			<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( $heading ); ?></h2>
		<?php endif; ?>
		<div class="tw-grid" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
			<?php foreach ( $locations as $location ) : ?>
				<div class="tw-card tw-location-card">
					<div class="tw-location-name">
						<span class="tw-location-dot"></span>
						<?php echo esc_html( $location['name'] ); ?>
					</div>
					<?php $address_lines = vss_lines( $location['address'] ); ?>
					<?php if ( $address_lines ) : ?>
						<p class="tw-location-address"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $address_lines ) ) ); ?></p>
					<?php endif; ?>
					<?php if ( $location['phone'] ) : ?>
						<p class="tw-location-phone"><?php echo esc_html( $location['phone'] ); ?></p>
					<?php endif; ?>
					<?php if ( $location['hours'] ) : ?>
						<p class="tw-location-hours"><?php echo esc_html( $location['hours'] ); ?></p>
					<?php endif; ?>
					<?php if ( $location['directions_link'] ) : ?>
						<a href="<?php echo esc_url( $location['directions_link'] ); ?>" class="tw-location-directions" target="_blank" rel="noopener"><?php echo esc_html( vss_field( 'contact', 'locations', 'directions_text' ) ); ?></a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
