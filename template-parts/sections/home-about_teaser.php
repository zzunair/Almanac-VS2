<?php
/** Home / About teaser section. */
$image_id = get_theme_mod( 'vss_home_about_teaser_image', '' );
?>
<section class="tw-section">
	<div class="tw-container" style="display:flex;gap:40px;flex-wrap:wrap;align-items:center;">
		<div style="flex:1;min-width:280px;">
			<h2 class="tw-heading" style="margin-bottom:14px;"><?php echo esc_html( vss_field( 'home', 'about_teaser', 'heading' ) ); ?></h2>
			<p style="font-size:0.875rem;line-height:1.65;color:var(--tw-text-muted);margin:0 0 18px;"><?php echo esc_html( vss_field( 'home', 'about_teaser', 'body' ) ); ?></p>
			<a href="<?php echo esc_url( vss_field( 'home', 'about_teaser', 'link_href' ) ); ?>" class="tw-view-all"><?php echo esc_html( vss_field( 'home', 'about_teaser', 'link_text' ) ); ?></a>
		</div>
		<div style="flex:1;min-width:280px;">
			<?php if ( $image_id ) : ?>
				<img src="<?php echo esc_url( $image_id ); ?>" alt="<?php echo esc_attr( vss_field( 'home', 'about_teaser', 'heading' ) ); ?>" style="border-radius:20px;width:100%;aspect-ratio:16/9;object-fit:cover;" />
			<?php else : ?>
				<div class="tw-placeholder" style="border-radius:20px;aspect-ratio:16/9;background:#f5f3ec;"><?php echo esc_html( vss_field( 'home', 'about_teaser', 'image_label' ) ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>
