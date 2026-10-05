<?php
/**
 * Home / Brand strip. If the client has added logo images in Customizer >
 * Home Page > Brand Strip > Brand logos, show those (as clickable logos).
 * Otherwise fall back to a plain text list of every real brand so the
 * strip is never blank before real artwork has been uploaded.
 */
$blocks = vss_repeater( 'home', 'brands', 'logos' );

$items = array();
if ( $blocks ) {
	foreach ( $blocks as $block ) {
		if ( empty( $block['brand'] ) || empty( $block['image'] ) ) {
			continue;
		}
		$term = get_term_by( 'slug', $block['brand'], 'product_brand' );
		if ( ! $term || is_wp_error( $term ) ) {
			continue;
		}
		$items[] = array(
			'image' => $block['image'],
			'link'  => $block['link'] ? $block['link'] : get_term_link( $term ),
			'name'  => $term->name,
		);
	}
}

if ( empty( $items ) ) {
	$terms = get_terms( array( 'taxonomy' => 'product_brand', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		vss_render_empty_state(
			vss_field( 'home', 'brands', 'heading' ),
			'No brands yet. Add brand terms to your products in <strong>Products &rsaquo; Brands</strong>, or add logo images in <strong>Customizer &rsaquo; Home Page &rsaquo; Brand Strip</strong>.'
		);
		return;
	}
	?>
	<section class="tw-brand-strip">
		<div class="tw-container tw-brand-row">
			<?php if ( vss_field( 'home', 'brands', 'heading' ) ) : ?>
				<span class="tw-brand-name" style="color:var(--tw-text);"><?php echo esc_html( vss_field( 'home', 'brands', 'heading' ) ); ?></span>
			<?php endif; ?>
			<?php foreach ( $terms as $term ) : ?>
				<span class="tw-brand-name"><?php echo esc_html( $term->name ); ?></span>
			<?php endforeach; ?>
		</div>
	</section>
	<?php
	return;
}
?>
<section class="tw-brand-strip">
	<div class="tw-container tw-brand-row">
		<?php if ( vss_field( 'home', 'brands', 'heading' ) ) : ?>
			<span class="tw-brand-name" style="color:var(--tw-text);"><?php echo esc_html( vss_field( 'home', 'brands', 'heading' ) ); ?></span>
		<?php endif; ?>
		<?php foreach ( $items as $item ) : ?>
			<a href="<?php echo esc_url( $item['link'] ); ?>" class="tw-brand-logo">
				<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" />
			</a>
		<?php endforeach; ?>
	</div>
</section>
