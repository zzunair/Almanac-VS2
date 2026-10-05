<?php
/**
 * Almanac VS theme functions.
 */

// Tied to style.css's own last-modified time so every asset that uses this
// constant (CSS, JS, the Customizer controls) auto-busts browser cache on
// each update -- no manual version bump, no "why isn't my change showing".
define( 'VSS_VERSION', '1.0.0-' . filemtime( get_template_directory() . '/style.css' ) );

require_once get_template_directory() . '/inc/section-defs.php';
require_once get_template_directory() . '/inc/template-functions.php';
require_once get_template_directory() . '/inc/customizer.php';
require_once get_template_directory() . '/inc/woocommerce.php';

function vss_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

	register_nav_menus(
		array(
			'primary'       => 'Primary Navigation',
			'footer_shop'   => 'Footer - Shop Column',
			'footer_company' => 'Footer - Company Column',
		)
	);
}
add_action( 'after_setup_theme', 'vss_setup' );

/**
 * Clean permalinks ("/shop/", "/product/name/", "/about/", ...) are assumed
 * throughout the templates. Switch to them on theme activation if the site
 * is still on the default query-string structure.
 */
function vss_flush_permalinks_on_activation() {
	if ( '' === get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
		flush_rewrite_rules();
	}
}
add_action( 'after_switch_theme', 'vss_flush_permalinks_on_activation' );

function vss_enqueue_assets() {
	$font = get_theme_mod( 'vss_font_family', 'Manrope' );
	wp_enqueue_style( 'vss-fonts', vss_google_font_url( $font ), array(), null );
	wp_enqueue_style( 'vss-style', get_stylesheet_uri(), array(), VSS_VERSION );
	wp_enqueue_script( 'vss-frontend', get_template_directory_uri() . '/assets/js/frontend.js', array(), VSS_VERSION, true );
	wp_localize_script(
		'vss-frontend',
		'vssSearch',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'vss_search' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'vss_enqueue_assets' );

/**
 * Prints the site-wide color + font CSS variables from Customizer >
 * Colors / Typography, overriding style.css's :root defaults. Runs after
 * style.css is linked (priority 20) so it always wins.
 */
function vss_output_dynamic_styles() {
	// vss_sanitize_font_family() already restricts this to the
	// curated whitelist, and sanitize_hex_color() already restricts every
	// color to a valid #rgb/#rrggbb string -- both safe to print raw
	// inside a <style> block, where HTML-entity escaping would corrupt
	// the CSS (a <style> element is raw text, its entities aren't decoded).
	$font = get_theme_mod( 'vss_font_family', 'Manrope' );
	$vars = array( '--tw-font-family' => "'" . str_replace( "'", '', $font ) . "', sans-serif" );
	foreach ( vss_color_vars() as $name => $value ) {
		if ( $value ) {
			$vars[ $name ] = $value;
		}
	}

	$topbar_bg   = get_theme_mod( 'vss_header_topbar_bg', '#171310' );
	$topbar_text = get_theme_mod( 'vss_header_topbar_text', '#efc9a8' );
	if ( $topbar_bg ) {
		$vars['--tw-topbar-bg'] = $topbar_bg;
	}
	if ( $topbar_text ) {
		$vars['--tw-topbar-text'] = $topbar_text;
	}

	// vss_sanitize_font_scale() restricts this to the whitelist of
	// numeric percentages in vss_font_scale_choices() -- safe to print raw.
	$font_scale = get_theme_mod( 'vss_font_scale', '100' );

	echo '<style id="vss-dynamic-styles">:root{' . "\n";
	foreach ( $vars as $name => $value ) {
		echo $name . ':' . $value . ";\n";
	}
	echo "}\nhtml{font-size:" . $font_scale . "%;}</style>\n";
}
add_action( 'wp_head', 'vss_output_dynamic_styles', 20 );

/**
 * Live-preview the Colors/Typography settings in the Customizer without a
 * full page reload: colors and the nav text-style just flip a CSS
 * variable / class in the preview iframe via postMessage.
 */
function vss_customize_preview_assets() {
	wp_enqueue_script(
		'vss-customizer-preview',
		get_template_directory_uri() . '/assets/js/customizer-preview.js',
		array( 'customize-preview' ),
		VSS_VERSION,
		true
	);
}
add_action( 'customize_preview_init', 'vss_customize_preview_assets' );

/**
 * Page templates are matched by the WordPress page slug (page-about.php,
 * page-service.php, ...), so make sure those pages exist and route
 * correctly even before any content has been added to them.
 */
function vss_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'vss-home';
	}
	$classes[] = 'tw-nav-style-' . get_theme_mod( 'vss_nav_text_style', 'uppercase' );
	return $classes;
}
add_filter( 'body_class', 'vss_body_classes' );

/**
 * This theme's entire product catalog (Shop, single products, featured
 * products, filters, compare) depends on WooCommerce. Front-end templates
 * guard their own wc_*() calls so a deactivated WooCommerce degrades
 * (those sections just don't render) instead of a fatal error -- this
 * notice is what actually tells the site owner why, and how to fix it.
 */
function vss_woocommerce_missing_notice() {
	if ( function_exists( 'WC' ) ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><strong>Almanac VS</strong> requires the WooCommerce plugin to show products, the Shop page, and related sections. Please <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) ); ?>">install and activate WooCommerce</a>.</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'vss_woocommerce_missing_notice' );

/**
 * GA4 / GTM / TargetCRM, all wired to Customizer > Integrations so a client
 * can plug in their own IDs without touching code. Every ID has already
 * been through a whitelist sanitize_callback (see inc/customizer.php), so
 * it's safe to print raw inside a <script> block or src attribute.
 */
function vss_output_gtm_head() {
	$gtm_id = get_theme_mod( 'vss_gtm_id', '' );
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo $gtm_id; ?>');</script>
	<?php
}
add_action( 'wp_head', 'vss_output_gtm_head', 1 );

function vss_output_gtm_body() {
	$gtm_id = get_theme_mod( 'vss_gtm_id', '' );
	if ( ! $gtm_id ) {
		return;
	}
	?>
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id; ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<?php
}
add_action( 'wp_body_open', 'vss_output_gtm_body' );

function vss_output_ga4() {
	$ga4_id = get_theme_mod( 'vss_ga4_id', '' );
	if ( ! $ga4_id ) {
		return;
	}
	?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga4_id; ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo $ga4_id; ?>');
	</script>
	<?php
}
add_action( 'wp_head', 'vss_output_ga4' );

function vss_output_targetcrm_widget() {
	$client_id = get_theme_mod( 'vss_targetcrm_client_id', '' );
	if ( ! $client_id ) {
		return;
	}
	?>
	<script src="https://app.constellationdealer.com/widget.js" id="target-crm-widget" data-client-id="<?php echo esc_attr( $client_id ); ?>" defer></script>
	<?php
}
add_action( 'wp_footer', 'vss_output_targetcrm_widget' );
