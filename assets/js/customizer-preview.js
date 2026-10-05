/**
 * Live-preview bindings for the Customizer > Colors / Typography controls
 * that use transport: 'postMessage' -- runs inside the preview iframe and
 * updates CSS variables / body classes directly, no full reload.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.customize ) {
		return;
	}

	var colorVarMap = {
		vss_color_accent: '--tw-accent',
		vss_color_accent_dark: '--tw-accent-dark',
		vss_color_text: '--tw-text',
		vss_color_text_muted: '--tw-text-muted',
		vss_color_bg: '--tw-bg',
		vss_color_bg_soft: '--tw-bg-soft',
		vss_color_border: '--tw-border',
	};

	Object.keys( colorVarMap ).forEach( function ( settingId ) {
		wp.customize( settingId, function ( value ) {
			value.bind( function ( newValue ) {
				document.documentElement.style.setProperty( colorVarMap[ settingId ], newValue );
			} );
		} );
	} );

	wp.customize( 'vss_nav_text_style', function ( value ) {
		value.bind( function ( newValue ) {
			document.body.className = document.body.className.replace( /\btw-nav-style-\S+/g, '' );
			document.body.classList.add( 'tw-nav-style-' + newValue );
		} );
	} );
} )( window.wp );
