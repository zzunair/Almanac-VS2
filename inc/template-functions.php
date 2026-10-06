<?php
/**
 * Helpers for reading section order/visibility and section field values
 * that were registered in inc/section-defs.php and wired up to the
 * Customizer in inc/customizer.php.
 */

/**
 * Returns the ordered list of enabled section ids for a page.
 *
 * Falls back to the definition's default_order, and appends any section
 * id that exists in the definition but is missing from a saved order
 * (e.g. because the theme was updated and a new section was added).
 */
function vss_get_section_order( $page ) {
	$defs = vss_section_defs();
	if ( ! isset( $defs[ $page ] ) ) {
		return array();
	}

	$default_order = $defs[ $page ]['default_order'];
	$raw           = get_theme_mod( 'vss_' . $page . '_section_order', '' );
	$saved         = array();

	if ( $raw ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$saved = $decoded;
		}
	}

	if ( empty( $saved ) ) {
		$saved = array_map(
			function ( $id ) {
				return array( 'id' => $id, 'enabled' => true );
			},
			$default_order
		);
	}

	// Append any section defined in the theme but missing from the saved order.
	$known_ids = wp_list_pluck( $saved, 'id' );
	foreach ( $default_order as $id ) {
		if ( ! in_array( $id, $known_ids, true ) ) {
			$saved[] = array( 'id' => $id, 'enabled' => true );
		}
	}

	$enabled = array();
	foreach ( $saved as $row ) {
		if ( ! empty( $row['enabled'] ) && isset( $defs[ $page ]['sections'][ $row['id'] ] ) ) {
			$enabled[] = $row['id'];
		}
	}

	return $enabled;
}

/**
 * Reads one field value for one section of one page, falling back to the
 * default declared in section-defs.php.
 */
function vss_field( $page, $section, $field ) {
	$defs = vss_section_defs();
	if ( ! isset( $defs[ $page ]['sections'][ $section ]['fields'][ $field ] ) ) {
		return '';
	}

	$def = $defs[ $page ]['sections'][ $section ]['fields'][ $field ];
	if ( 'repeater' === $def['type'] ) {
		return '';
	}
	$mod_key = 'vss_' . $page . '_' . $section . '_' . $field;

	return get_theme_mod( $mod_key, $def['default'] );
}

/**
 * Reads one repeater field's blocks for one section of one page, falling
 * back to the default_blocks declared in section-defs.php. Each returned
 * block is guaranteed to have every declared block_field key (missing
 * ones filled with that field's default) so templates never need isset()
 * checks per field.
 */
function vss_repeater( $page, $section, $field ) {
	$defs = vss_section_defs();
	if ( ! isset( $defs[ $page ]['sections'][ $section ]['fields'][ $field ] ) ) {
		return array();
	}

	$def          = $defs[ $page ]['sections'][ $section ]['fields'][ $field ];
	$block_fields = isset( $def['block_fields'] ) ? $def['block_fields'] : array();
	$mod_key      = 'vss_' . $page . '_' . $section . '_' . $field;
	$default_json = wp_json_encode( isset( $def['default_blocks'] ) ? $def['default_blocks'] : array() );
	$raw          = get_theme_mod( $mod_key, $default_json );

	$blocks = json_decode( $raw, true );
	if ( ! is_array( $blocks ) ) {
		$blocks = array();
	}

	foreach ( $blocks as $i => $block ) {
		foreach ( $block_fields as $key => $field_def ) {
			if ( ! isset( $block[ $key ] ) ) {
				$blocks[ $i ][ $key ] = isset( $field_def['default'] ) ? $field_def['default'] : '';
			}
		}
	}

	return $blocks;
}

/**
 * Renders every enabled section (in saved order) for a page by including
 * template-parts/sections/{page}-{section}.php.
 */
function vss_render_sections( $page ) {
	foreach ( vss_get_section_order( $page ) as $section_id ) {
		get_template_part( 'template-parts/sections/' . $page . '-' . $section_id );
	}
}

/**
 * Splits a textarea field into an array of non-empty trimmed lines.
 */
