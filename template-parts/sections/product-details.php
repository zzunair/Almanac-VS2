<?php
/** Product / full description + specifications. */
global $product;
if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}

$brand_terms = wc_get_product_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
$cat_terms   = vss_product_categories( $product->get_id() );

$specs = array();
if ( $brand_terms ) {
	$specs['Brand'] = $brand_terms[0];
}
if ( $cat_terms ) {
	$specs['Category'] = $cat_terms[0];
}
foreach ( $product->get_attributes() as $attribute ) {
	if ( is_a( $attribute, 'WC_Product_Attribute' ) && ! $attribute->is_taxonomy() ) {
		$specs[ wc_attribute_label( $attribute->get_name() ) ] = implode( ', ', $attribute->get_options() );
	}
}
$weight = $product->get_weight();
if ( $weight ) {
	$specs[ 'Weight' ] = $weight . ' ' . get_option( 'woocommerce_weight_unit' );
}
$has_description = (bool) $product->get_description();

if ( ! $has_description && ! $specs ) {
	return;
}
?>
<section class="tw-container tw-details<?php echo ( ! $has_description || ! $specs ) ? ' tw-details--single' : ''; ?>">
	<?php if ( $has_description ) : ?>
		<div class="tw-details-desc">
			<h2><?php echo esc_html( vss_field( 'product', 'details', 'description_heading' ) ); ?></h2>
			<p><?php echo wp_kses_post( $product->get_description() ); ?></p>
		</div>
	<?php endif; ?>
	<?php if ( $specs ) : ?>
		<div class="tw-details-specs">
			<h2><?php echo esc_html( vss_field( 'product', 'details', 'specs_heading' ) ); ?></h2>
			<div>
				<?php foreach ( $specs as $label => $value ) : ?>
					<div class="tw-spec-row"><span><?php echo esc_html( $label ); ?></span><span><?php echo esc_html( $value ); ?></span></div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</section>
