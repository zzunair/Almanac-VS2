<?php
/** Shop / products grid + pagination. Reads the shared query built in archive-product.php. */
$shop     = isset( $GLOBALS['vss_shop'] ) ? $GLOBALS['vss_shop'] : array( 'products' => array(), 'total' => 0, 'max_pages' => 1, 'paged' => 1 );
$products = $shop['products'];

if ( is_product_taxonomy() ) {
	$grid_heading = get_queried_object()->name;
} elseif ( is_search() ) {
	$grid_heading = 'Search results for "' . get_search_query( false ) . '"';
} else {
	$grid_heading = vss_field( 'shop', 'products_grid', 'heading' );
}
?>
<div class="tw-shop-main">
	<div class="tw-section-head">
		<h1 class="tw-heading" style="font-size:1.625rem;font-weight:800;"><?php echo esc_html( $grid_heading ); ?></h1>
		<span class="tw-shop-count"><?php echo esc_html( number_format_i18n( $shop['total'] ) . vss_field( 'shop', 'products_grid', 'count_suffix' ) ); ?></span>
	</div>

	<?php if ( $products ) : ?>
		<div class="tw-grid tw-grid--product">
			<?php foreach ( $products as $product ) : ?>
				<?php include get_theme_file_path( 'template-parts/product-card.php' ); ?>
			<?php endforeach; ?>
		</div>
	<?php elseif ( is_search() ) : ?>
		<p><?php echo esc_html( str_replace( '{term}', get_search_query( false ), vss_field( 'shop', 'products_grid', 'empty_search_text' ) ) ); ?></p>
	<?php else : ?>
		<p><?php echo esc_html( vss_field( 'shop', 'products_grid', 'empty_filter_text' ) ); ?></p>
	<?php endif; ?>

	<?php if ( $shop['max_pages'] > 1 ) : ?>
		<div class="tw-pagination">
			<?php
			echo paginate_links(
				array(
					'base'      => add_query_arg( 'paged', '%#%' ),
					'format'    => '',
					'current'   => $shop['paged'],
					'total'     => $shop['max_pages'],
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>