function vss_lines( $text ) {
	$lines = preg_split( '/\r\n|\r|\n/', (string) $text );
	$lines = array_map( 'trim', $lines );
	return array_values( array_filter( $lines, 'strlen' ) );
}

/**
 * The curated list of Google Fonts offered in Customizer > Typography.
 * Keys double as the Google Fonts family name.
 */
function vss_font_choices() {
	return array(
		'Manrope'            => 'Manrope (default)',
		'Inter'              => 'Inter',
		'Poppins'            => 'Poppins',
		'Work Sans'          => 'Work Sans',
		'Roboto'             => 'Roboto',
		'Open Sans'          => 'Open Sans',
		'Lato'               => 'Lato',
		'Montserrat'         => 'Montserrat',
		'Nunito'             => 'Nunito',
		'Nunito Sans'        => 'Nunito Sans',
		'Raleway'            => 'Raleway',
		'Rubik'              => 'Rubik',
		'Karla'              => 'Karla',
		'DM Sans'            => 'DM Sans',
		'Source Sans 3'      => 'Source Sans 3',
		'PT Sans'            => 'PT Sans',
		'Noto Sans'          => 'Noto Sans',
		'Ubuntu'             => 'Ubuntu',
		'Oswald'             => 'Oswald',
		'Barlow'             => 'Barlow',
		'Josefin Sans'       => 'Josefin Sans',
		'Quicksand'          => 'Quicksand',
		'Cabin'              => 'Cabin',
		'Fira Sans'          => 'Fira Sans',
		'Archivo'            => 'Archivo',
		'Space Grotesk'      => 'Space Grotesk',
		'Outfit'             => 'Outfit',
		'Sora'               => 'Sora',
		'Plus Jakarta Sans'  => 'Plus Jakarta Sans',
		'Figtree'            => 'Figtree',
		'IBM Plex Sans'      => 'IBM Plex Sans',
		'Lora'               => 'Lora',
		'Playfair Display'   => 'Playfair Display',
		'Merriweather'       => 'Merriweather',
		'PT Serif'           => 'PT Serif',
		'Crimson Text'       => 'Crimson Text',
		'Libre Baskerville'  => 'Libre Baskerville',
		'EB Garamond'        => 'EB Garamond',
		'Cormorant'          => 'Cormorant',
		'Bitter'             => 'Bitter',
		'Zilla Slab'         => 'Zilla Slab',
	);
}

/**
 * Builds the Google Fonts CSS2 URL for one family across the weights the
 * theme actually uses.
 */
function vss_google_font_url( $family ) {
	$choices = vss_font_choices();
	if ( ! isset( $choices[ $family ] ) ) {
		$family = 'Manrope';
	}
	return 'https://fonts.googleapis.com/css2?family=' . rawurlencode( $family ) . ':wght@400;500;600;700;800&display=swap';
}

/**
 * Reads the seven global brand-color theme_mods, each with the design's
 * original value as its default, keyed by the CSS variable they feed.
 */
function vss_color_vars() {
	return array(
		'--tw-accent'      => get_theme_mod( 'vss_color_accent', '#f2600c' ),
		'--tw-accent-dark' => get_theme_mod( 'vss_color_accent_dark', '#c94a05' ),
		'--tw-text'        => get_theme_mod( 'vss_color_text', '#171310' ),
		'--tw-text-muted'  => get_theme_mod( 'vss_color_text_muted', '#6e655a' ),
		'--tw-bg'          => get_theme_mod( 'vss_color_bg', '#faf6f0' ),
		'--tw-bg-soft'     => get_theme_mod( 'vss_color_bg_soft', '#f1eae0' ),
		'--tw-border'      => get_theme_mod( 'vss_color_border', '#e4dacb' ),
	);
}

/**
 * The image WooCommerce itself stores for a product category (set on the
 * category's own edit screen), used as the fallback photo for a Collection
 * List tile that doesn't have its own custom image.
 */
function vss_category_thumbnail_url( $term ) {
	$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
	return $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'medium' ) : '';
}

