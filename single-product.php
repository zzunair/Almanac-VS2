<?php
/**
 * The single product page -- matches Product.dc.html. Sections are
 * rendered in the order/visibility configured under Customizer > Product Page.
 */
get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		global $product;
		$product = wc_get_product( get_the_ID() );
		vss_render_sections( 'product' );
	}
}

get_footer();
