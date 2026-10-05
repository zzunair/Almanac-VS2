<?php
/** Shop / filters sidebar -- real filtering via GET params, auto-submits on change. */
$selected_cats   = ! empty( $_GET['product_cat'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['product_cat'] ) ) : array();
$selected_brands = ! empty( $_GET['product_brand'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET['product_brand'] ) ) : array();
$selected_stock  = ! empty( $_GET['stock'] ) ? array_map( 'sanitize_text_field', (array) wp_unslash( $_GET['stock'] ) ) : array();

// The taxonomy's cached term_taxonomy.count is only kept accurate for
// product_cat/product_tag by WooCommerce's own recount hook -- a custom
// taxonomy like product_brand just gets default WP counting, which drifts
// stale (e.g. after a bulk import that bypasses wp_set_object_terms()).
// So don't trust hide_empty or ->count for either list -- compute a real,
// live count per term instead, and only list terms that actually have one.
$vss_term_query_key = array(
	'product_cat'   => 'category',
	'product_brand' => 'product_brand',
);
$vss_live_term_count = function ( $taxonomy, $slug ) use ( $vss_term_query_key ) {
	$result = wc_get_products(
		array(
			'status'                  => 'publish',
			$vss_term_query_key[ $taxonomy ] => array( $slug ),
			'paginate'                => true,
			'limit'                   => 1,
		)
	);
	return is_object( $result ) ? (int) $result->total : 0;
};

$cat_terms   = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => array( get_option( 'default_product_cat' ) ) ) );
$brand_terms = get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) );

if ( ! is_wp_error( $cat_terms ) ) {
	foreach ( $cat_terms as $term ) {
		$term->tw_count = $vss_live_term_count( 'product_cat', $term->slug );
	}
	$cat_terms = array_values( array_filter( $cat_terms, function ( $term ) { return $term->tw_count > 0; } ) );
}
if ( ! is_wp_error( $brand_terms ) ) {
	foreach ( $brand_terms as $term ) {
		$term->tw_count = $vss_live_term_count( 'product_brand', $term->slug );
	}
	$brand_terms = array_values( array_filter( $brand_terms, function ( $term ) { return $term->tw_count > 0; } ) );
}

$instock_result    = wc_get_products( array( 'status' => 'publish', 'stock_status' => 'instock', 'paginate' => true, 'limit' => 1 ) );
$outofstock_result = wc_get_products( array( 'status' => 'publish', 'stock_status' => 'outofstock', 'paginate' => true, 'limit' => 1 ) );
$low_count         = 0;
foreach ( wc_get_products( array( 'status' => 'publish', 'stock_status' => 'instock', 'limit' => -1 ) ) as $product ) {
	if ( 'is-lowstock' === vss_stock_badge( $product )['class'] ) {
		$low_count++;
	}
}
$active_count = count( $selected_cats ) + count( $selected_brands ) + count( $selected_stock );

// Long lists (100+ terms) get their own scroll area plus a live search box
// instead of pushing the whole sidebar height out -- the threshold is a
// judgment call, not a WooCommerce limit.
$vss_long_list_threshold = 8;
?>
<aside class="tw-filters">
	<button type="button" class="tw-filters-toggle" aria-expanded="<?php echo $active_count ? 'true' : 'false'; ?>" aria-controls="tw-filters-body">
		<span><?php echo esc_html( vss_field( 'shop', 'filters', 'heading' ) ); ?><?php echo $active_count ? ' &middot; ' . (int) $active_count : ''; ?></span>
		<span class="tw-filters-toggle-icon" aria-hidden="true">&#9662;</span>
	</button>
	<div class="tw-filters-body<?php echo $active_count ? ' is-open' : ''; ?>" id="tw-filters-body">
	<div class="tw-filters-title"><?php echo esc_html( vss_field( 'shop', 'filters', 'heading' ) ); ?></div>
	<form method="get" class="tw-filters-form" action="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>">

		<?php if ( vss_field( 'shop', 'filters', 'show_availability' ) ) : ?>
			<details class="tw-filter-group" open>
				<summary class="tw-filter-group-label"><?php echo esc_html( vss_field( 'shop', 'filters', 'availability_label' ) ); ?></summary>
				<div class="tw-filter-group-body">
					<label><input type="checkbox" name="stock[]" value="instock" <?php checked( in_array( 'instock', $selected_stock, true ) ); ?> /> <?php echo esc_html( vss_field( 'shop', 'filters', 'instock_label' ) ); ?> <span class="tw-filter-count">(<?php echo (int) $instock_result->total; ?>)</span></label>
					<label><input type="checkbox" name="stock[]" value="low" <?php checked( in_array( 'low', $selected_stock, true ) ); ?> /> <?php echo esc_html( vss_field( 'shop', 'filters', 'lowstock_label' ) ); ?> <span class="tw-filter-count">(<?php echo (int) $low_count; ?>)</span></label>
					<label><input type="checkbox" name="stock[]" value="outofstock" <?php checked( in_array( 'outofstock', $selected_stock, true ) ); ?> /> <?php echo esc_html( vss_field( 'shop', 'filters', 'outofstock_label' ) ); ?> <span class="tw-filter-count">(<?php echo (int) $outofstock_result->total; ?>)</span></label>
				</div>
			</details>
		<?php endif; ?>

		<?php if ( vss_field( 'shop', 'filters', 'show_brand' ) && ! is_wp_error( $brand_terms ) && $brand_terms ) : ?>
			<details class="tw-filter-group" open>
				<summary class="tw-filter-group-label"><?php echo esc_html( vss_field( 'shop', 'filters', 'brand_label' ) ); ?></summary>
				<div class="tw-filter-group-body">
					<?php if ( count( $brand_terms ) > $vss_long_list_threshold ) : ?>
						<input type="text" class="tw-filter-search" placeholder="Search brands&hellip;" aria-label="Search brands" />
					<?php endif; ?>
					<div class="tw-filter-scroll<?php echo count( $brand_terms ) > $vss_long_list_threshold ? ' is-scrollable' : ''; ?>">
						<?php foreach ( $brand_terms as $term ) : ?>
							<label>
								<input type="checkbox" name="product_brand[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $selected_brands, true ) ); ?> />
								<?php echo esc_html( $term->name ); ?> <span class="tw-filter-count">(<?php echo (int) $term->tw_count; ?>)</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</details>
		<?php endif; ?>

		<?php if ( vss_field( 'shop', 'filters', 'show_category' ) && ! is_wp_error( $cat_terms ) && $cat_terms ) : ?>
			<details class="tw-filter-group" open>
				<summary class="tw-filter-group-label"><?php echo esc_html( vss_field( 'shop', 'filters', 'category_label' ) ); ?></summary>
				<div class="tw-filter-group-body">
					<?php if ( count( $cat_terms ) > $vss_long_list_threshold ) : ?>
						<input type="text" class="tw-filter-search" placeholder="Search categories&hellip;" aria-label="Search categories" />
					<?php endif; ?>
					<div class="tw-filter-scroll<?php echo count( $cat_terms ) > $vss_long_list_threshold ? ' is-scrollable' : ''; ?>">
						<?php foreach ( $cat_terms as $term ) : ?>
							<label>
								<input type="checkbox" name="product_cat[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( in_array( $term->slug, $selected_cats, true ) ); ?> />
								<?php echo esc_html( $term->name ); ?> <span class="tw-filter-count">(<?php echo (int) $term->tw_count; ?>)</span>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</details>
		<?php endif; ?>

		<noscript><button type="submit" class="tw-btn tw-btn-outline">Apply filters</button></noscript>
	</form>
	</div>
</aside>
