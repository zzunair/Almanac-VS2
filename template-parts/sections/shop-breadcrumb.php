<?php
/** Shop / breadcrumb. */
$items = array(
	array( 'label' => 'Home', 'url' => home_url( '/' ) ),
	array( 'label' => 'Shop', 'url' => wc_get_page_permalink( 'shop' ) ),
);
if ( is_product_taxonomy() ) {
	$items = array_merge( $items, vss_breadcrumb_term_items( get_queried_object() ) );
} elseif ( is_search() ) {
	$items[] = array( 'label' => 'Search results for "' . get_search_query( false ) . '"', 'url' => '' );
}
vss_breadcrumb( $items );
