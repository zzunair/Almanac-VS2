/* global jQuery, wp */
( function ( $, api ) {
	'use strict';

	if ( ! api || ! api.controlConstructor ) {
		return;
	}

	api.controlConstructor[ 'vss-section-order' ] = api.Control.extend( {
		ready: function () {
			var control = this;
			var $list = control.container.find( '.vss-section-order' );

			function readValue() {
				var rows = [];
				$list.find( '.vss-section-order-item' ).each( function () {
					var $item = $( this );
					rows.push( {
						id: $item.data( 'id' ),
						enabled: $item.find( '.vss-section-enabled' ).is( ':checked' ),
					} );
				} );
				return rows;
			}

			function syncValue() {
				var value = JSON.stringify( readValue() );
				$list.siblings( '.vss-section-order-value' ).val( value );
				control.setting.set( value );
			}

			$list.sortable( {
				handle: '.vss-drag-handle',
				axis: 'y',
				update: syncValue,
			} );

			$list.on( 'change', '.vss-section-enabled', syncValue );
		},
	} );

	/**
	 * Generic "add / remove / reorder blocks" repeater control -- used for
	 * slideshow slides, collection tiles, and the various card grids.
	 */
	api.controlConstructor[ 'vss-repeater' ] = api.Control.extend( {
		ready: function () {
			var control = this;
			var $root  = control.container.find( '.tw-repeater' );
			var $hidden = control.container.find( '.tw-repeater-value' );
			var $template = control.container.find( '.tw-repeater-template' );
			var titleField = $root.data( 'title-field' );
			var minBlocks = parseInt( $root.data( 'min' ), 10 ) || 0;

			function fieldValue( $field ) {
				if ( $field.is( ':checkbox' ) ) {
					return $field.is( ':checked' );
				}
				return $field.val();
			}

			function fieldTitleText( $field ) {
				if ( $field.is( 'select' ) ) {
					return $field.find( 'option:selected' ).text();
				}
				return $field.val();
			}

			function updateBlockTitle( $block ) {
				if ( ! titleField ) {
					return;
				}
				var $field = $block.find( '[data-field="' + titleField + '"]' );
				if ( $field.length ) {
					var text = fieldTitleText( $field ) || 'Block';
					$block.find( '.tw-repeater-block-title' ).first().text( text );
				}
			}

			function updateRemoveButtons() {
				var $blocks = $root.find( '> .tw-repeater-block' );
				$blocks.find( '.tw-repeater-remove' ).prop( 'disabled', $blocks.length <= minBlocks );
			}

			function syncValue() {
				var blocks = [];
				$root.find( '> .tw-repeater-block' ).each( function () {
					var block = {};
					$( this )
						.find( '.tw-repeater-block-body [data-field]' )
						.each( function () {
							block[ $( this ).data( 'field' ) ] = fieldValue( $( this ) );
						} );
					blocks.push( block );
				} );
				var value = JSON.stringify( blocks );
				$hidden.val( value );
				control.setting.set( value );
				updateRemoveButtons();
			}

			$root.sortable( {
				handle: '.tw-repeater-drag-handle',
				items: '> .tw-repeater-block',
				axis: 'y',
				update: syncValue,
			} );

			control.container.on( 'click', '.tw-repeater-block-toggle', function () {
				$( this ).closest( '.tw-repeater-block' ).toggleClass( 'is-collapsed' );
			} );

			control.container.on( 'click', '.tw-repeater-remove', function ( e ) {
				e.preventDefault();
				if ( $root.find( '> .tw-repeater-block' ).length <= minBlocks ) {
					return;
				}
				$( this ).closest( '.tw-repeater-block' ).remove();
				syncValue();
			} );

			control.container.on( 'click', '.tw-repeater-add', function ( e ) {
				e.preventDefault();
				var $clone = $( document.importNode( $template[ 0 ].content, true ) ).find( '.tw-repeater-block' );
				$root.append( $clone );
				updateBlockTitle( $clone );
				syncValue();
			} );

			control.container.on( 'input change', '.tw-repeater-block-body [data-field]', function () {
				var $block = $( this ).closest( '.tw-repeater-block' );
				updateBlockTitle( $block );
				syncValue();
			} );

			control.container.on( 'click', '.tw-repeater-image-select', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.tw-repeater-image' );
				var $input = $wrap.find( '[data-field]' );
				var $preview = $wrap.find( '.tw-repeater-image-preview' );
				var $remove = $wrap.find( '.tw-repeater-image-remove' );
				var $button = $( this );

				var frame = wp.media( { title: 'Select image', multiple: false, library: { type: 'image' } } );
				frame.on( 'select', function () {
					// Use the original full-resolution upload, not the small
					// "medium" (max 300px) size -- these images render in cards
					// several hundred px wide (hero photo, collection tiles),
					// and stretching a 300px image to fill them is what causes
					// the blur. Downscaling a full-size image never blurs;
					// upscaling a small one always does.
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					var url = attachment.url;
					$input.val( url );
					$preview.attr( 'src', url ).removeClass( 'tw-hidden' );
					$remove.removeClass( 'tw-hidden' );
					$button.text( 'Change image' );
					updateBlockTitle( $wrap.closest( '.tw-repeater-block' ) );
					syncValue();
				} );
				frame.open();
			} );

			control.container.on( 'click', '.tw-repeater-image-remove', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.tw-repeater-image' );
				$wrap.find( '[data-field]' ).val( '' );
				$wrap.find( '.tw-repeater-image-preview' ).addClass( 'tw-hidden' ).attr( 'src', '' );
				$wrap.find( '.tw-repeater-image-select' ).text( 'Select image' );
				$( this ).addClass( 'tw-hidden' );
				syncValue();
			} );

			updateRemoveButtons();
		},
	} );
} )( jQuery, wp.customize );
