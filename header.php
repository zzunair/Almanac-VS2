<?php
/**
 * The header for the theme -- matches Header.dc.html.
 */
$current_page = '';
if ( is_front_page() ) {
	$current_page = 'home';
} elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
	$current_page = 'shop';
} elseif ( is_singular( 'product' ) ) {
	$current_page = 'shop';
} elseif ( is_page( 'service' ) ) {
	$current_page = 'service';
} elseif ( is_page( 'financing' ) ) {
	$current_page = 'financing';
} elseif ( is_page( 'contact' ) ) {
	$current_page = 'contact';
} elseif ( is_page( 'about' ) ) {
	$current_page = 'about';
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="tw-header">
	<div class="tw-topbar">
		<span><?php echo wp_kses_post( get_theme_mod( 'vss_header_top_bar_left', 'Open today · 8am–6pm &nbsp;·&nbsp; (555) 019-2244' ) ); ?></span>
		<span><?php echo esc_html( get_theme_mod( 'vss_header_top_bar_right', "Reserve online, no payment needed — we'll hold it 24 hrs" ) ); ?></span>
	</div>
	<div class="tw-header-row">
		<?php $logo_image = get_theme_mod( 'vss_header_logo_image', '' ); ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="tw-logo">
			<?php if ( $logo_image ) : ?>
				<?php
				$logo_max_width  = max( 1, (int) get_theme_mod( 'vss_header_logo_max_width', 180 ) );
				$logo_max_height = max( 1, (int) get_theme_mod( 'vss_header_logo_max_height', 50 ) );
				?>
				<img src="<?php echo esc_url( $logo_image ); ?>" alt="<?php echo esc_attr( get_theme_mod( 'vss_header_logo_text', get_bloginfo( 'name' ) ) ); ?>" class="tw-logo-image" style="max-width:<?php echo $logo_max_width; ?>px;max-height:<?php echo $logo_max_height; ?>px;" />
			<?php else : ?>
				<span class="tw-logo-mark" aria-hidden="true">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--tw-dark)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18l5-9 4 6 3-4 6 7"/></svg>
				</span>
				<span class="tw-logo-lockup">
					<span class="tw-logo-text"><?php echo esc_html( get_theme_mod( 'vss_header_logo_text', get_bloginfo( 'name' ) ) ); ?></span>
					<?php $tagline = get_theme_mod( 'vss_header_logo_tagline', 'Power Equipment' ); ?>
					<?php if ( $tagline ) : ?>
						<span class="tw-logo-tagline"><?php echo esc_html( $tagline ); ?></span>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</a>

		<button type="button" class="tw-nav-toggle" aria-expanded="false" aria-controls="tw-header-controls">
			<span class="tw-nav-toggle-bar"></span>
			<span class="tw-nav-toggle-bar"></span>
			<span class="tw-nav-toggle-bar"></span>
			<span class="screen-reader-text">Menu</span>
		</button>

		<div class="tw-header-controls" id="tw-header-controls">
			<div class="tw-search-wrap">
				<form class="tw-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" autocomplete="off">
					<input type="text" name="s" placeholder="<?php echo esc_attr( get_theme_mod( 'vss_header_search_placeholder', 'Search products' ) ); ?>" <?php echo get_theme_mod( 'vss_header_search_suggestions', true ) ? 'data-tw-live-search="1"' : ''; ?> />
					<input type="hidden" name="post_type" value="product" />
					<button type="submit"><?php echo esc_html( get_theme_mod( 'vss_header_search_button_text', 'Search' ) ); ?></button>
				</form>
				<?php if ( get_theme_mod( 'vss_header_search_suggestions', true ) ) : ?>
					<div class="tw-search-suggestions" hidden></div>
				<?php endif; ?>
			</div>
			<nav class="tw-nav" aria-label="Primary">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'depth'          => 1,
						)
					);
					?>
				<?php else : ?>
					<ul>
						<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo 'home' === $current_page ? 'is-active' : ''; ?>">Home</a></li>
						<li><a href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>" class="<?php echo 'shop' === $current_page ? 'is-active' : ''; ?>">Shop</a></li>
						<li><a href="<?php echo esc_url( home_url( '/service/' ) ); ?>" class="<?php echo 'service' === $current_page ? 'is-active' : ''; ?>">Service</a></li>
						<li><a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>" class="<?php echo 'financing' === $current_page ? 'is-active' : ''; ?>">Financing</a></li>
						<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="<?php echo 'contact' === $current_page ? 'is-active' : ''; ?>">Contact</a></li>
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>" class="<?php echo 'about' === $current_page ? 'is-active' : ''; ?>">About</a></li>
					</ul>
				<?php endif; ?>
			</nav>
		</div>

		<?php $reserve_text = get_theme_mod( 'vss_header_reserve_button_text', 'Reserve In-Store' ); ?>
		<?php if ( $reserve_text ) : ?>
			<a href="<?php echo esc_url( get_theme_mod( 'vss_header_reserve_button_link', '/shop/' ) ); ?>" class="tw-btn tw-btn-outline tw-header-reserve"><?php echo esc_html( $reserve_text ); ?></a>
		<?php endif; ?>
	</div>
</header>
