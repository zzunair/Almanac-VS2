<?php
/** Shop / breadcrumb. */
$crumb = 'Home / Shop';
if ( is_product_taxonomy() ) {
	$term  = get_queried_object();
	$crumb = 'Home / Shop / ' . $term->name;
} elseif ( is_search() ) {
	$crumb = 'Home / Shop / Search results for "' . get_search_query( false ) . '"';
}
?>
<div class="tw-container tw-breadcrumb"><?php echo esc_html( $crumb ); ?></div>
