<?php
/**
 * WooCommerce integration: theme support, the custom "Brand" taxonomy
 * (WooCommerce has no built-in brand taxonomy), and search config.
 *
 * archive-product.php and single-product.php in the theme root replace
 * WooCommerce's catalog markup. Cart and checkout stay off unless
 * Site-Wide Settings → Commerce is enabled.
 */

function vss_woocommerce_setup() {
	add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'vss_woocommerce_setup' );

function vss_register_brand_taxonomy() {
	register_taxonomy(
		'product_brand',
		'product',
		array(
			'label'             => 'Brands',
			'labels'            => array(
				'name'          => 'Brands',
				'singular_name' => 'Brand',
			),
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'public'            => true,
			'rewrite'           => array( 'slug' => 'brand' ),
		)
	);
}
add_action( 'init', 'vss_register_brand_taxonomy' );

/**
 * WooCommerce's product search widget/box normally posts to the shop page;
 * the header search in the design just needs to hit WP's own search with
 * post_type=product so results stay within the catalog.
 */
function vss_product_search_url() {
	return home_url( '/' );
}

/**
 * Site-Wide Settings → Commerce. Off by default so the site stays a showroom.
 */
function vss_ecommerce_enabled() {
	return (bool) get_theme_mod( 'vss_enable_ecommerce', false );
}

/**
 * Header cart link. Also the markup replaced by WooCommerce's cart
 * fragments, so the count updates after an Add to cart click.
 */
function vss_cart_link() {
	if ( ! vss_ecommerce_enabled() || ! function_exists( 'wc_get_cart_url' ) ) {
		return;
	}

	$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
	$label = $count ? sprintf( 'Cart, %d items', $count ) : 'Cart';
	?>
	<a class="tw-cart-link" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php echo esc_attr( $label ); ?>">
		<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
			<path d="M6 6h15l-1.5 9h-12z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
			<path d="M6 6L5 3H2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
			<circle cx="9" cy="20" r="1.4" fill="currentColor"/>
			<circle cx="18" cy="20" r="1.4" fill="currentColor"/>
		</svg>
		<span class="screen-reader-text">Cart</span>
		<?php if ( $count ) : ?>
			<span class="tw-cart-count"><?php echo (int) $count; ?></span>
		<?php endif; ?>
	</a>
	<?php
}

/**
 * Keep the header count in sync with ajax Add to cart.
 */
function vss_cart_fragments( $fragments ) {
	if ( ! vss_ecommerce_enabled() ) {
		return $fragments;
	}
	ob_start();
	vss_cart_link();
	$fragments['a.tw-cart-link'] = ob_get_clean();
	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'vss_cart_fragments' );

/**
 * Cart scripts stay off unless Commerce is enabled. With it on, ajax Add
 * to cart (including the homepage catalog) and the header count need them.
 */
function vss_dequeue_woocommerce_cart_scripts() {
	if ( vss_ecommerce_enabled() ) {
		wp_enqueue_script( 'wc-add-to-cart' );
		wp_enqueue_script( 'wc-cart-fragments' );
		return;
	}
	wp_dequeue_script( 'wc-cart-fragments' );
	wp_dequeue_script( 'wc-add-to-cart' );
}
add_action( 'wp_enqueue_scripts', 'vss_dequeue_woocommerce_cart_scripts', 20 );

/**
 * Cart and Checkout are only reachable while Commerce is on. My Account
 * is unused, so it always goes to the shop.
 */
function vss_redirect_unused_woocommerce_pages() {
	if ( is_admin() || ! function_exists( 'is_cart' ) ) {
		return;
	}
	$blocked = array( is_account_page() );
	if ( ! vss_ecommerce_enabled() ) {
		$blocked[] = is_cart();
		$blocked[] = is_checkout();
	}
	if ( in_array( true, $blocked, true ) ) {
		wp_safe_redirect( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );
		exit;
	}
}
add_action( 'template_redirect', 'vss_redirect_unused_woocommerce_pages' );

/**
 * Live search-as-you-type suggestions for the header search box (behind
 * Customizer > Header > "Show search suggestions while typing"). Returns
 * up to 6 matching products as JSON: name, price, thumbnail, stock badge,
 * and permalink -- everything the dropdown in assets/js/frontend.js needs
 * to render without a second request.
 */
