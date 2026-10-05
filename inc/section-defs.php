<?php
/**
 * Central registry of customizable sections for every page template.
 *
 * Each page has an ordered list of sections. Every section has a set of
 * fields that are exposed in the Customizer. This single table drives:
 *  - the Customizer panels/sections/controls (inc/customizer.php)
 *  - the drag-and-drop / enable-disable section order control
 *  - the front-end renderer that includes the matching template-part
 *
 * Two kinds of fields:
 *  - scalar fields (type: text, textarea, checkbox, number, color, image,
 *    url) -- a single value, read with vss_field().
 *  - repeater fields (type: repeater) -- an add/remove/reorder list of
 *    blocks, each block having its own set of `block_fields`, read with
 *    vss_repeater(). This is the "Shopify section blocks" pattern:
 *    slides, collection tiles, feature cards, steps, etc. are all blocks
 *    a client can add, remove, and reorder without touching code.
 */

function vss_product_cat_options() {
	$options = array( '' => '— choose a category —' );
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $options;
	}
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'exclude'    => array( get_option( 'default_product_cat' ) ),
		)
	);
	if ( is_wp_error( $terms ) ) {
		return $options;
	}
	foreach ( $terms as $term ) {
		$options[ $term->slug ] = $term->name;
	}
	return $options;
}

function vss_product_brand_options() {
	$options = array( '' => '— choose a brand —' );
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return $options;
	}
	$terms = get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return $options;
	}
	foreach ( $terms as $term ) {
		$options[ $term->slug ] = $term->name;
	}
	return $options;
}

