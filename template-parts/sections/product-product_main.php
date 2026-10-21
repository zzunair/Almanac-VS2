<?php
/** Product / gallery, info & reserve form -- matches Product.dc.html. */
global $product;
if ( ! $product instanceof WC_Product ) {
	$product = wc_get_product( get_the_ID() );
}

if ( function_exists( 'WC' ) && isset( WC()->structured_data ) ) {
	WC()->structured_data->generate_product_data( $product );
}

$brand_terms = wc_get_product_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
$cat_terms   = vss_product_categories( $product->get_id() );
$badge       = vss_stock_badge( $product );
$qty         = $product->managing_stock() ? $product->get_stock_quantity() : null;

$gallery_ids   = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();

// Thumbnails include the main photo itself as the first one, so clicking
// back to it after viewing a gallery shot is possible too -- not just
// gallery images that aren't already shown large.
$thumb_ids = array_values( array_unique( array_filter( array_merge( array( $main_image_id ), $gallery_ids ) ) ) );
?>
<section class="tw-container tw-product-layout">
	<div class="tw-gallery">
		<?php if ( $main_image_id ) : ?>
			<div class="tw-gallery-main" id="tw-gallery-main"><?php echo wp_get_attachment_image( $main_image_id, 'large', false, vss_image_attrs( $main_image_id, $product->get_name(), array( 'style' => 'width:100%;height:100%;object-fit:cover;border-radius:20px;', 'loading' => 'eager', 'fetchpriority' => 'high' ) ) ); ?></div>
		<?php else : ?>
			<div class="tw-gallery-main tw-placeholder"><?php echo esc_html( vss_field( 'product', 'product_main', 'main_photo_placeholder' ) ); ?></div>
		<?php endif; ?>
		<?php if ( count( $thumb_ids ) > 1 ) : ?>
			<div class="tw-gallery-thumbs" data-tw-gallery>
				<?php foreach ( array_slice( $thumb_ids, 0, 4 ) as $i => $tid ) : ?>
					<button type="button" class="tw-gallery-thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-full="<?php echo esc_url( wp_get_attachment_image_url( $tid, 'large' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( 'Show photo %d', $i + 1 ) ); ?>">
						<?php echo wp_get_attachment_image( $tid, 'thumbnail', false, vss_image_attrs( $tid, sprintf( '%s — photo %d', $product->get_name(), $i + 1 ), array( 'style' => 'width:100%;height:100%;object-fit:cover;border-radius:12px;' ) ) ); ?>
					</button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="tw-product-info">
		<div class="tw-product-pills" style="justify-content:space-between;">
			<div style="display:flex;gap:8px;">
				<?php if ( $brand_terms ) : ?><span class="tw-pill"><?php echo esc_html( $brand_terms[0] ); ?></span><?php endif; ?>
				<?php if ( $cat_terms ) : ?><span class="tw-pill"><?php echo esc_html( $cat_terms[0] ); ?></span><?php endif; ?>
			</div>
			<?php if ( get_theme_mod( 'vss_compare_enabled', true ) ) : ?>
				<label class="tw-compare-toggle" style="position:static;background:none;border:0;padding:0;">
					<input
						type="checkbox"
						class="tw-compare-checkbox"
						<?php echo vss_compare_checkbox_attrs( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped per attribute in the helper. ?>
					/>
					<span><?php echo esc_html( get_theme_mod( 'vss_compare_checkbox_label', 'Compare' ) ); ?></span>
				</label>
			<?php endif; ?>
		</div>

		<h1 class="tw-product-title"><?php echo esc_html( $product->get_name() ); ?></h1>
		<div class="tw-product-price"><?php echo wp_kses_post( wc_price( $product->get_price() ) ); ?></div>

		<div class="tw-product-stock-row">
			<span class="tw-badge tw-badge--lg <?php echo esc_attr( $badge['class'] ); ?>"><?php echo esc_html( $badge['text'] ); ?></span>
			<?php if ( null !== $qty ) : ?>
				<span class="tw-product-stock-note"><?php echo esc_html( $qty . vss_field( 'product', 'product_main', 'stock_location_suffix' ) ); ?></span>
			<?php endif; ?>
		</div>

		<?php if ( $product->get_short_description() ) : ?>
			<p class="tw-product-summary"><?php echo wp_kses_post( $product->get_short_description() ); ?></p>
		<?php endif; ?>

		<?php
		$selling = function_exists( 'vss_ecommerce_enabled' ) && vss_ecommerce_enabled();
		if ( $selling && function_exists( 'woocommerce_template_single_add_to_cart' ) ) :
			?>
			<div class="tw-product-purchase">
				<?php woocommerce_template_single_add_to_cart(); ?>
			</div>
			<button type="button" class="tw-request-open" aria-expanded="false" aria-controls="tw-request-modal">Request information</button>
		<?php endif; ?>

		<?php if ( $selling ) : ?>
		<div class="tw-request-overlay" id="tw-request-modal" hidden>
			<div class="tw-request-dialog" role="dialog" aria-modal="true" aria-labelledby="tw-request-title">
		<?php endif; ?>
		<div class="tw-reserve-box">
			<?php if ( $selling ) : ?>
				<button type="button" class="tw-request-close" aria-label="Close">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
			<?php endif; ?>
			<?php if ( $product->is_in_stock() ) : ?>
				<div class="tw-reserve-form">
					<div class="tw-reserve-heading"<?php echo $selling ? ' id="tw-request-title"' : ''; ?>><?php echo esc_html( vss_field( 'product', 'product_main', 'reserve_heading' ) ); ?></div>
					<p class="tw-reserve-intro"><?php echo esc_html( vss_field( 'product', 'product_main', 'reserve_intro' ) ); ?></p>
					<div id="tw-crm-popup" class="tw-reserve-lead-form" data-tw-crm-inline data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
						<div id="leads-form-div"></div>
					</div>
				</div>
			<?php else : ?>
				<div class="tw-reserve-heading"<?php echo $selling ? ' id="tw-request-title"' : ''; ?>><?php echo esc_html( vss_field( 'product', 'product_main', 'outofstock_heading' ) ); ?></div>
				<p class="tw-reserve-intro"><?php echo esc_html( vss_field( 'product', 'product_main', 'outofstock_text' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $selling ) : ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</section>
