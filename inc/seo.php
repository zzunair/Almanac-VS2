<?php
/**
 * Built-in SEO: meta description, canonical, Open Graph / Twitter cards,
 * robots rules and JSON-LD (business, website search, breadcrumbs).
 *
 * Everything here steps aside when a dedicated SEO plugin is active, so
 * sites that install Yoast, Rank Math, etc. never get duplicate tags.
 * Product structured data is WooCommerce's own and is always on.
 */

/** Placeholder contact details shipped as Customizer defaults -- never published as schema. */
function vss_seo_placeholder_values() {
	return array( '123 Main Street', 'Your City, ST 00000', '(123) 456-7890', 'hello@yourstore.com' );
}

function vss_seo_plugin_active() {
	$active = defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| defined( 'SLIM_SEO_VER' )
		|| function_exists( 'the_seo_framework' );
	return (bool) apply_filters( 'vss_seo_plugin_active', $active );
}

/** Plain-text excerpt capped at $max characters, cut on a word boundary. */
function vss_seo_trim( $text, $max = 160 ) {
	$text = html_entity_decode( (string) $text, ENT_QUOTES, 'UTF-8' );
	$text = wp_strip_all_tags( strip_shortcodes( $text ), true );
	$text = trim( preg_replace( '/\s+/u', ' ', $text ) );
	if ( '' === $text || mb_strlen( $text ) <= $max ) {
		return $text;
	}
	$cut   = mb_substr( $text, 0, $max - 1 );
	$space = mb_strrpos( $cut, ' ' );
	if ( false !== $space && $space > 60 ) {
		$cut = mb_substr( $cut, 0, $space );
	}
	return rtrim( $cut, " ,.;:-–—" ) . '…';
}

/** Intro copy of the Customizer-built pages, used when the page itself has no content. */
function vss_seo_page_section_text( $slug ) {
	$map = array(
		'service'   => array( 'service', 'hero', 'body' ),
		'financing' => array( 'financing', 'hero', 'body' ),
		'contact'   => array( 'contact', 'intro', 'body' ),
		'about'     => array( 'about', 'intro', 'body1' ),
	);
	if ( ! isset( $map[ $slug ] ) ) {
		return '';
	}
	return vss_field( $map[ $slug ][0], $map[ $slug ][1], $map[ $slug ][2] );
}

function vss_seo_description() {
	$text = '';

	if ( is_front_page() ) {
		$text = get_theme_mod( 'vss_seo_home_description', '' );
		if ( ! $text ) {
			$slides = vss_repeater( 'home', 'hero', 'slides' );
			$text   = ! empty( $slides[0]['body'] ) ? $slides[0]['body'] : '';
		}
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product ) {
			$text = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
			if ( ! vss_seo_trim( $text ) ) {
				$text = sprintf( '%s — available at %s.', $product->get_name(), get_bloginfo( 'name' ) );
			}
		}
	} elseif ( function_exists( 'is_shop' ) && is_shop() && ! is_search() ) {
		$text = get_theme_mod( 'vss_seo_shop_description', '' );
		if ( ! $text ) {
			$text = sprintf( 'Browse equipment in stock at %s, with live availability and current pricing.', get_bloginfo( 'name' ) );
		}
	} elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$term = get_queried_object();
		$text = term_description();
		if ( ! vss_seo_trim( $text ) && $term ) {
			$text = sprintf( 'Shop %s at %s — real inventory with live stock status and current pricing.', $term->name, get_bloginfo( 'name' ) );
		}
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		if ( has_excerpt( $post ) ) {
			$text = get_the_excerpt( $post );
		} elseif ( ! empty( $post->post_content ) ) {
			$text = $post->post_content;
		}
		if ( ! vss_seo_trim( $text ) && 'page' === $post->post_type ) {
			$text = vss_seo_page_section_text( $post->post_name );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$text = term_description();
	}

	if ( ! vss_seo_trim( $text ) ) {
		$text = get_bloginfo( 'description' );
	}
	return vss_seo_trim( $text );
}

