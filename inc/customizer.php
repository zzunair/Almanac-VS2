<?php
/**
 * Registers the Customizer panels/sections/controls for:
 *  - global Header & Footer content
 *  - every page's section order (drag/enable/disable) control
 *  - every page's per-section content fields
 *
 * All of this is data-driven from vss_section_defs() so adding a
 * new section only requires editing inc/section-defs.php and the matching
 * template-parts/sections/{page}-{section}.php file.
 */

function vss_sanitize_section_order( $value ) {
	$decoded = json_decode( $value, true );
	if ( ! is_array( $decoded ) ) {
		return null;
	}
	$clean = array();
	foreach ( $decoded as $row ) {
		if ( ! isset( $row['id'] ) || ! preg_match( '/^[a-z0-9_]+$/', $row['id'] ) ) {
			continue;
		}
		$clean[] = array(
			'id'      => sanitize_key( $row['id'] ),
			'enabled' => ! empty( $row['enabled'] ),
		);
	}
	return wp_json_encode( $clean );
}

function vss_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * '' means "Automatic" (the theme's existing auto-generated list / fallback
 * menu location) -- anything else must be a real, still-existing menu ID.
 */
function vss_sanitize_nav_menu_choice( $value ) {
	if ( '' === $value ) {
		return '';
	}
	return wp_get_nav_menu_object( (int) $value ) ? (string) (int) $value : '';
}

/**
 * All menus the site admin has built under Appearance > Menus, for the
 * Footer "which menu should this column show" dropdowns -- so picking one
 * is a single Customizer control instead of a trip to Appearance > Menus >
 * Manage Locations.
 */
function vss_nav_menu_choices() {
	$choices = array( '' => 'Automatic' );
	$menus   = wp_get_nav_menus();
	if ( $menus ) {
		foreach ( $menus as $menu ) {
			$choices[ (string) $menu->term_id ] = $menu->name;
		}
	}
	return $choices;
}

/**
 * Only ever allow one of the curated Google Fonts through -- the value
 * gets printed raw (unescaped) inside a <style> block, so it must be
 * restricted to a known-safe whitelist rather than merely HTML-escaped.
 */
function vss_sanitize_font_family( $value ) {
	$choices = array_keys( vss_font_choices() );
	return in_array( $value, $choices, true ) ? $value : 'Manrope';
}

/**
 * Site-wide text size. Every font-size in style.css (and in the templates)
 * is written in rem, so this one setting scales all of them together --
 * no need to go through and bump sizes on individual elements one by one.
 */
function vss_font_scale_choices() {
	return array(
		'87.5'  => 'Small',
		'100'   => 'Default',
		'112.5' => 'Large',
		'125'   => 'Extra large',
	);
}

function vss_sanitize_font_scale( $value ) {
	$choices = array_keys( vss_font_scale_choices() );
	return in_array( $value, $choices, true ) ? $value : '100';
}

/**
 * These IDs get printed raw inside a <script> src attribute or inline
 * gtag() call, so whitelist the exact expected shape rather than merely
 * HTML-escaping.
 */
function vss_sanitize_ga4_id( $value ) {
	$value = trim( (string) $value );
	return preg_match( '/^G-[A-Z0-9]+$/', $value ) ? $value : '';
}

function vss_sanitize_gtm_id( $value ) {
	$value = trim( (string) $value );
	return preg_match( '/^GTM-[A-Z0-9]+$/', $value ) ? $value : '';
}

function vss_field_sanitize_callback( $type ) {
	switch ( $type ) {
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'checkbox':
			return 'vss_sanitize_checkbox';
		case 'number':
		case 'range':
			return 'intval';
		case 'decimal':
			return 'floatval';
		case 'color':
			return 'sanitize_hex_color';
		case 'image':
		case 'url':
			return 'esc_url_raw';
		default:
			return 'sanitize_text_field';
	}
}

