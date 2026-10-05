<?php
/**
 * Home / Collection List. If the client has curated specific collections
 * in Customizer > Home Page > Collection List, show exactly those, in
 * that order -- each with its own image (or the category's own WooCommerce
 * photo) and an optional custom link. Otherwise fall back to every real
 * WooCommerce category so the section is never blank on a fresh install.
 */
$blocks = vss_repeater( 'home', 'categories', 'collections' );

$tiles = array();
if ( $blocks ) {
	foreach ( $blocks as $block ) {
		if ( empty( $block['category'] ) ) {
			continue;
		}
		$term = get_term_by( 'slug', $block['category'], 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$tiles[] = array(
			'link'  => $block['link'] ? $block['link'] : get_term_link( $term ),
			'label' => $block['label'] ? $block['label'] : $term->name,
			'image' => $block['image'] ? $block['image'] : vss_category_thumbnail_url( $term ),
			'icon'  => vss_category_icon_path( $block['label'] ? $block['label'] : $term->name ),
			'count' => vss_term_live_count( 'product_cat', $term->slug ),
		);
	}
} else {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'exclude'    => array( get_option( 'default_product_cat' ) ),
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$tiles[] = array(
				'link'  => get_term_link( $term ),
				'label' => $term->name,
				'image' => vss_category_thumbnail_url( $term ),
				'icon'  => vss_category_icon_path( $term->name ),
				'count' => vss_term_live_count( 'product_cat', $term->slug ),
			);
		}
	}
}

if ( empty( $tiles ) ) {
	vss_render_empty_state(
		vss_field( 'home', 'categories', 'heading' ),
		'No product categories yet. Add categories to your products in <strong>Products &rsaquo; Categories</strong>, or curate a custom list in <strong>Customizer &rsaquo; Home Page &rsaquo; Collection List</strong>.'
	);
	return;
}
?>
<section class="tw-section">
	<div class="tw-container">
		<?php if ( vss_field( 'home', 'categories', 'heading' ) ) : ?>
			<h2 class="tw-heading" style="margin-bottom:26px;"><?php echo esc_html( vss_field( 'home', 'categories', 'heading' ) ); ?></h2>
		<?php endif; ?>
		<div class="tw-grid tw-grid--cat">
			<?php foreach ( $tiles as $tile ) : ?>
				<a href="<?php echo esc_url( $tile['link'] ); ?>" class="tw-cat-card">
					<?php if ( $tile['image'] ) : ?>
						<img class="tw-cat-card-img" src="<?php echo esc_url( $tile['image'] ); ?>" alt="<?php echo esc_attr( $tile['label'] ); ?>" />
					<?php else : ?>
						<span class="tw-cat-icon">
							<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="var(--tw-accent-dark)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="<?php echo esc_attr( $tile['icon'] ); ?>"/></svg>
						</span>
					<?php endif; ?>
					<span class="tw-cat-card-label"><?php echo esc_html( $tile['label'] ); ?></span>
					<span class="tw-cat-card-count"><?php echo esc_html( (int) $tile['count'] ); ?> in stock &rarr;</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
