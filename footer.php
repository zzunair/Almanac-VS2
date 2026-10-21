<?php
/**
 * The footer for the theme -- matches Footer.dc.html.
 */

$address_lines     = vss_lines( get_theme_mod( 'vss_footer_address', "123 Main Street\nYour City, ST 00000" ) );
$phone_email_lines = vss_lines( get_theme_mod( 'vss_footer_phone_email', "(123) 456-7890\nhello@yourstore.com" ) );
$hours_lines       = vss_lines( get_theme_mod( 'vss_footer_hours', "Mon–Fri · 8am–6pm\nSaturday · 8am–4pm\nSunday · Closed" ) );

$shop_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
		'exclude'    => array( get_option( 'default_product_cat' ) ),
	)
);
if ( is_wp_error( $shop_terms ) ) {
	$shop_terms = array();
}
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

// A footer column has room for a handful of links, not a full catalog tree
// -- a store with 100+ categories should really assign a real menu here
// (Customizer > Footer already offers that), but the automatic fallback
// still needs to degrade gracefully instead of dumping every category.
$vss_footer_shop_limit    = 8;
$vss_footer_shop_has_more = count( $shop_terms ) > $vss_footer_shop_limit;
$shop_terms               = array_slice( $shop_terms, 0, $vss_footer_shop_limit );