/**
 * Builds a sanitize callback for one repeater field's block schema --
 * decodes the JSON block array and runs each block's values through the
 * scalar sanitizer that matches its declared type.
 */
function vss_make_repeater_sanitizer( $block_fields ) {
	return function ( $value ) use ( $block_fields ) {
		$decoded = json_decode( $value, true );
		if ( ! is_array( $decoded ) ) {
			return null;
		}
		$clean = array();
		foreach ( $decoded as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			$clean_block = array();
			foreach ( $block_fields as $key => $field ) {
				if ( ! array_key_exists( $key, $block ) ) {
					continue;
				}
				$clean_block[ $key ] = call_user_func( vss_field_sanitize_callback( $field['type'] ), $block[ $key ] );
			}
			$clean[] = $clean_block;
		}
		return wp_json_encode( $clean );
	};
}

/**
 * Adds the setting + control for a single section field.
 */
function vss_add_field_control( $wp_customize, $setting_id, $customizer_section, $field ) {
	if ( 'repeater' === $field['type'] ) {
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => wp_json_encode( isset( $field['default_blocks'] ) ? $field['default_blocks'] : array() ),
				'sanitize_callback' => vss_make_repeater_sanitizer( $field['block_fields'] ),
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new VSS_Repeater_Control(
				$wp_customize,
				$setting_id,
				array(
					'label'           => $field['label'],
					'section'         => $customizer_section,
					'block_fields'    => $field['block_fields'],
					'title_field'     => isset( $field['title_field'] ) ? $field['title_field'] : '',
					'add_button_text' => isset( $field['add_button_text'] ) ? $field['add_button_text'] : 'Add block',
					'min_blocks'      => isset( $field['min'] ) ? (int) $field['min'] : 0,
				)
			)
		);
		return;
	}

	$wp_customize->add_setting(
		$setting_id,
		array(
			'default'           => $field['default'],
			'sanitize_callback' => vss_field_sanitize_callback( $field['type'] ),
			'transport'         => 'refresh',
		)
	);

	if ( 'color' === $field['type'] ) {
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$setting_id,
				array( 'label' => $field['label'], 'section' => $customizer_section )
			)
		);
		return;
	}

	if ( 'image' === $field['type'] ) {
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				$setting_id,
				array( 'label' => $field['label'], 'section' => $customizer_section )
			)
		);
		return;
	}

	$control_type = in_array( $field['type'], array( 'text', 'textarea', 'checkbox', 'number', 'url', 'range' ), true ) ? $field['type'] : 'text';

	$control_args = array(
		'label'   => $field['label'],
		'section' => $customizer_section,
		'type'    => $control_type,
	);
	if ( 'range' === $control_type && ! empty( $field['input_attrs'] ) ) {
		$control_args['input_attrs'] = $field['input_attrs'];
	}

	$wp_customize->add_control( $setting_id, $control_args );
}

/**
 * Returns the active_callback that makes a page's panel appear only while
 * that page is the one being previewed -- the Shopify-style "you only see
 * the settings for the page you're looking at" behavior. WordPress
 * re-evaluates this against the current preview URL, including when the
 * client clicks a link inside the live preview to a different page.
 */
function vss_panel_active_callback( $page ) {
	switch ( $page ) {
		case 'home':
			return 'is_front_page';
		case 'shop':
			return function () {
				return function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
			};
		case 'product':
			return function () {
				return is_singular( 'product' );
			};
		default:
			return function () use ( $page ) {
				return is_page( $page );
			};
	}
}

