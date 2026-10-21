<?php
/**
 * Product search uses the shop layout. Everything else uses the fallback.
 */
if ( isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] && function_exists( 'wc_get_products' ) ) {
	require get_template_directory() . '/archive-product.php';
	return;
}
require get_template_directory() . '/index.php';
