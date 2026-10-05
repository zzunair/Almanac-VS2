<?php
/**
 * The home page -- matches Home.dc.html. Sections are rendered in the
 * order/visibility configured under Customizer > Home Page.
 */
get_header();
vss_render_sections( 'home' );
get_footer();
