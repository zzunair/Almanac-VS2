/**
 * Front-end behaviour that has no server side: the product "reserve" form
 * and the contact form only ever flip local UI state. This site never
 * processes a transaction or a real submission -- by design.
 */
( function () {
	'use strict';

	var navToggle = document.querySelector( '.tw-nav-toggle' );
	var headerControls = document.getElementById( 'tw-header-controls' );
	if ( navToggle && headerControls ) {
		navToggle.addEventListener( 'click', function () {
			var isOpen = headerControls.classList.toggle( 'is-open' );
			navToggle.classList.toggle( 'is-open', isOpen );
			navToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	}

	document.querySelectorAll( '.tw-reserve-box' ).forEach( function ( box ) {
		var form       = box.querySelector( '.tw-reserve-form' );
		var success    = box.querySelector( '.tw-reserve-success' );
		var nameInput  = box.querySelector( '.tw-reserve-name-input' );
		var successText = box.querySelector( '.tw-reserve-success-text' );
		var template   = successText ? successText.getAttribute( 'data-template' ) : '';
		var submitBtn  = box.querySelector( '.tw-reserve-submit' );
		var resetBtn   = box.querySelector( '.tw-reserve-reset' );

		if ( submitBtn ) {
			submitBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var name = nameInput ? nameInput.value.trim() : '';
				var thanksName = name ? ', ' + name : '';
				if ( successText ) {
					successText.textContent = template.replace( '{name}', thanksName );
				}
				if ( form ) form.classList.add( 'tw-hidden' );
				if ( success ) success.classList.remove( 'tw-hidden' );
			} );
		}

		if ( resetBtn ) {
			resetBtn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				if ( nameInput ) nameInput.value = '';
				if ( success ) success.classList.add( 'tw-hidden' );
				if ( form ) form.classList.remove( 'tw-hidden' );
			} );
		}
	} );

	document.querySelectorAll( '.tw-contact-form' ).forEach( function ( form ) {
		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
		} );
	} );

	var filtersToggle = document.querySelector( '.tw-filters-toggle' );
	var filtersBody = document.getElementById( 'tw-filters-body' );
	if ( filtersToggle && filtersBody ) {
		filtersToggle.addEventListener( 'click', function () {
			var isOpen = filtersBody.classList.toggle( 'is-open' );
			filtersToggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		} );
	}

	document.querySelectorAll( '.tw-filters-form' ).forEach( function ( form ) {
		form.addEventListener( 'change', function ( e ) {
			if ( e.target.classList.contains( 'tw-filter-search' ) ) {
				return;
			}
			form.submit();
		} );
	} );

	// Live text filter for long brand/category lists -- narrows the
	// checkboxes in the matching .tw-filter-scroll without submitting.
	document.querySelectorAll( '.tw-filter-search' ).forEach( function ( input ) {
		var scroll = input.parentElement.querySelector( '.tw-filter-scroll' );
		if ( ! scroll ) {
			return;
		}
		input.addEventListener( 'input', function () {
			var q = input.value.trim().toLowerCase();
			scroll.querySelectorAll( 'label' ).forEach( function ( label ) {
				var matches = ! q || label.textContent.toLowerCase().indexOf( q ) !== -1;
				label.style.display = matches ? '' : 'none';
			} );
		} );
	} );

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str == null ? '' : String( str );
		return div.innerHTML;
	}

	document.querySelectorAll( '[data-tw-live-search]' ).forEach( function ( input ) {
		var wrap = input.closest( '.tw-search-wrap' );
		var dropdown = wrap ? wrap.querySelector( '.tw-search-suggestions' ) : null;
		if ( ! wrap || ! dropdown || typeof window.vssSearch === 'undefined' ) {
			return;
		}

		var timer = null;
		var currentTerm = '';

		function render( items, term ) {
			if ( ! items.length ) {
				dropdown.innerHTML = '';
				dropdown.hidden = true;
				return;
			}
			var html = items
				.map( function ( p ) {
					var img = p.image
						? '<img src="' + escapeHtml( p.image ) + '" alt="" />'
						: '<span class="tw-search-suggestion-noimg"></span>';
					return (
						'<a class="tw-search-suggestion" href="' + escapeHtml( p.url ) + '">' +
						img +
						'<span class="tw-search-suggestion-info">' +
						'<span class="tw-search-suggestion-name">' + escapeHtml( p.name ) + '</span>' +
						'<span class="tw-search-suggestion-meta">' + escapeHtml( p.price ) + ' &middot; ' + escapeHtml( p.badge ) + '</span>' +
						'</span>' +
						'</a>'
					);
				} )
				.join( '' );
			html += '<button type="button" class="tw-search-suggestion-viewall">View all results for &ldquo;' + escapeHtml( term ) + '&rdquo;</button>';
			dropdown.innerHTML = html;
			dropdown.hidden = false;

			var viewAllBtn = dropdown.querySelector( '.tw-search-suggestion-viewall' );
			if ( viewAllBtn ) {
				viewAllBtn.addEventListener( 'click', function () {
					input.closest( 'form' ).submit();
				} );
			}
		}

		input.addEventListener( 'input', function () {
			var term = input.value.trim();
			currentTerm = term;
			clearTimeout( timer );
			if ( term.length < 2 ) {
				dropdown.hidden = true;
				return;
			}
			timer = setTimeout( function () {
				var url =
					window.vssSearch.ajaxUrl +
					'?action=vss_search_suggest&nonce=' + encodeURIComponent( window.vssSearch.nonce ) +
					'&term=' + encodeURIComponent( term );
				fetch( url )
					.then( function ( r ) { return r.json(); } )
					.then( function ( items ) {
						if ( term === currentTerm ) {
							render( items, term );
						}
					} )
					.catch( function () {} );
			}, 300 );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! wrap.contains( e.target ) ) {
				dropdown.hidden = true;
			}
		} );

		input.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key ) {
				dropdown.hidden = true;
			}
		} );
	} );

	document.querySelectorAll( '[data-tw-gallery]' ).forEach( function ( thumbs ) {
		var main = document.getElementById( 'tw-gallery-main' );
		var mainImg = main ? main.querySelector( 'img' ) : null;
		if ( ! mainImg ) {
			return;
		}
		thumbs.querySelectorAll( '.tw-gallery-thumb' ).forEach( function ( thumb ) {
			thumb.addEventListener( 'click', function () {
				var full = thumb.getAttribute( 'data-full' );
				if ( ! full ) {
					return;
				}
				mainImg.src = full;
				mainImg.removeAttribute( 'srcset' );
				thumbs.querySelectorAll( '.tw-gallery-thumb' ).forEach( function ( t ) {
					t.classList.toggle( 'is-active', t === thumb );
				} );
			} );
		} );
	} );

	document.querySelectorAll( '[data-tw-slideshow]' ).forEach( function ( root ) {
		var slides = root.querySelectorAll( '.tw-slide' );
		var dots   = root.querySelectorAll( '.tw-slideshow-dot' );
		var prev   = root.querySelector( '.tw-slideshow-prev' );
		var next   = root.querySelector( '.tw-slideshow-next' );
		if ( slides.length < 2 ) {
			return;
		}

		var index = 0;
		var timer = null;

		function show( i ) {
			index = ( i + slides.length ) % slides.length;
			slides.forEach( function ( slide, n ) {
				slide.classList.toggle( 'is-active', n === index );
			} );
			dots.forEach( function ( dot, n ) {
				dot.classList.toggle( 'is-active', n === index );
			} );
		}

		function stopAutoplay() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function startAutoplay() {
			stopAutoplay();
			timer = setInterval( function () {
				show( index + 1 );
			}, 6000 );
		}

		if ( prev ) prev.addEventListener( 'click', function () { stopAutoplay(); show( index - 1 ); } );
		if ( next ) next.addEventListener( 'click', function () { stopAutoplay(); show( index + 1 ); } );
		dots.forEach( function ( dot, n ) {
			dot.addEventListener( 'click', function () {
				stopAutoplay();
				show( n );
			} );
		} );

		root.addEventListener( 'mouseenter', stopAutoplay );
		root.addEventListener( 'mouseleave', startAutoplay );

		startAutoplay();
	} );

	// Product compare: entirely client-side (localStorage) -- no account,
	// no server round trip. Selecting a card's checkbox snapshots the data
	// already on the page (name/brand/price/image/badge) so the comparison
	// view never has to re-fetch anything.
	var compareBar = document.getElementById( 'tw-compare-bar' );
	if ( compareBar ) {
		var STORAGE_KEY   = 'tw_compare_items';
		var maxItems      = parseInt( compareBar.getAttribute( 'data-max' ), 10 ) || 3;
		var barTextTpl    = compareBar.getAttribute( 'data-bar-text' ) || '{count} selected for comparison';
		var maxTextTpl    = compareBar.getAttribute( 'data-max-text' ) || 'You can compare up to {max} items at a time.';
		var viewText      = compareBar.getAttribute( 'data-view-text' ) || 'View Product';
		var barTextEl     = document.getElementById( 'tw-compare-bar-text' );
		var clearBtn      = document.getElementById( 'tw-compare-clear' );
		var openBtn       = document.getElementById( 'tw-compare-open' );
		var modal         = document.getElementById( 'tw-compare-modal' );
		var modalOverlay  = document.getElementById( 'tw-compare-modal-overlay' );
		var modalClose    = document.getElementById( 'tw-compare-modal-close' );
		var modalBody     = document.getElementById( 'tw-compare-modal-body' );

		function compareEscape( str ) {
			var div = document.createElement( 'div' );
			div.textContent = str == null ? '' : String( str );
			return div.innerHTML;
		}

		function readStore() {
			try {
				var raw = window.localStorage.getItem( STORAGE_KEY );
				return raw ? JSON.parse( raw ) : {};
			} catch ( e ) {
				return {};
			}
		}

		function writeStore( store ) {
			try {
				window.localStorage.setItem( STORAGE_KEY, JSON.stringify( store ) );
			} catch ( e ) {}
		}

		function syncCheckboxes( store ) {
			var count = Object.keys( store ).length;
			var atMax = count >= maxItems;
			document.querySelectorAll( '.tw-compare-checkbox' ).forEach( function ( box ) {
				var id = box.getAttribute( 'data-id' );
				box.checked  = !! store[ id ];
				box.disabled = atMax && ! box.checked;
			} );
		}

		function updateBar( store ) {
			var count = Object.keys( store ).length;
			if ( count > 0 ) {
				compareBar.hidden = false;
				barTextEl.textContent = barTextTpl.replace( '{count}', count );
			} else {
				compareBar.hidden = true;
			}
		}

		function refresh() {
			var store = readStore();
			syncCheckboxes( store );
			updateBar( store );
			return store;
		}

		function closeModal() {
			modal.hidden = true;
		}

		function renderModal( store ) {
			var ids = Object.keys( store );
			if ( ! ids.length ) {
				modalBody.innerHTML = '';
				return;
			}
			modalBody.innerHTML = ids
				.map( function ( id ) {
					var item = store[ id ];
					var img = item.image ? '<img src="' + compareEscape( item.image ) + '" alt="" />' : '';
					return (
						'<div class="tw-compare-item">' +
						'<button type="button" class="tw-compare-item-remove" data-remove-id="' + compareEscape( id ) + '" aria-label="Remove">&times;</button>' +
						'<div class="tw-compare-item-img">' + img + '</div>' +
						'<span class="tw-compare-item-brand">' + compareEscape( item.brand ) + '</span>' +
						'<span class="tw-compare-item-name">' + compareEscape( item.name ) + '</span>' +
						'<span class="tw-compare-item-price">' + compareEscape( item.price ) + '</span>' +
						'<span class="tw-badge ' + compareEscape( item.badgeClass ) + '">' + compareEscape( item.badge ) + '</span>' +
						'<a href="' + compareEscape( item.url ) + '" class="tw-btn tw-btn-outline" style="margin-top:6px;">' + compareEscape( viewText ) + '</a>' +
						'</div>'
					);
				} )
				.join( '' );

			modalBody.querySelectorAll( '[data-remove-id]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var id = btn.getAttribute( 'data-remove-id' );
					var s = readStore();
					delete s[ id ];
					writeStore( s );
					var updated = refresh();
					if ( Object.keys( updated ).length ) {
						renderModal( updated );
					} else {
						closeModal();
					}
				} );
			} );
		}

		function openModal() {
			var store = readStore();
			renderModal( store );
			modal.hidden = false;
		}

		document.addEventListener( 'change', function ( e ) {
			if ( ! e.target.classList || ! e.target.classList.contains( 'tw-compare-checkbox' ) ) {
				return;
			}
			var box   = e.target;
			var id    = box.getAttribute( 'data-id' );
			var store = readStore();
			if ( box.checked ) {
				if ( Object.keys( store ).length >= maxItems ) {
					box.checked = false;
					window.alert( maxTextTpl.replace( '{max}', maxItems ) );
					return;
				}
				store[ id ] = {
					name:       box.getAttribute( 'data-name' ) || '',
					brand:      box.getAttribute( 'data-brand' ) || '',
					price:      box.getAttribute( 'data-price' ) || '',
					image:      box.getAttribute( 'data-image' ) || '',
					url:        box.getAttribute( 'data-url' ) || '',
					badge:      box.getAttribute( 'data-badge' ) || '',
					badgeClass: box.getAttribute( 'data-badge-class' ) || '',
				};
			} else {
				delete store[ id ];
			}
			writeStore( store );
			refresh();
		} );

		if ( clearBtn ) {
			clearBtn.addEventListener( 'click', function () {
				writeStore( {} );
				refresh();
			} );
		}

		if ( openBtn ) openBtn.addEventListener( 'click', openModal );
		if ( modalClose ) modalClose.addEventListener( 'click', closeModal );
		if ( modalOverlay ) modalOverlay.addEventListener( 'click', closeModal );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && modal && ! modal.hidden ) {
				closeModal();
			}
		} );

		refresh();
	}

} )();
