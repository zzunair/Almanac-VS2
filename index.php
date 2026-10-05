<?php
/**
 * Fallback template (required by WordPress). Every URL this theme is
 * designed for is covered by a more specific template; this only
 * catches anything unexpected (e.g. an uncategorized post archive).
 */
get_header();
?>
<div class="tw-container tw-section">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article style="margin-bottom:40px;">
				<h1 style="font-size:1.625rem;font-weight:800;"><?php the_title(); ?></h1>
				<div><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p>Nothing found.</p>
	<?php endif; ?>
</div>
<?php
get_footer();
