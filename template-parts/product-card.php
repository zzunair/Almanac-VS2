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
$selling    = function_exists( 'vss_ecommerce_enabled' ) && vss_ecommerce_enabled();
?>
<div class="tw-product-card-wrap<?php echo $selling ? ' has-cart' : ''; ?>">
	<a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>" class="tw-product-card">
		<?php if ( $image_id ) : ?>
			<div class="tw-product-card-img"><?php echo wp_get_attachment_image( $image_id, 'medium', false, vss_image_attrs( $image_id, $product->get_name(), array( 'style' => 'max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;' ) ) ); ?></div>
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
	<?php if ( $selling ) : ?>
		<?php
		$GLOBALS['product'] = $product;
		$cart_classes       = array( 'tw-card-cart', 'button', 'product_type_' . $product->get_type() );
		if ( $product->is_purchasable() && $product->is_in_stock() ) {
			$cart_classes[] = 'add_to_cart_button';
		}
		if ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			$cart_classes[] = 'ajax_add_to_cart';
		}
		?>
		<a
			href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
			class="<?php echo esc_attr( implode( ' ', $cart_classes ) ); ?>"
			data-quantity="1"
			data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
			data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
			rel="nofollow"
		>
			<svg class="tw-card-cart-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
				<path d="M6 6h15l-1.5 9h-12z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
				<path d="M6 6L5 3H2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
				<circle cx="9" cy="20" r="1.4" fill="currentColor"/>
				<circle cx="18" cy="20" r="1.4" fill="currentColor"/>
			</svg>
			<svg class="tw-card-cart-check" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
				<path d="M5 12.5l4.2 4.2L19 7" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<span class="screen-reader-text"><?php echo esc_html( $product->add_to_cart_text() ); ?></span>
		</a>
	<?php endif; ?>
	<?php if ( $compare_on ) : ?>
		<label class="tw-compare-toggle">
			<input
				type="checkbox"
				class="tw-compare-checkbox"
				<?php echo vss_compare_checkbox_attrs( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute in the helper. ?>
			/>
			<span><?php echo esc_html( get_theme_mod( 'vss_compare_checkbox_label', 'Compare' ) ); ?></span>
		</label>
	<?php endif; ?>
</div>