function vss_ajax_search_suggest() {
	check_ajax_referer( 'vss_search', 'nonce' );

	if ( ! function_exists( 'wc_get_products' ) ) {
		wp_send_json( array() );
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	if ( '' === trim( $term ) ) {
		wp_send_json( array() );
	}

	$limit    = 6;
	$products = wc_get_products(
		array(
			'status'  => 'publish',
			's'       => $term,
			'limit'   => $limit,
			'orderby' => 'relevance',
		)
	);
	$found_ids = wp_list_pluck( $products, 'id' );

	// WordPress's default 's' search only looks at the title/content, so a
	// search for a brand or category name ("Husqvarna", "Mowers") would
	// otherwise come back empty even though matching products exist.
	if ( count( $products ) < $limit ) {
		foreach ( array( 'product_brand' => 'product_brand', 'product_cat' => 'category' ) as $taxonomy => $query_key ) {
			if ( count( $products ) >= $limit ) {
				break;
			}
			$matching_terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
					'name__like' => $term,
				)
			);
			if ( is_wp_error( $matching_terms ) || ! $matching_terms ) {
				continue;
			}
			$more = wc_get_products(
				array(
					'status'  => 'publish',
					$query_key => wp_list_pluck( $matching_terms, 'slug' ),
					'limit'   => $limit - count( $products ),
					'exclude' => $found_ids,
				)
			);
			foreach ( $more as $product ) {
				if ( ! in_array( $product->get_id(), $found_ids, true ) ) {
					$products[]  = $product;
					$found_ids[] = $product->get_id();
				}
			}
		}
	}

	$results = array();
	foreach ( $products as $product ) {
		$image_id = $product->get_image_id();
		$results[] = array(
			'name'    => $product->get_name(),
			// wc_price() encodes the currency symbol as an HTML entity (e.g.
			// &#36;) for safe HTML output; decode it back to a real
			// character since this goes out as plain-text JSON, not markup.
			'price'   => html_entity_decode( wp_strip_all_tags( wc_price( $product->get_price() ) ), ENT_QUOTES, 'UTF-8' ),
			'image'   => $image_id ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '',
			'badge'   => vss_stock_badge( $product )['text'],
			'url'     => get_permalink( $product->get_id() ),
		);
	}

	wp_send_json( $results );
}
add_action( 'wp_ajax_vss_search_suggest', 'vss_ajax_search_suggest' );
add_action( 'wp_ajax_nopriv_vss_search_suggest', 'vss_ajax_search_suggest' );

/**
 * "Recently Viewed" tracking -- a plain cookie of product IDs, most recent
 * first, capped at 12. Has to run on template_redirect (before any output)
 * since setcookie() cannot follow headers already sent.
 */
function vss_track_recently_viewed() {
	if ( ! is_singular( 'product' ) ) {
		return;
	}
	$id  = get_the_ID();
	$ids = isset( $_COOKIE['vss_recently_viewed'] ) ? array_map( 'absint', explode( ',', wp_unslash( $_COOKIE['vss_recently_viewed'] ) ) ) : array();
	$ids = array_values( array_diff( $ids, array( $id ) ) );
	array_unshift( $ids, $id );
	$ids = array_slice( $ids, 0, 12 );
	setcookie( 'vss_recently_viewed', implode( ',', $ids ), time() + MONTH_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN );
}
add_action( 'template_redirect', 'vss_track_recently_viewed' );

/**
 * Reads the vss_recently_viewed cookie back into real, still-published
 * WC_Product objects (most recent first), skipping the product currently
 * being viewed and anything deleted/unpublished since it was viewed.
 */
function vss_get_recently_viewed_products( $exclude_id, $limit = 4 ) {
	if ( empty( $_COOKIE['vss_recently_viewed'] ) ) {
		return array();
	}
	$ids = array_map( 'absint', explode( ',', wp_unslash( $_COOKIE['vss_recently_viewed'] ) ) );
	$ids = array_values( array_diff( array_filter( $ids ), array( absint( $exclude_id ) ) ) );
	if ( ! $ids ) {
		return array();
	}

	$products = wc_get_products(
		array(
			'status'  => 'publish',
			'include' => $ids,
			'limit'   => -1,
		)
	);

	// wc_get_products() with 'include' doesn't preserve the given order --
	// re-sort by the cookie's own most-recent-first order.
	$by_id = array();
	foreach ( $products as $product ) {
		$by_id[ $product->get_id() ] = $product;
	}
	$ordered = array();
	foreach ( $ids as $id ) {
		if ( isset( $by_id[ $id ] ) ) {
			$ordered[] = $by_id[ $id ];
		}
	}
	return array_slice( $ordered, 0, $limit );
}