/**
 * A decorative stroke-icon (SVG path data) for a category tile, keyed by
 * keyword match against the category name -- so any real category gets a
 * sensible icon without needing a per-category Customizer field. Falls
 * back to a generic equipment glyph for anything unrecognized.
 */
function vss_category_icon_path( $label ) {
	$label = strtolower( $label );
	$map   = array(
		'mower'     => 'M4 18h16M6 18l1-9h10l1 9M9 9V5h6v4',
		'chainsaw'  => 'M4 20l8-8M9 5l10 10-2 2L7 7z',
		'generator' => 'M4 8h16v10H4zM8 12h2M14 12h2',
		'trimmer'   => 'M4 20l7-7M14 6l4 4-8 8H6v-4z',
		'blower'    => 'M4 20l7-7M14 6l4 4-8 8H6v-4z',
		'snow'      => 'M12 3v18M4.5 7.5l15 9M19.5 7.5l-15 9',
		'tractor'   => 'M4 18h16M6 18l1-9h10l1 9M9 9V5h6v4',
		'part'      => 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l1.3-1.3a4 4 0 0 1-5.6 5.6l-6.6 6.6a2 2 0 0 1-2.8-2.8l6.6-6.6a4 4 0 0 1 5.6-5.6z',
		'accessor'  => 'M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l1.3-1.3a4 4 0 0 1-5.6 5.6l-6.6 6.6a2 2 0 0 1-2.8-2.8l6.6-6.6a4 4 0 0 1 5.6-5.6z',
	);
	foreach ( $map as $keyword => $path ) {
		if ( false !== strpos( $label, $keyword ) ) {
			return $path;
		}
	}
	return 'M4 8h16v10H4zM8 12h2M14 12h2';
}

/**
 * Live product count for a taxonomy term -- see shop-filters.php for why
 * the cached term_taxonomy.count can't be trusted for this.
 */
function vss_term_live_count( $taxonomy, $slug ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return 0;
	}
	$query_key = 'product_cat' === $taxonomy ? 'category' : $taxonomy;
	$result    = wc_get_products(
		array(
			'status'    => 'publish',
			$query_key  => array( $slug ),
			'paginate'  => true,
			'limit'     => 1,
		)
	);
	return is_object( $result ) ? (int) $result->total : 0;
}

/**
 * Every WooCommerce product always carries at least one product_cat term --
 * if none was picked, WordPress silently falls back to the default
 * "Uncategorized" term (same as any post). That's an internal fallback, not
 * real product data, so anywhere a category name gets displayed should
 * filter it out first -- otherwise removing/unassigning categories just
 * makes products show "Uncategorized" literally instead of nothing.
 */
