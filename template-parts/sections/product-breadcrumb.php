<?php
/** Product / breadcrumb. */
global $product;
if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}
$cats  = vss_product_categories( $product->get_id() );
$crumb = array( 'Home', 'Shop' );
if ( $cats ) {
	$crumb[] = $cats[0];
}
$crumb[] = $product->get_name();
?>
<div class="tw-container tw-breadcrumb"><?php echo esc_html( implode( ' / ', $crumb ) ); ?></div>
