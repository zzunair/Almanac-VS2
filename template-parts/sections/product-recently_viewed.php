<?php
/** Product / Recently Viewed -- reads the vss_recently_viewed cookie set in inc/woocommerce.php. */
global $product;
$current_id = $product instanceof WC_Product ? $product->get_id() : get_the_ID();

$count    = max( 1, (int) vss_field( 'product', 'recently_viewed', 'count' ) );
$products = vss_get_recently_viewed_products( $current_id, $count );
if ( empty( $products ) ) {
	return;
}
?>
<section class="tw-section">
	<div class="tw-container">
		<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( vss_field( 'product', 'recently_viewed', 'heading' ) ); ?></h2>
		<div class="tw-grid tw-grid--product">
			<?php foreach ( $products as $product ) : ?>
				<?php include get_theme_file_path( 'template-parts/product-card.php' ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