function vss_customize_register( $wp_customize ) {
	require_once get_template_directory() . '/inc/class-section-order-control.php';
	require_once get_template_directory() . '/inc/class-repeater-control.php';
	// Not calling register_control_type(): that call causes core to look for
	// a JS Underscore template (tmpl-customize-control-{type}-content) and,
	// if found (even an empty default one), overwrite this control's real
	// PHP-rendered content with it. This control renders entirely server side;
	// only the JS Control *constructor* (registered in customizer-controls.js
	// via api.controlConstructor) is needed, for the sortable/enable behavior.

	/* ---------------- Site-Wide Settings (everything not tied to one page) ---------------- */
	$wp_customize->add_panel( 'vss_global', array( 'title' => 'Site-Wide Settings', 'priority' => 5 ) );

	/* ---------------- Colors (global) ---------------- */
	$wp_customize->add_section( 'vss_colors', array( 'title' => 'Colors', 'panel' => 'vss_global', 'priority' => 10 ) );

	$color_fields = array(
		'accent'      => array( 'label' => 'Buttons & Accent', 'default' => '#f2600c' ),
		'accent_dark' => array( 'label' => 'Links & Eyebrow Text', 'default' => '#c94a05' ),
		'text'        => array( 'label' => 'Headings & Body Text', 'default' => '#171310' ),
		'text_muted'  => array( 'label' => 'Muted / Secondary Text', 'default' => '#6e655a' ),
		'bg'          => array( 'label' => 'Page Background', 'default' => '#faf6f0' ),
		'bg_soft'     => array( 'label' => 'Soft Section Background', 'default' => '#f1eae0' ),
		'border'      => array( 'label' => 'Card & Input Borders', 'default' => '#e4dacb' ),
	);
	foreach ( $color_fields as $key => $field ) {
		$setting_id = 'vss_color_' . $key;
		$wp_customize->add_setting(
			$setting_id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$setting_id,
				array( 'label' => $field['label'], 'section' => 'vss_colors' )
			)
		);
	}

	/* ---------------- Typography (global) ---------------- */
	$wp_customize->add_section( 'vss_typography', array( 'title' => 'Typography', 'panel' => 'vss_global', 'priority' => 11 ) );

	$wp_customize->add_setting(
		'vss_font_family',
		array( 'default' => 'Manrope', 'sanitize_callback' => 'vss_sanitize_font_family', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'vss_font_family',
		array(
			'label'   => 'Site Font',
			'section' => 'vss_typography',
			'type'    => 'select',
			'choices' => vss_font_choices(),
		)
	);

	$wp_customize->add_setting(
		'vss_font_scale',
		array( 'default' => '100', 'sanitize_callback' => 'vss_sanitize_font_scale', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'vss_font_scale',
		array(
			'label'       => 'Text Size',
			'description' => 'Scales the font size of every element on the site together.',
			'section'     => 'vss_typography',
			'type'        => 'select',
			'choices'     => vss_font_scale_choices(),
		)
	);

	$wp_customize->add_setting(
		'vss_nav_text_style',
		array( 'default' => 'uppercase', 'sanitize_callback' => 'sanitize_key', 'transport' => 'postMessage' )
	);
	$wp_customize->add_control(
		'vss_nav_text_style',
		array(
			'label'       => 'Navigation Text Style',
			'description' => 'How the "home / shop / service / ..." menu labels are cased.',
			'section'     => 'vss_typography',
			'type'        => 'select',
			'choices'     => array(
				'none'       => 'As typed (Title Case)',
				'uppercase'  => 'UPPERCASE',
				'lowercase'  => 'lowercase',
				'capitalize' => 'Capitalize Each Word',
			),
		)
	);

	/* ---------------- Announcement Bar ---------------- */
	$wp_customize->add_section( 'vss_announcement', array( 'title' => 'Announcement Bar', 'panel' => 'vss_global', 'priority' => 29 ) );

	$announcement_fields = array(
		'top_bar_left'  => array( 'label' => 'Left text', 'default' => 'Open today · 8am–6pm &nbsp;·&nbsp; (555) 019-2244', 'type' => 'text' ),
		'top_bar_right' => array( 'label' => 'Right text', 'default' => "Reserve online, no payment needed — we'll hold it 24 hrs", 'type' => 'text' ),
		'topbar_bg'     => array( 'label' => 'Background color', 'default' => '#171310', 'type' => 'color' ),
		'topbar_text'   => array( 'label' => 'Text color', 'default' => '#efc9a8', 'type' => 'color' ),
	);
	foreach ( $announcement_fields as $key => $field ) {
		vss_add_field_control( $wp_customize, 'vss_header_' . $key, 'vss_announcement', $field );
	}

	/* ---------------- Header ---------------- */
	$wp_customize->add_section( 'vss_header', array( 'title' => 'Header', 'panel' => 'vss_global', 'priority' => 30 ) );

	$header_fields = array(
		'logo_image'           => array( 'label' => 'Logo image (leave empty to keep the text logo below)', 'default' => '', 'type' => 'image' ),
		'logo_max_width'       => array( 'label' => 'Logo image max width (px)', 'default' => 180, 'type' => 'range', 'input_attrs' => array( 'min' => 40, 'max' => 400, 'step' => 5 ) ),
		'logo_max_height'      => array( 'label' => 'Logo image max height (px)', 'default' => 50, 'type' => 'range', 'input_attrs' => array( 'min' => 20, 'max' => 150, 'step' => 5 ) ),
		'logo_text'            => array( 'label' => 'Logo text', 'default' => get_bloginfo( 'name' ), 'type' => 'text' ),
		'logo_tagline'         => array( 'label' => 'Logo tagline (small text under the logo, optional)', 'default' => 'Power Equipment', 'type' => 'text' ),
		'search_placeholder'   => array( 'label' => 'Search placeholder', 'default' => 'Search products', 'type' => 'text' ),
		'search_button_text'   => array( 'label' => 'Search button text', 'default' => 'Search', 'type' => 'text' ),
		'search_suggestions'   => array( 'label' => 'Show live suggestions while typing in search', 'default' => true, 'type' => 'checkbox' ),
		'reserve_button_text'  => array( 'label' => '"Reserve" button text (leave empty to hide)', 'default' => 'Reserve In-Store', 'type' => 'text' ),
		'reserve_button_link'  => array( 'label' => '"Reserve" button link', 'default' => '/shop/', 'type' => 'url' ),
	);
	foreach ( $header_fields as $key => $field ) {
		vss_add_field_control( $wp_customize, 'vss_header_' . $key, 'vss_header', $field );
	}

	/* ---------------- Footer ---------------- */
	$wp_customize->add_section( 'vss_footer', array( 'title' => 'Footer', 'panel' => 'vss_global', 'priority' => 31 ) );

	$menu_choices = vss_nav_menu_choices();
	foreach ( array(
		'footer_shop_menu'    => 'Shop column menu',
		'footer_company_menu' => 'Company column menu',
	) as $key => $label ) {
		$wp_customize->add_setting(
			'vss_' . $key,
			array(
				'default'           => '',
				'sanitize_callback' => 'vss_sanitize_nav_menu_choice',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'vss_' . $key,
			array(
				'label'       => $label,
				'description' => 'Automatic' === reset( $menu_choices ) && 1 === count( $menu_choices )
					? 'Create a menu under Appearance > Menus first, then pick it here.'
					: 'Choose a menu built under Appearance > Menus, or leave on Automatic for the default list.',
				'section'     => 'vss_footer',
				'type'        => 'select',
				'choices'     => $menu_choices,
			)
		);
	}

	$footer_fields = array(
		'company_name'   => array( 'label' => 'Company name', 'default' => get_bloginfo( 'name' ), 'type' => 'text' ),
		'address'        => array( 'label' => 'Address', 'default' => "412 Industrial Pkwy\nMillbrook, OH 43920", 'type' => 'textarea' ),
		'phone_email'    => array( 'label' => 'Phone & email', 'default' => "(555) 019-2244\nsales@vss-example.com", 'type' => 'textarea' ),
		'hours_heading'  => array( 'label' => 'Store hours column heading', 'default' => 'Store Hours', 'type' => 'text' ),
		'hours'          => array( 'label' => 'Store hours', 'default' => "Mon–Fri · 8am–6pm\nSaturday · 8am–4pm\nSunday · Closed", 'type' => 'textarea' ),
		'shop_heading'   => array( 'label' => 'Shop column heading', 'default' => 'Shop', 'type' => 'text' ),
		'company_heading' => array( 'label' => 'Company column heading', 'default' => 'Company', 'type' => 'text' ),
		'note'           => array( 'label' => 'Footer note', 'default' => 'Service & repair scheduling available in-store. Financing options available on request. Delivery & curbside pickup offered on most items.', 'type' => 'textarea' ),
		'legal_right'    => array( 'label' => 'Legal line (right side)', 'default' => 'Design mockup — no transactions are processed on this site.', 'type' => 'text' ),
	);
	foreach ( $footer_fields as $key => $field ) {
		vss_add_field_control( $wp_customize, 'vss_footer_' . $key, 'vss_footer', $field );
	}

	/* ---------------- Footer Newsletter ---------------- */
	$wp_customize->add_section( 'vss_newsletter', array( 'title' => 'Footer Newsletter', 'panel' => 'vss_global', 'priority' => 32 ) );

	$newsletter_fields = array(
		'enabled'     => array( 'label' => 'Show newsletter signup in footer', 'default' => true, 'type' => 'checkbox' ),
		'heading'     => array( 'label' => 'Heading', 'default' => 'Seasonal Tips & Sale Alerts', 'type' => 'text' ),
		'body'        => array( 'label' => 'Body text', 'default' => 'One email a month. No spam, unsubscribe anytime.', 'type' => 'textarea' ),
		'placeholder' => array( 'label' => 'Email field placeholder', 'default' => 'you@example.com', 'type' => 'text' ),
		'button_text' => array( 'label' => 'Button text', 'default' => 'Sign Up', 'type' => 'text' ),
	);
	foreach ( $newsletter_fields as $key => $field ) {
		vss_add_field_control( $wp_customize, 'vss_newsletter_' . $key, 'vss_newsletter', $field );
	}

	/* ---------------- Compare Products ---------------- */
	$wp_customize->add_section( 'vss_compare', array( 'title' => 'Compare Products', 'panel' => 'vss_global', 'priority' => 33 ) );

	$compare_fields = array(
		'enabled'        => array( 'label' => 'Enable "Compare" on product cards', 'default' => true, 'type' => 'checkbox' ),
		'checkbox_label' => array( 'label' => 'Checkbox label', 'default' => 'Compare', 'type' => 'text' ),
		'max_items'      => array( 'label' => 'Max items to compare at once', 'default' => 3, 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 4 ) ),
		'bar_text'       => array( 'label' => 'Selection bar text (use {count})', 'default' => '{count} selected for comparison', 'type' => 'text' ),
		'clear_text'     => array( 'label' => 'Clear button text', 'default' => 'Clear', 'type' => 'text' ),
		'button_text'    => array( 'label' => 'Compare button text', 'default' => 'Compare Now', 'type' => 'text' ),
		'modal_heading'  => array( 'label' => 'Comparison window heading', 'default' => 'Compare Products', 'type' => 'text' ),
		'max_text'       => array( 'label' => 'Max-reached message (use {max})', 'default' => 'You can compare up to {max} items at a time.', 'type' => 'text' ),
		'view_text'      => array( 'label' => '"View product" link text', 'default' => 'View Product', 'type' => 'text' ),
	);
	foreach ( $compare_fields as $key => $field ) {
		vss_add_field_control( $wp_customize, 'vss_compare_' . $key, 'vss_compare', $field );
	}

	/* ---------------- Integrations ---------------- */
	$wp_customize->add_panel( 'vss_integrations', array( 'title' => 'Integrations', 'priority' => 6 ) );

	$wp_customize->add_section( 'vss_analytics', array( 'title' => 'Analytics', 'panel' => 'vss_integrations', 'priority' => 10 ) );

	$wp_customize->add_setting(
		'vss_ga4_id',
		array( 'default' => '', 'sanitize_callback' => 'vss_sanitize_ga4_id', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'vss_ga4_id',
		array(
			'label'       => 'GA4 Measurement ID',
			'description' => 'e.g. G-XXXXXXXXXX. Leave empty to disable Google Analytics.',
			'section'     => 'vss_analytics',
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'vss_gtm_id',
		array( 'default' => '', 'sanitize_callback' => 'vss_sanitize_gtm_id', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'vss_gtm_id',
		array(
			'label'       => 'Google Tag Manager Container ID',
			'description' => 'e.g. GTM-XXXXXXX. Leave empty to disable GTM.',
			'section'     => 'vss_analytics',
			'type'        => 'text',
		)
	);

	$wp_customize->add_section( 'vss_targetcrm', array( 'title' => 'TargetCRM Widget', 'panel' => 'vss_integrations', 'priority' => 20 ) );

	$wp_customize->add_setting(
		'vss_targetcrm_client_id',
		array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh' )
	);
	$wp_customize->add_control(
		'vss_targetcrm_client_id',
		array(
			'label'       => 'TargetCRM Client ID',
			'description' => 'Loads the TargetCRM widget with this client ID. Leave empty to disable.',
			'section'     => 'vss_targetcrm',
			'type'        => 'text',
		)
	);

	/* ---------------- Per-page sections ---------------- */
	$defs     = vss_section_defs();
	$priority = 40;

	foreach ( $defs as $page => $page_def ) {
		$panel_id = 'vss_page_' . $page;
		$wp_customize->add_panel(
			$panel_id,
			array(
				'title'           => $page_def['label'],
				'priority'        => $priority++,
				'active_callback' => vss_panel_active_callback( $page ),
			)
		);

		// Section order / enable-disable control.
		$order_section_id = $panel_id . '_order';
		$wp_customize->add_section(
			$order_section_id,
			array( 'title' => 'Section Order', 'panel' => $panel_id, 'priority' => 0 )
		);

		$order_setting_id = 'vss_' . $page . '_section_order';
		$default_order    = array_map(
			function ( $id ) {
				return array( 'id' => $id, 'enabled' => true );
			},
			$page_def['default_order']
		);

		$wp_customize->add_setting(
			$order_setting_id,
			array(
				'default'           => wp_json_encode( $default_order ),
				'sanitize_callback' => 'vss_sanitize_section_order',
				'transport'         => 'refresh',
			)
		);

		$sections_config = array();
		foreach ( $page_def['sections'] as $section_id => $section_def ) {
			$sections_config[ $section_id ] = $section_def['label'];
		}

		$wp_customize->add_control(
			new VSS_Section_Order_Control(
				$wp_customize,
				$order_setting_id,
				array(
					'label'           => 'Sections',
					'section'         => $order_section_id,
					'sections_config' => $sections_config,
				)
			)
		);

		// One Customizer section per content section, in default order.
		$sub_priority = 10;
		foreach ( $page_def['sections'] as $section_id => $section_def ) {
			if ( empty( $section_def['fields'] ) ) {
				continue;
			}

			$customizer_section_id = $panel_id . '_' . $section_id;
			$wp_customize->add_section(
				$customizer_section_id,
				array(
					'title'    => $section_def['label'],
					'panel'    => $panel_id,
					'priority' => $sub_priority++,
				)
			);

			foreach ( $section_def['fields'] as $field_id => $field ) {
				$setting_id = 'vss_' . $page . '_' . $section_id . '_' . $field_id;
				vss_add_field_control( $wp_customize, $setting_id, $customizer_section_id, $field );
			}
		}
	}
}
add_action( 'customize_register', 'vss_customize_register' );
