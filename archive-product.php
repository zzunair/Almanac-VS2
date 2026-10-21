<?php
/**
 * The Shop page (also used for product category / brand archives) --
 * matches Shop.dc.html. Sections are rendered in the order/visibility
 * configured under Customizer > Shop Page.
 *
 * Filtering is done here, once, and shared with the filters + grid
 * sections via $GLOBALS['vss_shop'] so both stay in sync without
 * running the product query twice.
 */
get_header();

$per_page = max( 1, (int) vss_field( 'shop', 'products_grid', 'per_page' ) );
$paged    = max( 1, (int) get_query_var( 'paged' ) ? get_query_var( 'paged' ) : ( isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 ) );

$args = array(
	'status'   => 'publish',
	'paginate' => true,
	'page'     => $paged,
	'limit'    => $per_page,
);

if ( is_product_taxonomy() ) {
	$term = get_queried_object();
	if ( $term ) {
		$args[ $term->taxonomy ] = array( $term->slug );
	}
}

if ( is_search() ) {
	// An empty "include" is ignored by WP_Query (returns everything), so
	// force a deliberately-unmatchable id instead of falling through to
	// "show the whole catalog" when nothing actually matched.
	$matched_ids     = function_exists( 'vss_product_search_ids' ) ? vss_product_search_ids( get_search_query() ) : array();
	$args['include'] = $matched_ids ? $matched_ids : array( 0 );
}

if ( ! empty( $_GET['product_cat'] ) ) {
	$args['category'] = array_map( 'sanitize_title', (array) wp_unslash( $_GET['product_cat'] ) );
}

if ( ! empty( $_GET['product_brand'] ) ) {
	$args['product_brand'] = array_map( 'sanitize_title', (array) wp_unslash( $_GET['product_brand'] ) );
}

$stock_filters = ! empty( $_GET['stock'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['stock'] ) ) : array();
$wants_instock    = in_array( 'instock', $stock_filters, true );
$wants_low        = in_array( 'low', $stock_filters, true );
$wants_outofstock = in_array( 'outofstock', $stock_filters, true );

if ( $stock_filters ) {
	$statuses = array();
	if ( $wants_instock || $wants_low ) {
		$statuses[] = 'instock';
	}
	if ( $wants_outofstock ) {
		$statuses[] = 'outofstock';
	}
	$args['stock_status'] = array_unique( $statuses );
}

$result    = wc_get_products( $args );
$products  = is_object( $result ) ? $result->products : $result;
$total     = is_object( $result ) ? $result->total : count( $products );
$max_pages = is_object( $result ) && $result->max_num_pages ? $result->max_num_pages : 1;

// "Low stock" isn't a real stock_status -- it's derived from comparing
// two meta values per product -- so once "Low Stock" is checked without
// plain "In Stock", narrow the instock results down to just the low ones.
if ( $wants_low && ! $wants_instock ) {
	$products = array_values(
		array_filter(
			$products,
			function ( $product ) {
				return 'is-lowstock' === vss_stock_badge( $product )['class'];
			}
		)
	);
}

$GLOBALS['vss_shop'] = array(
	'products'  => $products,
	'total'     => $total,
	'max_pages' => $max_pages,
	'paged'     => $paged,
);

// "filters" and "products_grid" sit side by side in a flex row, so (unlike
// other pages) they share one wrapping container rather than each being a
// standalone full-width section.
$order = vss_get_section_order( 'shop' );

if ( in_array( 'breadcrumb', $order, true ) ) {
	get_template_part( 'template-parts/sections/shop-breadcrumb' );
}

$row_sections = array_intersect( $order, array( 'filters', 'products_grid' ) );
if ( $row_sections ) {
	echo '<div class="tw-container tw-shop-layout">';
	foreach ( $row_sections as $section_id ) {
		get_template_part( 'template-parts/sections/shop-' . $section_id );
	}
	echo '</div>';
}

get_footer();
