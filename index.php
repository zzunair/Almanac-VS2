<?php
/**
 * Fallback template (required by WordPress). Every URL this theme is
 * designed for is covered by a more specific template; this only
 * catches anything unexpected (e.g. an uncategorized post archive).
 */
get_header();

$commerce_view = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
?>
<div class="tw-container <?php echo $commerce_view ? 'tw-commerce' : 'tw-section'; ?>">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php if ( $commerce_view ) : ?>
				<?php
				if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
					$crumb = 'Order received';
				} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
					$crumb = 'Checkout';
				} else {
					$crumb = 'Cart';
				}
				?>
				<div class="tw-commerce-page">
					<?php
					vss_breadcrumb(
						array(
							array( 'label' => 'Home', 'url' => home_url( '/' ) ),
							array( 'label' => $crumb, 'url' => '' ),
						),
						''
					);
					?>
					<h1 class="tw-commerce-title"><?php the_title(); ?></h1>
					<div class="tw-commerce-content"><?php the_content(); ?></div>
				</div>
			<?php else : ?>
				<article style="margin-bottom:40px;">
					<h1 style="font-size:1.625rem;font-weight:800;"><?php the_title(); ?></h1>
					<div><?php the_content(); ?></div>
				</article>
			<?php endif; ?>
		<?php endwhile; ?>
	<?php else : ?>
		<p>Nothing found.</p>
	<?php endif; ?>
</div>
<?php
get_footer();
