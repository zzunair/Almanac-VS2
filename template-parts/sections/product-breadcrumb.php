<?php
/** Product / breadcrumb. */
global $product;
if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}
$items = array(
	array( 'label' => 'Home', 'url' => home_url( '/' ) ),
	array( 'label' => 'Shop', 'url' => wc_get_page_permalink( 'shop' ) ),
);
$cats = vss_product_categories( $product->get_id(), 'terms' );
if ( $cats ) {
	$items = array_merge( $items, vss_breadcrumb_term_items( $cats[0] ) );
}
$items[] = array( 'label' => $product->get_name(), 'url' => get_permalink( $product->get_id() ) );
vss_breadcrumb( $items );
