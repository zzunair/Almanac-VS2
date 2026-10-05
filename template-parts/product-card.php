<?php
/**
 * Single product card -- matches ProductCard.dc.html. Expects $product
 * (a WC_Product) to be set by the including template.
 */
if ( empty( $product ) || ! $product instanceof WC_Product ) {
	return;
}

$badge = vss_stock_badge( $product );
$brand_terms = wc_get_product_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
$brand = $brand_terms ? $brand_terms[0] : '';
$cat_terms = vss_product_categories( $product->get_id() );
$image_id = $product->get_image_id();
$image_label = $cat_terms ? rtrim( $cat_terms[0], 's' ) . ' Photo' : 'Product Photo';
$compare_on = get_theme_mod( 'vss_compare_enabled', true );
?>
<div class="tw-product-card-wrap">
	<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="tw-product-card">
		<?php if ( $image_id ) : ?>
			<div class="tw-product-card-img"><?php echo wp_get_attachment_image( $image_id, 'medium', false, array( 'style' => 'max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;' ) ); ?></div>
		<?php else : ?>
			<div class="tw-product-card-img tw-placeholder"><?php echo esc_html( $image_label ); ?></div>
		<?php endif; ?>
		<div class="tw-product-card-body">
			<span class="tw-product-card-brand"><?php echo esc_html( $brand ); ?></span>
			<span class="tw-product-card-name"><?php echo esc_html( $product->get_name() ); ?></span>
			<span class="tw-product-card-price"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></span>
			<span class="tw-badge <?php echo esc_attr( $badge['class'] ); ?>"><?php echo esc_html( $badge['text'] ); ?></span>
		</div>
	</a>
	<?php if ( $compare_on ) : ?>
		<label class="tw-compare-toggle">
			<input
				type="checkbox"
				class="tw-compare-checkbox"
				data-id="<?php echo esc_attr( $product->get_id() ); ?>"
				data-name="<?php echo esc_attr( $product->get_name() ); ?>"
				data-brand="<?php echo esc_attr( $brand ); ?>"
				data-price="<?php echo esc_attr( wp_strip_all_tags( wc_price( $product->get_price() ) ) ); ?>"
				data-image="<?php echo esc_attr( $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '' ); ?>"
				data-url="<?php echo esc_attr( get_permalink( $product->get_id() ) ); ?>"
				data-badge="<?php echo esc_attr( $badge['text'] ); ?>"
				data-badge-class="<?php echo esc_attr( $badge['class'] ); ?>"
			/>
			<span><?php echo esc_html( get_theme_mod( 'vss_compare_checkbox_label', 'Compare' ) ); ?></span>
		</label>
	<?php endif; ?>
</div>