function vss_section_defs() {
	return array(
		'home' => array(
			'label'    => 'Home Page',
			'sections' => array(
				'hero' => array(
					'label'  => 'Hero Slideshow',
					'fields' => array(
						'trust_text' => array( 'type' => 'text', 'label' => 'Trust line (under the buttons)', 'default' => '500+ units in stock · reserved items held 24 hrs, no payment required' ),
						'slides'        => array(
							'type'            => 'repeater',
							'label'           => 'Slides',
							'title_field'     => 'heading',
							'add_button_text' => 'Add slide',
							'min'             => 1,
							'block_fields'    => array(
								'layout'          => array( 'type' => 'select', 'label' => 'Layout', 'default' => 'split', 'options' => array( 'split' => 'Split (copy left, image card right)', 'banner' => 'Full-width banner (image behind text)' ) ),
								'text_align'      => array( 'type' => 'select', 'label' => 'Text alignment (banner layout only)', 'default' => 'left', 'options' => array( 'left' => 'Left', 'center' => 'Center', 'right' => 'Right' ) ),
								'eyebrow'         => array( 'type' => 'text', 'label' => 'Eyebrow text', 'default' => 'local dealer · in-stock inventory' ),
								'heading'         => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'New slide' ),
								'body'            => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => '' ),
								'button_text'     => array( 'type' => 'text', 'label' => 'Primary button text', 'default' => 'Browse inventory' ),
								'button_link'     => array( 'type' => 'url', 'label' => 'Primary button link', 'default' => '/shop/' ),
								'button2_text'    => array( 'type' => 'text', 'label' => 'Secondary button text (optional)', 'default' => 'Book Service' ),
								'button2_link'    => array( 'type' => 'url', 'label' => 'Secondary button link', 'default' => '/service/' ),
								'image'           => array( 'type' => 'image', 'label' => 'Feature image (split layout: decorative card. Banner layout: full-width background)', 'default' => '' ),
								'highlight_text'   => array( 'type' => 'text', 'label' => 'Floating highlight text (optional, over the image)', 'default' => '' ),
								'highlight_badge'  => array( 'type' => 'text', 'label' => 'Floating highlight badge (optional)', 'default' => 'In Stock' ),
							),
							'default_blocks'  => array(
								array(
									'layout'      => 'split',
									'text_align'  => 'left',
									'eyebrow'     => 'local dealer · in-stock inventory',
									'heading'     => "Browse what's actually on our floor.",
									'body'        => "Real inventory, real pricing, no surprises. Reserve an item online and we'll set it aside for 24 hours — no payment required.",
									'button_text' => 'Browse inventory',
									'button_link' => '/shop/',
									'button2_text' => 'Book Service',
									'button2_link' => '/service/',
									'image'       => '',
									'highlight_text' => '',
									'highlight_badge' => 'In Stock',
								),
							),
						),
					),
				),
				'categories' => array(
					'label'  => 'Collection List',
					'fields' => array(
						'heading'     => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Shop by category' ),
						'collections' => array(
							'type'            => 'repeater',
							'label'           => 'Collections',
							'title_field'     => 'category',
							'add_button_text' => 'Add collection',
							'min'             => 0,
							'block_fields'    => array(
								'category' => array( 'type' => 'select', 'label' => 'Category', 'default' => '', 'options' => 'product_cat' ),
								'label'    => array( 'type' => 'text', 'label' => 'Custom label (optional)', 'default' => '' ),
								'image'    => array( 'type' => 'image', 'label' => 'Custom image (optional -- falls back to the category\'s own photo)', 'default' => '' ),
								'link'     => array( 'type' => 'url', 'label' => 'Custom link (optional -- falls back to the category page)', 'default' => '' ),
							),
							// Left empty on purpose: an empty list means "show every
							// real WooCommerce category automatically" (see
							// home-categories.php) so the section is never blank
							// before a client has curated anything.
							'default_blocks'  => array(),
						),
					),
				),
				'brands' => array(
					'label'  => 'Brand Strip',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading (optional)', 'default' => '' ),
						'logos'   => array(
							'type'            => 'repeater',
							'label'           => 'Brand logos',
							'title_field'     => 'brand',
							'add_button_text' => 'Add brand logo',
							'min'             => 0,
							'block_fields'    => array(
								'brand' => array( 'type' => 'select', 'label' => 'Brand', 'default' => '', 'options' => 'product_brand' ),
								'image' => array( 'type' => 'image', 'label' => 'Logo image', 'default' => '' ),
								'link'  => array( 'type' => 'url', 'label' => 'Custom link (optional -- falls back to the brand page)', 'default' => '' ),
							),
							// Left empty on purpose: with no logos curated, the strip
							// falls back to a plain text list of every real brand
							// (see home-brands.php) so it's never blank. Add a logo
							// per brand here once you have real brand artwork.
							'default_blocks'  => array(),
						),
					),
				),
				'featured_products' => array(
					'label'  => 'Featured Products',
					'fields' => array(
						'heading'        => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Featured products' ),
						'view_all_text'  => array( 'type' => 'text', 'label' => '"View all" link text', 'default' => 'View all' ),
						'view_all_link'  => array( 'type' => 'url', 'label' => '"View all" link', 'default' => '/shop/' ),
						'count'          => array( 'type' => 'number', 'label' => 'Number of products to show', 'default' => 8 ),
					),
				),
				'how_it_works' => array(
					'label'  => 'How Reservations Work',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'How reservations work' ),
						'steps'   => array(
							'type'            => 'repeater',
							'label'           => 'Steps',
							'title_field'     => 'title',
							'add_button_text' => 'Add step',
							'min'             => 1,
							'block_fields'    => array(
								'title' => array( 'type' => 'text', 'label' => 'Step title', 'default' => 'New step' ),
								'desc'  => array( 'type' => 'textarea', 'label' => 'Step description', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'title' => 'Browse & pick an item', 'desc' => 'Find the item you want, with live stock status and current pricing.' ),
								array( 'title' => 'Submit a reservation', 'desc' => 'Fill out the reserve form on the product page — no payment needed.' ),
								array( 'title' => 'We hold it 24 hours', 'desc' => 'Our team confirms by phone and holds the item for you at the store.' ),
							),
						),
					),
				),
				'callouts' => array(
					'label'  => 'Service / Financing / Delivery Callouts',
					'fields' => array(
						'items' => array(
							'type'            => 'repeater',
							'label'           => 'Callouts',
							'title_field'     => 'title',
							'add_button_text' => 'Add callout',
							'min'             => 1,
							'block_fields'    => array(
								'image' => array( 'type' => 'image', 'label' => 'Image (optional)', 'default' => '' ),
								'title' => array( 'type' => 'text', 'label' => 'Title', 'default' => 'New callout' ),
								'desc'  => array( 'type' => 'textarea', 'label' => 'Description', 'default' => '' ),
								'link'  => array( 'type' => 'url', 'label' => 'Link (optional)', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'image' => '', 'title' => 'service & repair', 'desc' => 'Bring your equipment in for tune-ups and repair. Ask our team about scheduling.', 'link' => '/service/' ),
								array( 'image' => '', 'title' => 'financing available', 'desc' => 'Financing options may be available on qualifying purchases — inquire in-store.', 'link' => '/financing/' ),
								array( 'image' => '', 'title' => 'delivery & pickup', 'desc' => 'Local delivery and curbside pickup offered on most in-stock items.', 'link' => '' ),
							),
						),
					),
				),
				'testimonials' => array(
					'label'  => 'Testimonials',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'What our customers say' ),
						'items'   => array(
							'type'            => 'repeater',
							'label'           => 'Testimonials',
							'title_field'     => 'name',
							'add_button_text' => 'Add testimonial',
							'min'             => 0,
							'block_fields'    => array(
								'quote'    => array( 'type' => 'textarea', 'label' => 'Quote', 'default' => '' ),
								'name'     => array( 'type' => 'text', 'label' => 'Customer name', 'default' => 'New customer' ),
								'location' => array( 'type' => 'text', 'label' => 'Location', 'default' => '' ),
								'rating'   => array( 'type' => 'number', 'label' => 'Star rating (1-5)', 'default' => 5 ),
							),
							'default_blocks'  => array(
								array( 'quote' => 'Reserved a mower online and it was waiting for me the next morning. Zero hassle.', 'name' => 'Dana R.', 'location' => 'Millbrook', 'rating' => 5 ),
								array( 'quote' => 'Filters actually show real stock counts, so I never make a trip for something they don’t have.', 'name' => 'Carl W.', 'location' => 'Fairview Heights', 'rating' => 5 ),
								array( 'quote' => 'Service team is honest about what actually needs fixing. Appreciate that.', 'name' => 'Priya S.', 'location' => 'Millbrook', 'rating' => 5 ),
							),
						),
					),
				),
				'about_teaser' => array(
					'label'  => 'About Teaser',
					'fields' => array(
						'heading'     => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'A local dealer, not a warehouse' ),
						'body'        => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => "We've served Millbrook since 1994 — stocking, servicing, and standing behind the equipment we sell. Come see the floor for yourself." ),
						'link_text'   => array( 'type' => 'text', 'label' => 'Link text', 'default' => 'More about us' ),
						'link_href'   => array( 'type' => 'url', 'label' => 'Link URL', 'default' => '/about/' ),
						'image'       => array( 'type' => 'image', 'label' => 'Photo (leave empty to keep placeholder)', 'default' => '' ),
						'image_label' => array( 'type' => 'text', 'label' => 'Placeholder label', 'default' => 'Storefront Photo' ),
					),
				),
			),
			'default_order' => array( 'hero', 'categories', 'brands', 'featured_products', 'how_it_works', 'testimonials', 'callouts', 'about_teaser' ),
		),

		'shop' => array(
			'label'    => 'Shop Page',
			'sections' => array(
				'breadcrumb' => array(
					'label'  => 'Breadcrumb',
					'fields' => array(),
				),
				'filters' => array(
					'label'  => 'Filters Sidebar',
					'fields' => array(
						'heading'            => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Filters' ),
						'show_category'      => array( 'type' => 'checkbox', 'label' => 'Show category filter', 'default' => true ),
						'show_brand'         => array( 'type' => 'checkbox', 'label' => 'Show brand filter', 'default' => true ),
						'show_availability'  => array( 'type' => 'checkbox', 'label' => 'Show availability filter', 'default' => true ),
						'category_label'     => array( 'type' => 'text', 'label' => 'Category group label', 'default' => 'Category' ),
						'brand_label'        => array( 'type' => 'text', 'label' => 'Brand group label', 'default' => 'Brand' ),
						'availability_label' => array( 'type' => 'text', 'label' => 'Availability group label', 'default' => 'Availability' ),
						'instock_label'      => array( 'type' => 'text', 'label' => 'In-stock option label', 'default' => 'In Stock' ),
						'lowstock_label'     => array( 'type' => 'text', 'label' => 'Low-stock option label', 'default' => 'Low Stock' ),
						'outofstock_label'   => array( 'type' => 'text', 'label' => 'Out-of-stock option label', 'default' => 'Out of Stock' ),
					),
				),
				'products_grid' => array(
					'label'  => 'Products Grid',
					'fields' => array(
						'heading'            => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Shop' ),
						'per_page'           => array( 'type' => 'number', 'label' => 'Products per page', 'default' => 12 ),
						'count_suffix'       => array( 'type' => 'text', 'label' => 'Product count suffix', 'default' => ' products' ),
						'empty_search_text'  => array( 'type' => 'textarea', 'label' => 'No search results text (use {term} as placeholder)', 'default' => 'Nothing matched "{term}". Try a different search or browse by category on the left.' ),
						'empty_filter_text'  => array( 'type' => 'textarea', 'label' => 'No filter results text', 'default' => 'No products match those filters.' ),
					),
				),
			),
			'default_order' => array( 'breadcrumb', 'filters', 'products_grid' ),
		),

		'product' => array(
			'label'    => 'Product Page',
			'sections' => array(
				'breadcrumb' => array(
					'label'  => 'Breadcrumb',
					'fields' => array(),
				),
				'product_main' => array(
					'label'  => 'Gallery, Info & Reserve Form',
					'fields' => array(
						'stock_location_suffix' => array( 'type' => 'text', 'label' => 'Stock quantity suffix', 'default' => ' units at Millbrook location' ),
						'main_photo_placeholder' => array( 'type' => 'text', 'label' => 'Main photo placeholder text', 'default' => 'Main Product Photo' ),
						'reserve_heading'       => array( 'type' => 'text', 'label' => 'Reserve box heading', 'default' => 'Reserve this item' ),
						'reserve_intro'         => array( 'type' => 'textarea', 'label' => 'Reserve box intro text', 'default' => "We'll hold this item for 24 hours after you reserve. No payment required." ),
						'name_label'            => array( 'type' => 'text', 'label' => 'Name field label', 'default' => 'Name' ),
						'name_placeholder'      => array( 'type' => 'text', 'label' => 'Name field placeholder', 'default' => 'Full name' ),
						'phone_label'           => array( 'type' => 'text', 'label' => 'Phone field label', 'default' => 'Phone' ),
						'phone_placeholder'     => array( 'type' => 'text', 'label' => 'Phone field placeholder', 'default' => '(555) 555-5555' ),
						'email_label'           => array( 'type' => 'text', 'label' => 'Email field label', 'default' => 'Email' ),
						'email_placeholder'     => array( 'type' => 'text', 'label' => 'Email field placeholder', 'default' => 'you@example.com' ),
						'notes_label'           => array( 'type' => 'text', 'label' => 'Notes field label', 'default' => 'Notes / Message' ),
						'notes_placeholder'     => array( 'type' => 'text', 'label' => 'Notes field placeholder', 'default' => 'Anything we should know?' ),
						'reserve_button_text'   => array( 'type' => 'text', 'label' => 'Reserve button text', 'default' => 'Reserve this item' ),
						'success_heading'       => array( 'type' => 'text', 'label' => 'Success heading', 'default' => 'Reservation received' ),
						'success_text'          => array( 'type' => 'textarea', 'label' => 'Success text (use {name} as placeholder)', 'default' => 'Thanks{name}. Our team will call within one business day to confirm. This item is held for 24 hours.' ),
						'reset_button_text'     => array( 'type' => 'text', 'label' => 'Reset button text', 'default' => 'Make another reservation' ),
						'outofstock_heading'    => array( 'type' => 'text', 'label' => 'Out-of-stock heading', 'default' => 'Currently out of stock' ),
						'outofstock_text'       => array( 'type' => 'textarea', 'label' => 'Out-of-stock body text', 'default' => 'Check back soon, or contact us to ask about availability.' ),
					),
				),
				'details' => array(
					'label'  => 'Description & Specifications',
					'fields' => array(
						'description_heading' => array( 'type' => 'text', 'label' => 'Description heading', 'default' => 'Full description' ),
						'specs_heading'        => array( 'type' => 'text', 'label' => 'Specifications heading', 'default' => 'Specifications' ),
					),
				),
				'recently_viewed' => array(
					'label'  => 'Recently Viewed',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Recently Viewed' ),
						'count'   => array( 'type' => 'number', 'label' => 'Number of products to show', 'default' => 4 ),
					),
				),
			),
			'default_order' => array( 'breadcrumb', 'product_main', 'details', 'recently_viewed' ),
		),

		'service' => array(
			'label'    => 'Service Page',
			'sections' => array(
				'hero' => array(
					'label'  => 'Hero',
					'fields' => array(
						'eyebrow' => array( 'type' => 'text', 'label' => 'Eyebrow text', 'default' => 'in-house support' ),
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Service & Repair' ),
						'body'    => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => 'Everything we sell is backed by our own service bay. Ask our team in-store for current scheduling and turnaround.' ),
					),
				),
				'service_cards' => array(
					'label'  => 'Service Cards',
					'fields' => array(
						'cards' => array(
							'type'            => 'repeater',
							'label'           => 'Cards',
							'title_field'     => 'title',
							'add_button_text' => 'Add card',
							'min'             => 1,
							'block_fields'    => array(
								'title' => array( 'type' => 'text', 'label' => 'Title', 'default' => 'New card' ),
								'desc'  => array( 'type' => 'textarea', 'label' => 'Description', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'title' => 'Tune-ups & maintenance', 'desc' => 'Blade sharpening, oil changes, belt and cable adjustments to keep equipment running through the season.' ),
								array( 'title' => 'Repairs', 'desc' => 'Engine, electrical, and drive-system repair for mowers, chainsaws, trimmers, and generators — all brands welcome.' ),
								array( 'title' => 'Seasonal storage prep', 'desc' => 'Fuel stabilization and winterizing so your equipment starts reliably next season.' ),
							),
						),
					),
				),
				'faq' => array(
					'label'  => 'FAQ',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Service FAQ' ),
						'items'   => array(
							'type'            => 'repeater',
							'label'           => 'Questions',
							'title_field'     => 'question',
							'add_button_text' => 'Add question',
							'min'             => 0,
							'block_fields'    => array(
								'question' => array( 'type' => 'text', 'label' => 'Question', 'default' => 'New question' ),
								'answer'   => array( 'type' => 'textarea', 'label' => 'Answer', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'question' => 'How long does a typical repair take?', 'answer' => 'Most tune-ups and minor repairs are completed same-day. Larger jobs usually take 3-5 business days — we’ll always give you an estimate before starting.' ),
								array( 'question' => 'Do you service equipment you didn’t sell us?', 'answer' => 'Yes — we service most major brands regardless of where you bought them.' ),
								array( 'question' => 'Do I need an appointment?', 'answer' => 'Walk-ins are welcome, but booking ahead guarantees a same-week drop-off slot.' ),
							),
						),
					),
				),
				'cta_banner' => array(
					'label'  => 'CTA Banner',
					'fields' => array(
						'heading'     => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Schedule a service visit' ),
						'body'        => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => 'Call or stop by — our service bay books appointments in-store.' ),
						'button_text' => array( 'type' => 'text', 'label' => 'Button text', 'default' => 'Contact Us to Schedule' ),
						'button_link' => array( 'type' => 'url', 'label' => 'Button link', 'default' => '/contact/' ),
					),
				),
			),
			'default_order' => array( 'hero', 'service_cards', 'faq', 'cta_banner' ),
		),

		'financing' => array(
			'label'    => 'Financing Page',
			'sections' => array(
				'hero' => array(
					'label'  => 'Hero',
					'fields' => array(
						'eyebrow' => array( 'type' => 'text', 'label' => 'Eyebrow text', 'default' => 'informational only' ),
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Financing' ),
						'body'    => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => 'Most purchases can be financed. Details below — ask our team in-store for current terms.' ),
					),
				),
				'financing_cards' => array(
					'label'  => 'Financing Cards',
					'fields' => array(
						'cards'      => array(
							'type'            => 'repeater',
							'label'           => 'Cards',
							'title_field'     => 'title',
							'add_button_text' => 'Add card',
							'min'             => 1,
							'block_fields'    => array(
								'title' => array( 'type' => 'text', 'label' => 'Title', 'default' => 'New card' ),
								'desc'  => array( 'type' => 'textarea', 'label' => 'Description', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'title' => 'Qualifying purchases', 'desc' => 'Financing may be available on larger equipment purchases, subject to approval.' ),
								array( 'title' => 'In-store application', 'desc' => 'Our team can walk you through current options and terms when you visit.' ),
								array( 'title' => 'No online payment', 'desc' => 'This page is informational only — all financing and payment is handled in person.' ),
							),
						),
						'disclaimer' => array( 'type' => 'textarea', 'label' => 'Disclaimer text', 'default' => 'Financing terms, rates, and availability vary and are subject to credit approval. Ask a team member for current details.' ),
					),
				),
				'promo_blocks' => array(
					'label'  => 'Promo Blocks',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading (leave empty to hide)', 'default' => '' ),
						'blocks'  => array(
							'type'            => 'repeater',
							'label'           => 'Blocks',
							'title_field'     => 'heading',
							'add_button_text' => 'Add block',
							'min'             => 0,
							'block_fields'    => array(
								'image'        => array( 'type' => 'image', 'label' => 'Image', 'default' => '' ),
								'heading'      => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'New offer' ),
								'body'         => array( 'type' => 'textarea', 'label' => 'Body text (optional)', 'default' => '' ),
								'button_text'  => array( 'type' => 'text', 'label' => 'Button text', 'default' => 'View Now' ),
								'button_link'  => array( 'type' => 'url', 'label' => 'Button link', 'default' => '' ),
								'open_new_tab' => array( 'type' => 'checkbox', 'label' => 'Open link in a new tab', 'default' => false ),
							),
							'default_blocks'  => array(),
						),
					),
				),
				'faq' => array(
					'label'  => 'FAQ',
					'fields' => array(
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Financing FAQ' ),
						'items'   => array(
							'type'            => 'repeater',
							'label'           => 'Questions',
							'title_field'     => 'question',
							'add_button_text' => 'Add question',
							'min'             => 0,
							'block_fields'    => array(
								'question' => array( 'type' => 'text', 'label' => 'Question', 'default' => 'New question' ),
								'answer'   => array( 'type' => 'textarea', 'label' => 'Answer', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'question' => 'Does checking my rate affect my credit score?', 'answer' => 'Pre-qualification is a soft check and won’t affect your score. A hard check only happens if you formally apply.' ),
								array( 'question' => 'Is there a down payment required?', 'answer' => 'No down payment is required on any of our promotional plans.' ),
								array( 'question' => 'What happens if I pay off early?', 'answer' => 'No prepayment penalties — pay it off anytime and stop accruing interest immediately.' ),
							),
						),
					),
				),
				'cta_banner' => array(
					'label'  => 'CTA Banner',
					'fields' => array(
						'heading'     => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Have questions about financing?' ),
						'body'        => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => 'Reach out and our team will walk you through your options.' ),
						'button_text' => array( 'type' => 'text', 'label' => 'Button text', 'default' => 'Contact Us' ),
						'button_link' => array( 'type' => 'url', 'label' => 'Button link', 'default' => '/contact/' ),
					),
				),
			),
			'default_order' => array( 'hero', 'financing_cards', 'promo_blocks', 'faq', 'cta_banner' ),
		),

		'contact' => array(
			'label'    => 'Contact Page',
			'sections' => array(
				'intro' => array(
					'label'  => 'Intro',
					'fields' => array(
						'eyebrow' => array( 'type' => 'text', 'label' => 'Eyebrow tag', 'default' => "WE'RE HERE TO HELP" ),
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'Contact us' ),
						'body'    => array( 'type' => 'textarea', 'label' => 'Body text', 'default' => 'Questions about a product, service, or reservation? Reach out and our team will get back to you.' ),
					),
				),
				'locations' => array(
					'label'  => 'Store Locations',
					'fields' => array(
						'heading'         => array( 'type' => 'text', 'label' => 'Heading (leave empty to hide)', 'default' => '' ),
						'directions_text' => array( 'type' => 'text', 'label' => 'Directions link text', 'default' => 'Get Directions →' ),
						'items'           => array(
							'type'            => 'repeater',
							'label'           => 'Locations',
							'title_field'     => 'name',
							'add_button_text' => 'Add location',
							'min'             => 0,
							'block_fields'    => array(
								'name'            => array( 'type' => 'text', 'label' => 'Location name', 'default' => 'New location' ),
								'address'         => array( 'type' => 'textarea', 'label' => 'Address', 'default' => '' ),
								'phone'           => array( 'type' => 'text', 'label' => 'Phone', 'default' => '' ),
								'hours'           => array( 'type' => 'text', 'label' => 'Hours (one line)', 'default' => '' ),
								'directions_link' => array( 'type' => 'url', 'label' => 'Directions link (optional)', 'default' => '' ),
							),
							'default_blocks'  => array(
								array(
									'name'            => 'Millbrook — Main Store',
									'address'         => "412 Industrial Pkwy\nMillbrook, OH 43920",
									'phone'           => '(555) 019-2244',
									'hours'           => 'Mon–Fri 8am–6pm · Sat 8am–4pm · Sun Closed',
									'directions_link' => '',
								),
								array(
									'name'            => 'Fairview Heights — Branch',
									'address'         => "88 Ridge Rd\nFairview Heights, OH 43955",
									'phone'           => '(555) 044-7710',
									'hours'           => 'Mon–Fri 9am–5:30pm · Sat 9am–2pm · Sun Closed',
									'directions_link' => '',
								),
							),
						),
					),
				),
				'form_info' => array(
					'label'  => 'Form & Contact Info',
					'fields' => array(
						'name_label'         => array( 'type' => 'text', 'label' => 'Name field label', 'default' => 'Name' ),
						'name_placeholder'   => array( 'type' => 'text', 'label' => 'Name field placeholder', 'default' => 'Your full name' ),
						'email_label'        => array( 'type' => 'text', 'label' => 'Email field label', 'default' => 'Email' ),
						'email_placeholder'  => array( 'type' => 'text', 'label' => 'Email field placeholder', 'default' => 'you@example.com' ),
						'phone_label'        => array( 'type' => 'text', 'label' => 'Phone field label', 'default' => 'Phone' ),
						'phone_placeholder'  => array( 'type' => 'text', 'label' => 'Phone field placeholder', 'default' => '(555) 555-5555' ),
						'message_label'      => array( 'type' => 'text', 'label' => 'Message field label', 'default' => 'Message' ),
						'message_placeholder' => array( 'type' => 'text', 'label' => 'Message field placeholder', 'default' => 'How can we help?' ),
						'send_button_text'  => array( 'type' => 'text', 'label' => 'Send button text', 'default' => 'Send message' ),
						'address_label'     => array( 'type' => 'text', 'label' => 'Address label', 'default' => 'address' ),
						'address_text'      => array( 'type' => 'textarea', 'label' => 'Address text', 'default' => "412 Industrial Pkwy\nMillbrook, OH 43920" ),
						'phone_email_label' => array( 'type' => 'text', 'label' => 'Phone & email label', 'default' => 'phone & email' ),
						'phone_email_text'  => array( 'type' => 'textarea', 'label' => 'Phone & email text', 'default' => "(555) 019-2244\nsales@vss-example.com" ),
						'hours_label'       => array( 'type' => 'text', 'label' => 'Hours label', 'default' => 'store hours' ),
						'hours_text'        => array( 'type' => 'textarea', 'label' => 'Hours text', 'default' => "Mon–Fri: 8am – 6pm\nSaturday: 8am – 4pm\nSunday: Closed" ),
					),
				),
			),
			'default_order' => array( 'intro', 'locations', 'form_info' ),
		),

		'about' => array(
			'label'    => 'About Page',
			'sections' => array(
				'intro' => array(
					'label'  => 'Intro',
					'fields' => array(
						'eyebrow' => array( 'type' => 'text', 'label' => 'Eyebrow tag', 'default' => 'OUR STORY' ),
						'heading' => array( 'type' => 'text', 'label' => 'Heading', 'default' => 'About ' . get_bloginfo( 'name' ) ),
						'body1'   => array( 'type' => 'textarea', 'label' => 'Paragraph 1', 'default' => get_bloginfo( 'name' ) . ' is a family-run outdoor power equipment dealer serving Millbrook and the surrounding area since 1994. We stock mowers, chainsaws, trimmers, generators, and snow blowers from trusted brands, and back every sale with in-house service.' ),
						'body2'   => array( 'type' => 'textarea', 'label' => 'Paragraph 2', 'default' => "Inventory and pricing shown online reflect what's actually on our floor. Reserve an item and we'll hold it for 24 hours while you finalize your visit." ),
					),
				),
				'features' => array(
					'label'  => 'Feature Cards',
					'fields' => array(
						'cards' => array(
							'type'            => 'repeater',
							'label'           => 'Cards',
							'title_field'     => 'title',
							'add_button_text' => 'Add card',
							'min'             => 1,
							'block_fields'    => array(
								'title' => array( 'type' => 'text', 'label' => 'Title', 'default' => 'New card' ),
								'desc'  => array( 'type' => 'textarea', 'label' => 'Description', 'default' => '' ),
							),
							'default_blocks'  => array(
								array( 'title' => 'Accurate inventory', 'desc' => "Stock counts reflect what's on our floor, updated regularly." ),
								array( 'title' => 'Genuine parts', 'desc' => 'Manufacturer-authorized parts and service for every brand we carry.' ),
								array( 'title' => 'Local service', 'desc' => 'Tune-ups, repairs, and reservations handled by our Millbrook team.' ),
							),
						),
					),
				),
				'hours_banner' => array(
					'label'  => 'Hours Banner',
					'fields' => array(
						'label' => array( 'type' => 'text', 'label' => 'Label', 'default' => 'store hours' ),
						'text'  => array( 'type' => 'text', 'label' => 'Hours text', 'default' => 'Mon–Fri: 8am – 6pm  ·  Saturday: 8am – 4pm  ·  Sunday: Closed' ),
					),
				),
				'cta_links' => array(
					'label'  => 'CTA Links',
					'fields' => array(
						'links' => array(
							'type'            => 'repeater',
							'label'           => 'Links',
							'title_field'     => 'text',
							'add_button_text' => 'Add link',
							'min'             => 0,
							'block_fields'    => array(
								'text' => array( 'type' => 'text', 'label' => 'Text', 'default' => 'New link' ),
								'href' => array( 'type' => 'url', 'label' => 'URL', 'default' => '/' ),
							),
							'default_blocks'  => array(
								array( 'text' => 'Service', 'href' => '/service/' ),
								array( 'text' => 'Financing', 'href' => '/financing/' ),
								array( 'text' => 'Contact Us', 'href' => '/contact/' ),
							),
						),
					),
				),
			),
			'default_order' => array( 'intro', 'features', 'hours_banner', 'cta_links' ),
		),
	);
}