function vss_product_categories( $product_id, $fields = 'names' ) {
	$default_id = (int) get_option( 'default_product_cat' );
	$terms      = wc_get_product_terms( $product_id, 'product_cat', array( 'fields' => 'all' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$terms = array_values( array_filter( $terms, function ( $term ) use ( $default_id ) {
		return $term->term_id !== $default_id;
	} ) );
	if ( 'names' === $fields ) {
		return wp_list_pluck( $terms, 'name' );
	}
	return $terms;
}

/**
 * A section that depends on real store data (categories, brands, products)
 * has nothing to show on a fresh install. Rather than the section just
 * vanishing -- which makes the page look broken/half-set-up with no
 * explanation -- show a setup hint, but ONLY to whoever can act on it
 * (logged in, can edit theme options); a real visitor never sees it, the
 * section is simply absent for them, same as before.
 */
function vss_render_empty_state( $heading, $message ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	?>
	<section class="tw-section">
		<div class="tw-container">
			<div class="tw-empty-state">
				<?php if ( $heading ) : ?><h2 class="tw-heading" style="margin-bottom:10px;"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
				<p><?php echo wp_kses_post( $message ); ?></p>
				<p class="tw-empty-state-note">Only visible to you (site admin) -- regular visitors won&rsquo;t see this section until it has real content.</p>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Maps a WooCommerce product's stock state to the badge used across the
 * design (in stock / low stock / out of stock).
 */
function vss_stock_badge( $product ) {
	$map = array(
		'instock'  => array( 'text' => 'in stock', 'class' => 'is-instock' ),
		'low'      => array( 'text' => 'low stock', 'class' => 'is-lowstock' ),
		'outofstock' => array( 'text' => 'out of stock', 'class' => 'is-outofstock' ),
	);

	$state = 'instock';

	if ( $product ) {
		if ( ! $product->is_in_stock() ) {
			$state = 'outofstock';
		} elseif ( $product->managing_stock() ) {
			$qty       = $product->get_stock_quantity();
			$low_amount = $product->get_low_stock_amount();
			if ( '' === $low_amount || null === $low_amount ) {
				$low_amount = get_option( 'woocommerce_notify_low_stock_amount', 2 );
			}
			if ( null !== $qty && $qty <= (int) $low_amount ) {
				$state = 'low';
			}
		}
	}

	return $map[ $state ];
}

/**
 * Extra fields the compare modal shows under the price: a short blurb
 * plus a handful of specs, so two products can be lined up against each
 * other instead of only image, name, price, and stock.
 */
function vss_compare_details( $product ) {
	$specs = array();

	$sku = $product->get_sku();
	if ( $sku ) {
		$specs[] = array(
			'label' => 'SKU',
			'value' => $sku,
		);
	}

	$categories = vss_product_categories( $product->get_id() );
	if ( $categories ) {
		$specs[] = array(
			'label' => 'Category',
			'value' => $categories[0],
		);
	}

	$weight = $product->get_weight();
	if ( $weight ) {
		$specs[] = array(
			'label' => 'Weight',
			'value' => $weight . ' ' . get_option( 'woocommerce_weight_unit' ),
		);
	}

	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! is_a( $attribute, 'WC_Product_Attribute' ) ) {
			continue;
		}
		$label = wc_attribute_label( $attribute->get_name() );
		if ( '' === $label || 'brand' === strtolower( $label ) ) {
			continue;
		}
		if ( $attribute->is_taxonomy() ) {
			$names = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
			$value = is_wp_error( $names ) ? '' : implode( ', ', $names );
		} else {
			$value = implode( ', ', $attribute->get_options() );
		}
		if ( '' === $value ) {
			continue;
		}
		$specs[] = array(
			'label' => $label,
			'value' => $value,
		);
	}

	$specs = array_slice( $specs, 0, 6 );

	$blurb = $product->get_short_description();
	if ( ! $blurb ) {
		$blurb = $product->get_description();
	}
	$blurb = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $blurb ) ) );
	if ( $blurb ) {
		$blurb = wp_trim_words( $blurb, 26, '…' );
	}

	return array(
		'description' => $blurb,
		'specs'       => $specs,
	);
}

/**
 * data-* attributes for a compare checkbox. Printed the same way on
 * product cards and the product page so the saved snapshot always
 * includes the description and specs.
 */
function vss_compare_checkbox_attrs( $product ) {
	$brand_terms = wc_get_product_terms( $product->get_id(), 'product_brand', array( 'fields' => 'names' ) );
	$badge       = vss_stock_badge( $product );
	$image_id    = $product->get_image_id();
	$details     = vss_compare_details( $product );

	$attrs = array(
		'data-id'          => $product->get_id(),
		'data-name'        => $product->get_name(),
		'data-brand'       => $brand_terms ? $brand_terms[0] : '',
		'data-price'       => wp_strip_all_tags( wc_price( $product->get_price() ) ),
		'data-image'       => $image_id ? (string) wp_get_attachment_image_url( $image_id, 'medium' ) : '',
		'data-url'         => get_permalink( $product->get_id() ),
		'data-badge'       => $badge['text'],
		'data-badge-class' => $badge['class'],
		'data-description' => $details['description'],
		'data-specs'       => wp_json_encode( $details['specs'] ),
	);

	$html = '';
	foreach ( $attrs as $name => $value ) {
		$html .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
	}
	return $html;
}
