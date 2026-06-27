( function () {
	'use strict';

	var shellConfig = window.VMS_EFWP_OverlayShell || {};
	var shellI18n = shellConfig.i18n || {};

	function shellText( key, fallback ) {
		return shellI18n[ key ] || fallback;
	}

	var ROOT_ID = 'vms-efwp-fastspring-overlay-root';

	var CHECKOUT_SELECTORS = [
		'#fsc-popup-frame',
		'#fscEmbeddedCheckout',
		'iframe[src*="onfastspring.com"]',
		'iframe[src*="fastspring.com"]',
	];

	var HOST_SELECTORS = CHECKOUT_SELECTORS.concat( [
		'#fscCanvas',
		'.fsc-modalBackdrop',
		'.fsc-modal-backdrop',
		'[id^="fsc-popup"]',
		'[class*="fsc-modal"]',
	] );

	function ensureRoot() {
		var root = document.getElementById( ROOT_ID );
		if ( root ) {
			if ( root.parentNode !== document.body ) {
				document.body.appendChild( root );
			}
			return root;
		}

		root = document.createElement( 'main' );
		root.id = ROOT_ID;
		root.className = 'vms-efwp-fastspring-overlay-root';
		root.setAttribute( 'role', 'dialog' );
		root.setAttribute( 'aria-modal', 'true' );
		root.setAttribute( 'aria-label', shellText( 'checkoutAriaLabel', 'FastSpring checkout' ) );
		document.body.appendChild( root );
		return root;
	}

	function findCheckoutNode( scope ) {
		var root = scope || document;
		var selector = CHECKOUT_SELECTORS.join( ',' );
		return selector ? root.querySelector( selector ) : null;
	}

	function isInsideCanvas( node ) {
		var canvas = document.getElementById( 'fscCanvas' );
		return !!( canvas && node && canvas !== node && canvas.contains( node ) );
	}

	function shouldMoveNode( node ) {
		if ( ! node || node.nodeType !== 1 || node.id === ROOT_ID ) {
			return false;
		}

		if ( node.id === 'fscCanvas' || node.classList.contains( 'fs-popup-background' ) ) {
			return false;
		}

		if ( isInsideCanvas( node ) ) {
			return false;
		}

		if ( node.tagName === 'IFRAME' && node.src && /fastspring\.com/i.test( node.src ) ) {
			return true;
		}

		var i;
		for ( i = 0; i < HOST_SELECTORS.length; i++ ) {
			try {
				if ( node.matches( HOST_SELECTORS[ i ] ) ) {
					return true;
				}
			} catch ( e ) {
				// Ignore invalid selectors in older browsers.
			}
		}

		return false;
	}

	function hideCanvasSpinner( canvas ) {
		if ( ! canvas ) {
			return;
		}

		var images = canvas.querySelectorAll( 'img[src*="spin.svg"], img[src*="pinhole"]' );
		var i;

		for ( i = 0; i < images.length; i++ ) {
			images[ i ].style.display = 'none';
		}
	}

	function fixCanvasLayer() {
		var canvas = document.getElementById( 'fscCanvas' );
		if ( ! canvas ) {
			return;
		}

		var checkoutInside = findCheckoutNode( canvas );
		var checkoutAnywhere = findCheckoutNode( document );

		canvas.style.pointerEvents = 'none';

		if ( checkoutInside ) {
			canvas.classList.add( 'vms-efwp-fsc-host' );
			canvas.style.opacity = '';
			canvas.style.visibility = '';
			hideCanvasSpinner( canvas );
			return;
		}

		canvas.classList.remove( 'vms-efwp-fsc-host' );

		if ( checkoutAnywhere ) {
			hideCanvasSpinner( canvas );
		}
	}

	function mountPopupNodes() {
		var root = ensureRoot();
		var canvas = document.getElementById( 'fscCanvas' );
		var checkoutInsideCanvas = canvas ? findCheckoutNode( canvas ) : null;
		var i;
		var children;

		if ( checkoutInsideCanvas ) {
			if ( canvas.parentNode !== document.body ) {
				document.body.appendChild( canvas );
			}
			fixCanvasLayer();
			root.classList.add( 'is-open' );
			return;
		}

		var selector = HOST_SELECTORS.join( ',' );
		var nodes = selector ? document.querySelectorAll( selector ) : [];

		for ( i = 0; i < nodes.length; i++ ) {
			if ( nodes[ i ].closest( '#' + ROOT_ID ) || isInsideCanvas( nodes[ i ] ) ) {
				continue;
			}
			if ( nodes[ i ].id === 'fscCanvas' ) {
				continue;
			}
			root.appendChild( nodes[ i ] );
		}

		children = document.body.children;
		for ( i = 0; i < children.length; i++ ) {
			if ( shouldMoveNode( children[ i ] ) && ! children[ i ].closest( '#' + ROOT_ID ) ) {
				root.appendChild( children[ i ] );
			}
		}

		fixCanvasLayer();

		if ( findCheckoutNode( document ) ) {
			root.classList.add( 'is-open' );
		}
	}

	function startObserver() {
		if ( window._VMS_EFWP_OverlayObserver || ! window.MutationObserver ) {
			return;
		}

		window._VMS_EFWP_OverlayObserver = new MutationObserver( function () {
			mountPopupNodes();
		} );

		window._VMS_EFWP_OverlayObserver.observe( document.body, {
			childList: true,
			subtree: true,
		} );
	}

	function activate() {
		document.documentElement.classList.add( 'vms-efwp-checkout-active', 'vms-efwp-fastspring-checkout' );
		ensureRoot();
		mountPopupNodes();
		startObserver();
	}

	function deactivate() {
		document.documentElement.classList.remove( 'vms-efwp-checkout-active', 'vms-efwp-fastspring-checkout' );
		var root = document.getElementById( ROOT_ID );
		if ( root ) {
			root.classList.remove( 'is-open' );
		}
	}

	window.VMS_EFWP_OverlayApi = {
		activate: activate,
		deactivate: deactivate,
		mount: mountPopupNodes,
		ensureRoot: ensureRoot,
	};
}() );