/** Canonical URL of the current view. Filter/sort query strings are dropped on purpose. */
function vss_seo_canonical_url() {
	$paged = max( 1, (int) get_query_var( 'paged' ), isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1 );
	$url   = '';

	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_singular() ) {
		$url = get_permalink( get_queried_object_id() );
	} elseif ( function_exists( 'is_shop' ) && is_shop() && ! is_search() ) {
		$url = wc_get_page_permalink( 'shop' );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$link = get_term_link( get_queried_object() );
		$url  = is_wp_error( $link ) ? '' : $link;
	}

	if ( $url && $paged > 1 && ! is_singular() ) {
		$url = add_query_arg( 'paged', $paged, $url );
	}
	return $url;
}

/** Best share image for the current view: array( url, width, height ) or null. */
function vss_seo_image() {
	$attachment_id = 0;

	if ( function_exists( 'is_product' ) && is_product() ) {
		$product       = wc_get_product( get_queried_object_id() );
		$attachment_id = $product ? (int) $product->get_image_id() : 0;
	} elseif ( is_singular() && has_post_thumbnail( get_queried_object_id() ) ) {
		$attachment_id = (int) get_post_thumbnail_id( get_queried_object_id() );
	} elseif ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		$attachment_id = (int) get_term_meta( get_queried_object_id(), 'thumbnail_id', true );
	}

	if ( $attachment_id ) {
		$src = wp_get_attachment_image_src( $attachment_id, 'large' );
		if ( $src ) {
			return array( $src[0], (int) $src[1], (int) $src[2] );
		}
	}

	if ( is_front_page() ) {
		$slides = vss_repeater( 'home', 'hero', 'slides' );
		if ( ! empty( $slides[0]['image'] ) ) {
			return array( $slides[0]['image'], 0, 0 );
		}
	}

	foreach ( array( get_theme_mod( 'vss_seo_share_image', '' ), get_theme_mod( 'vss_header_logo_image', '' ), get_site_icon_url( 512 ) ) as $fallback ) {
		if ( $fallback ) {
			return array( $fallback, 0, 0 );
		}
	}
	return null;
}

function vss_print_json_ld( $data ) {
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . "</script>\n";
}

function vss_seo_head() {
	if ( vss_seo_plugin_active() || is_404() ) {
		return;
	}

	$description = vss_seo_description();
	$canonical   = vss_seo_canonical_url();
	$image       = vss_seo_image();
	$is_product  = function_exists( 'is_product' ) && is_product();
	$type        = $is_product ? 'product' : ( is_singular( 'post' ) ? 'article' : 'website' );

	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	if ( $canonical && ! is_singular() ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
	}

	$og = array(
		'og:locale'    => get_locale(),
		'og:type'      => $type,
		'og:site_name' => get_bloginfo( 'name' ),
		'og:title'     => wp_get_document_title(),
		'og:url'       => $canonical,
	);
	if ( $description ) {
		$og['og:description'] = $description;
	}
	if ( $image ) {
		$og['og:image'] = $image[0];
		if ( $image[1] && $image[2] ) {
			$og['og:image:width']  = $image[1];
			$og['og:image:height'] = $image[2];
		}
	}
	if ( $is_product ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product && '' !== $product->get_price() ) {
			$og['product:price:amount']   = wc_format_decimal( $product->get_price(), wc_get_price_decimals() );
			$og['product:price:currency'] = get_woocommerce_currency();
			$og['product:availability']   = $product->is_in_stock() ? 'in stock' : 'out of stock';
		}
	}
	foreach ( $og as $property => $content ) {
		if ( '' !== (string) $content ) {
			echo '<meta property="' . esc_attr( $property ) . '" content="' . esc_attr( $content ) . '" />' . "\n";
		}
	}

	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";

	if ( is_front_page() ) {
		vss_print_json_ld( vss_seo_business_schema() );
		vss_print_json_ld(
			array(
				'@context'        => 'https://schema.org',
				'@type'           => 'WebSite',
				'name'            => get_bloginfo( 'name' ),
				'url'             => home_url( '/' ),
				'potentialAction' => array(
					'@type'       => 'SearchAction',
					'target'      => array(
						'@type'       => 'EntryPoint',
						'urlTemplate' => home_url( '/?post_type=product&s={search_term_string}' ),
					),
					'query-input' => 'required name=search_term_string',
				),
			)
		);
	}
}
add_action( 'wp_head', 'vss_seo_head', 2 );

