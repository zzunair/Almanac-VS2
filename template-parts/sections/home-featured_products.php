<?php
/** Home / Featured products section. */
if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$count = max( 1, (int) vss_field( 'home', 'featured_products', 'count' ) );

$products = wc_get_products(
	array(
		'status'  => 'publish',
		'limit'   => $count,
		'orderby' => 'date',
		'order'   => 'DESC',
	)
);

if ( empty( $products ) ) {
	vss_render_empty_state(
		vss_field( 'home', 'featured_products', 'heading' ),
		'No products yet. Add products in <strong>Products &rsaquo; Add New</strong> and the most recent ones will be featured here automatically.'
	);
	return;
}
?>
<section class="tw-section">
	<div class="tw-container">
		<div class="tw-section-head">
			<h2 class="tw-heading"><?php echo esc_html( vss_field( 'home', 'featured_products', 'heading' ) ); ?></h2>
			<a href="<?php echo esc_url( vss_field( 'home', 'featured_products', 'view_all_link' ) ); ?>" class="tw-view-all"><?php echo esc_html( vss_field( 'home', 'featured_products', 'view_all_text' ) ); ?></a>
		</div>
		<div class="tw-grid tw-grid--product">
			<?php foreach ( $products as $product ) : ?>
				<?php include get_theme_file_path( 'template-parts/product-card.php' ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
