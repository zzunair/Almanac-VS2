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

<?php
$topbar_left  = get_theme_mod( 'vss_header_top_bar_left', 'Open today · 8am–6pm &nbsp;·&nbsp; (123) 456-7890' );
$topbar_right = get_theme_mod( 'vss_header_top_bar_right', "Reserve online, no payment needed — we'll hold it 24 hrs" );
$reserve_text = get_theme_mod( 'vss_header_reserve_button_text', 'Reserve In-Store' );
$reserve_link = get_theme_mod( 'vss_header_reserve_button_link', '/shop/' );
?>
<header class="tw-header">
	<div class="tw-topbar">
		<span class="tw-topbar-left"><?php echo wp_kses_post( $topbar_left ); ?></span>
		<span class="tw-topbar-right"><?php echo esc_html( $topbar_right ); ?></span>
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

		<div class="tw-nav-backdrop" aria-hidden="true"></div>

		<div class="tw-header-controls" id="tw-header-controls">
			<div class="tw-drawer-head tw-drawer-only">
				<span class="tw-drawer-kicker">Menu</span>
				<button type="button" class="tw-drawer-close" aria-label="Close menu">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
				</button>
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
			<div class="tw-drawer-foot tw-drawer-only">
				<?php if ( $reserve_text ) : ?>
					<a href="<?php echo esc_url( $reserve_link ); ?>" class="tw-btn tw-btn-primary tw-drawer-reserve"><?php echo esc_html( $reserve_text ); ?></a>
				<?php endif; ?>
				<?php if ( $topbar_left || $topbar_right ) : ?>
					<div class="tw-drawer-info">
						<?php if ( $topbar_left ) : ?>
							<p class="tw-drawer-info-main"><?php echo wp_kses_post( $topbar_left ); ?></p>
						<?php endif; ?>
						<?php if ( $topbar_right ) : ?>
							<p class="tw-drawer-info-note"><?php echo esc_html( $topbar_right ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="tw-header-actions">
			<?php if ( $reserve_text ) : ?>
				<a href="<?php echo esc_url( $reserve_link ); ?>" class="tw-btn tw-btn-outline tw-header-reserve"><?php echo esc_html( $reserve_text ); ?></a>
			<?php endif; ?>
			<?php vss_cart_link(); ?>
			<button type="button" class="tw-search-open" aria-expanded="false" aria-controls="tw-search-overlay" aria-label="<?php echo esc_attr( get_theme_mod( 'vss_header_search_placeholder', 'Search products' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.2-3.2"/></svg>
			</button>
		</div>
	</div>
</header>

<div class="tw-search-overlay" id="tw-search-overlay" hidden>
	<div class="tw-search-dialog" role="dialog" aria-modal="true" aria-labelledby="tw-search-dialog-title">
		<div class="tw-search-dialog-head">
			<p class="tw-search-kicker" id="tw-search-dialog-title">Search products</p>
			<button type="button" class="tw-search-close" aria-label="Close search">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>
		<div class="tw-search-wrap">
			<form class="tw-search" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search" autocomplete="off">
				<span class="tw-search-field-icon" aria-hidden="true">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.2-3.2"/></svg>
				</span>
				<input type="text" name="s" placeholder="<?php echo esc_attr( get_theme_mod( 'vss_header_search_placeholder', 'Search products' ) ); ?>" <?php echo get_theme_mod( 'vss_header_search_suggestions', true ) ? 'data-tw-live-search="1"' : ''; ?> />
				<input type="hidden" name="post_type" value="product" />
				<button type="submit"><?php echo esc_html( get_theme_mod( 'vss_header_search_button_text', 'Search' ) ); ?></button>
			</form>
			<p class="tw-search-hint">Results appear as you type</p>
			<?php if ( get_theme_mod( 'vss_header_search_suggestions', true ) ) : ?>
				<div class="tw-search-suggestions" hidden></div>
			<?php endif; ?>
		</div>
	</div>
</div>