/** LocalBusiness schema built from the footer contact details. */
function vss_seo_business_schema() {
	$placeholders = vss_seo_placeholder_values();
	$type         = preg_replace( '/[^A-Za-z]/', '', get_theme_mod( 'vss_seo_business_type', 'Store' ) );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => $type ? $type : 'Store',
		'@id'      => home_url( '/#business' ),
		'name'     => get_theme_mod( 'vss_footer_company_name', get_bloginfo( 'name' ) ),
		'url'      => home_url( '/' ),
	);

	$logo = get_theme_mod( 'vss_header_logo_image', '' );
	if ( $logo ) {
		$schema['logo']  = $logo;
		$schema['image'] = $logo;
	}

	foreach ( vss_lines( get_theme_mod( 'vss_footer_phone_email', '' ) ) as $line ) {
		if ( in_array( $line, $placeholders, true ) ) {
			continue;
		}
		if ( is_email( $line ) ) {
			$schema['email'] = $line;
		} elseif ( preg_match( '/\d{3}.*\d{4}/', $line ) ) {
			$schema['telephone'] = $line;
		}
	}

	$address = array_values( array_diff( vss_lines( get_theme_mod( 'vss_footer_address', '' ) ), $placeholders ) );
	if ( $address ) {
		$postal = array(
			'@type'         => 'PostalAddress',
			'streetAddress' => $address[0],
		);
		if ( isset( $address[1] ) && preg_match( '/^(.+?),\s*([A-Za-z .]+?)\s+([A-Za-z0-9 -]{3,10})$/', $address[1], $m ) ) {
			$postal['addressLocality'] = trim( $m[1] );
			$postal['addressRegion']   = trim( $m[2] );
			$postal['postalCode']      = trim( $m[3] );
		} elseif ( isset( $address[1] ) ) {
			$postal['addressLocality'] = $address[1];
		}
		$schema['address'] = $postal;
	}

	$profiles = array_values( array_filter( array_map( 'esc_url_raw', vss_lines( get_theme_mod( 'vss_seo_social_profiles', '' ) ) ) ) );
	if ( $profiles ) {
		$schema['sameAs'] = $profiles;
	}

	return $schema;
}

/** Keep internal search results out of the index. */
function vss_seo_robots( $robots ) {
	if ( ! vss_seo_plugin_active() && is_search() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'vss_seo_robots' );

/**
 * Linked breadcrumb trail with BreadcrumbList schema.
 * $items: list of array( 'label' => ..., 'url' => ... ); the last item is the current page.
 */
function vss_breadcrumb( $items, $wrap_class = 'tw-container' ) {
	if ( ! $items ) {
		return;
	}
	$last = count( $items ) - 1;
	echo '<nav class="' . esc_attr( trim( $wrap_class . ' tw-breadcrumb' ) ) . '" aria-label="Breadcrumb"><ol>';
	foreach ( $items as $i => $item ) {
		echo '<li>';
		if ( $i < $last && ! empty( $item['url'] ) ) {
			echo '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( $item['label'] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';

	if ( vss_seo_plugin_active() ) {
		return;
	}
	$list = array();
	foreach ( $items as $i => $item ) {
		$entry = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $item['label'],
		);
		$url = ! empty( $item['url'] ) ? $item['url'] : ( $i === $last ? vss_seo_canonical_url() : '' );
		if ( $url ) {
			$entry['item'] = $url;
		}
		$list[] = $entry;
	}
	vss_print_json_ld(
		array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $list,
		)
	);
}

/** Trail items for a product term, ancestors first. */
function vss_breadcrumb_term_items( $term ) {
	$items = array();
	foreach ( array_reverse( get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' ) ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, $term->taxonomy );
		if ( $ancestor && ! is_wp_error( $ancestor ) ) {
			$ancestor_link = get_term_link( $ancestor );
			$items[]       = array( 'label' => $ancestor->name, 'url' => is_wp_error( $ancestor_link ) ? '' : $ancestor_link );
		}
	}
	$link    = get_term_link( $term );
	$items[] = array( 'label' => $term->name, 'url' => is_wp_error( $link ) ? '' : $link );
	return $items;
}

/** Alt text for an attachment, falling back to $fallback when the media library has none. */
function vss_image_attrs( $attachment_id, $fallback, $attrs = array() ) {
	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
	if ( '' === $alt ) {
		$attrs['alt'] = $fallback;
	}
	return $attrs;
}