// The Customizer > Footer dropdowns take priority (pick a menu right there,
// no trip to Appearance > Menus needed); a menu assigned to the
// footer_shop/footer_company location is the fallback for anyone who set
// it up the WordPress-native way instead; auto-generated content is last.
$footer_shop_menu_id    = (int) get_theme_mod( 'vss_footer_shop_menu', '' );
$footer_company_menu_id = (int) get_theme_mod( 'vss_footer_company_menu', '' );
?>
<footer class="tw-footer">
	<div class="tw-footer-grid">
		<div>
			<div class="tw-footer-brand-title"><?php echo esc_html( get_theme_mod( 'vss_footer_company_name', get_bloginfo( 'name' ) ) ); ?></div>
			<p><?php echo esc_html( implode( ', ', $address_lines ) ); ?></p>
			<p><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $phone_email_lines ) ) ); ?></p>
			<p style="color:var(--tw-dark-text-faint);"><span class="screen-reader-text"><?php echo esc_html( get_theme_mod( 'vss_footer_hours_heading', 'Store Hours' ) ); ?>: </span><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $hours_lines ) ) ); ?></p>
		</div>
		<div>
			<div class="tw-footer-col-title"><?php echo esc_html( get_theme_mod( 'vss_footer_shop_heading', 'Shop' ) ); ?></div>
			<?php if ( $footer_shop_menu_id || has_nav_menu( 'footer_shop' ) ) : ?>
				<?php
				wp_nav_menu(
					array_merge(
						array(
							'container'  => false,
							'menu_class' => 'tw-footer-links',
							'depth'      => 1,
						),
						$footer_shop_menu_id ? array( 'menu' => $footer_shop_menu_id ) : array( 'theme_location' => 'footer_shop' )
					)
				);
				?>
			<?php else : ?>
				<ul class="tw-footer-links">
					<?php if ( $shop_terms ) : ?>
						<?php foreach ( $shop_terms as $term ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
						<?php endforeach; ?>
						<?php if ( $vss_footer_shop_has_more ) : ?>
							<li><a href="<?php echo esc_url( $shop_url ); ?>">Shop all &rarr;</a></li>
						<?php endif; ?>
					<?php else : ?>
						<li><a href="<?php echo esc_url( $shop_url ); ?>">Shop all</a></li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div>
			<div class="tw-footer-col-title"><?php echo esc_html( get_theme_mod( 'vss_footer_company_heading', 'Company' ) ); ?></div>
			<?php if ( $footer_company_menu_id || has_nav_menu( 'footer_company' ) ) : ?>
				<?php
				wp_nav_menu(
					array_merge(
						array(
							'container'  => false,
							'menu_class' => 'tw-footer-links',
							'depth'      => 1,
						),
						$footer_company_menu_id ? array( 'menu' => $footer_company_menu_id ) : array( 'theme_location' => 'footer_company' )
					)
				);
				?>
			<?php else : ?>
				<ul class="tw-footer-links" style="margin-bottom:16px;">
					<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">About Us</a></li>
					<li><a href="<?php echo esc_url( home_url( '/service/' ) ); ?>">Service</a></li>
					<li><a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>">Financing</a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
				</ul>
			<?php endif; ?>
			<p class="tw-footer-note"><?php echo esc_html( get_theme_mod( 'vss_footer_note', 'Service & repair scheduling available in-store. Financing options available on request. Delivery & curbside pickup offered on most items.' ) ); ?></p>
		</div>
		<?php if ( get_theme_mod( 'vss_newsletter_enabled', true ) ) : ?>
			<div>
				<div class="tw-footer-col-title"><?php echo esc_html( get_theme_mod( 'vss_newsletter_heading', 'Seasonal Tips & Sale Alerts' ) ); ?></div>
				<p style="font-size:0.8125rem;line-height:1.6;margin:0 0 14px;color:var(--tw-dark-text-faint);"><?php echo esc_html( get_theme_mod( 'vss_newsletter_body', 'One email a month. No spam, unsubscribe anytime.' ) ); ?></p>
				<form class="tw-newsletter-form" onsubmit="return false;" style="display:flex;gap:8px;">
					<input type="email" placeholder="<?php echo esc_attr( get_theme_mod( 'vss_newsletter_placeholder', 'you@example.com' ) ); ?>" style="flex-grow:1;min-width:0;border:1px solid var(--tw-dark-border);border-radius:6px;padding:9px 12px;font-size:0.8125rem;background:var(--tw-dark-soft);color:var(--tw-dark-text-faint);" />
					<button type="submit" class="tw-btn tw-btn-primary" style="padding:9px 16px;white-space:nowrap;"><?php echo esc_html( get_theme_mod( 'vss_newsletter_button_text', 'Sign Up' ) ); ?></button>
				</form>
				<div class="tw-footer-social">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M16 8.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/><circle cx="17.2" cy="6.8" r="0.6" fill="currentColor"/></svg>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 3H6a2 2 0 0 0-2 2v2M22 8V6a2 2 0 0 0-2-2h-2M16 21h2a2 2 0 0 0 2-2v-2M2 16v2a2 2 0 0 0 2 2h2"/><path d="M8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0z"/></svg>
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M10 9l6 3-6 3V9z" fill="currentColor" stroke="none"/></svg>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<div class="tw-footer-legal">
		<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( get_theme_mod( 'vss_footer_company_name', get_bloginfo( 'name' ) ) ); ?></span>
		<span><?php echo esc_html( get_theme_mod( 'vss_footer_legal_right', 'All rights reserved.' ) ); ?></span>
	</div>
</footer>

<?php if ( get_theme_mod( 'vss_compare_enabled', true ) ) : ?>
	<div
		class="tw-compare-bar"
		id="tw-compare-bar"
		hidden
		data-max="<?php echo esc_attr( max( 2, (int) get_theme_mod( 'vss_compare_max_items', 3 ) ) ); ?>"
		data-bar-text="<?php echo esc_attr( get_theme_mod( 'vss_compare_bar_text', '{count} selected for comparison' ) ); ?>"
		data-max-text="<?php echo esc_attr( get_theme_mod( 'vss_compare_max_text', 'You can compare up to {max} items at a time.' ) ); ?>"
		data-view-text="<?php echo esc_attr( get_theme_mod( 'vss_compare_view_text', 'View Product' ) ); ?>"
	>
		<div class="tw-compare-bar-inner">
			<span class="tw-compare-bar-text" id="tw-compare-bar-text"></span>
			<div class="tw-compare-bar-actions">
				<button type="button" class="tw-compare-clear" id="tw-compare-clear"><?php echo esc_html( get_theme_mod( 'vss_compare_clear_text', 'Clear' ) ); ?></button>
				<button type="button" class="tw-btn tw-btn-primary tw-compare-open" id="tw-compare-open"><?php echo esc_html( get_theme_mod( 'vss_compare_button_text', 'Compare Now' ) ); ?></button>
			</div>
		</div>
	</div>

	<div class="tw-compare-modal" id="tw-compare-modal" hidden>
		<div class="tw-compare-modal-overlay" id="tw-compare-modal-overlay"></div>
		<div class="tw-compare-modal-panel">
			<div class="tw-compare-modal-head">
				<h2><?php echo esc_html( get_theme_mod( 'vss_compare_modal_heading', 'Compare Products' ) ); ?></h2>
				<button type="button" class="tw-compare-modal-close" id="tw-compare-modal-close" aria-label="Close">&times;</button>
			</div>
			<div class="tw-compare-grid" id="tw-compare-modal-body"></div>
		</div>
	</div>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
