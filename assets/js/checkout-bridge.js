( function () {
	'use strict';

	var config = window.VMS_EFPG_CheckoutBridge || {};
	var OVERLAY = config.overlay || {};
	var SUCCESS = config.successUrl || '';
	var CANCEL = config.cancelUrl || '';
	var opened = false;
	var done = false;
	var tries = 0;

	function appendQueryParam( url, key, value ) {
		try {
			var target = new URL( url, window.location.origin );
			target.searchParams.set( key, value );
			return target.toString();
		} catch ( e ) {
			var join = url.indexOf( '?' ) === -1 ? '?' : '&';
			return url + join + encodeURIComponent( key ) + '=' + encodeURIComponent( value );
		}
	}

	function confirmPaymentAndRedirect( orderId, orderKey, fsOrderId ) {
		if ( ! orderId || ! fsOrderId || ! SUCCESS ) {
			window.location.replace( SUCCESS || CANCEL );
			return;
		}

		window.location.replace( appendQueryParam( SUCCESS, 'vms_efpg_fs_order', fsOrderId ) );
	}

	window.VMS_EFPG_ErrorCallback = function ( code, message ) {
		console.error( '[VMS FastSpring]', code, message );
	};

	window.VMS_EFPG_Closed = function ( data ) {
		if ( done ) {
			return;
		}
		done = true;
		if ( window.VMS_EFPG_OverlayApi && typeof window.VMS_EFPG_OverlayApi.deactivate === 'function' ) {
			window.VMS_EFPG_OverlayApi.deactivate();
		}
		if ( window.fastspring && window.fastspring.builder && typeof window.fastspring.builder.reset === 'function' ) {
			try {
				window.fastspring.builder.reset();
			} catch ( e ) {
				// Ignore reset errors after close.
			}
		}
		if ( data && data.id ) {
			confirmPaymentAndRedirect(
				OVERLAY.orderId || 0,
				OVERLAY.orderKey || '',
				data.id
			);
		} else {
			window.location.replace( CANCEL );
		}
	};

	function ready() {
		return window.fastspring && window.fastspring.builder && typeof window.fastspring.builder.checkout === 'function';
	}

	function launch() {
		if ( opened || ! ready() || ! OVERLAY ) {
			return;
		}
		opened = true;
		if ( window.VMS_EFPG_OverlayApi && typeof window.VMS_EFPG_OverlayApi.activate === 'function' ) {
			window.VMS_EFPG_OverlayApi.activate();
		}
		try {
			window.fastspring.builder.reset();
			if ( OVERLAY.tags ) {
				window.fastspring.builder.tag( OVERLAY.tags );
			}
			if ( OVERLAY.pushPayload ) {
				if ( OVERLAY.useSecure ) {
					window.fastspring.builder.secure( OVERLAY.pushPayload, '' );
				} else {
					window.fastspring.builder.push( {
						reset: true,
						products: ( OVERLAY.pushPayload.items || [] ).map( function ( item ) {
							return { path: item.product, quantity: item.quantity || 1 };
						} ),
						paymentContact: OVERLAY.pushPayload.contact || {},
						country: OVERLAY.pushPayload.country,
						language: ( OVERLAY.pushPayload.language || 'EN' ).toLowerCase(),
					} );
				}
			}
			window.fastspring.builder.checkout();
			if ( window.VMS_EFPG_OverlayApi && typeof window.VMS_EFPG_OverlayApi.mount === 'function' ) {
				window.VMS_EFPG_OverlayApi.mount();
				setTimeout( function () { window.VMS_EFPG_OverlayApi.mount(); }, 100 );
				setTimeout( function () { window.VMS_EFPG_OverlayApi.mount(); }, 500 );
			}
		} catch ( e ) {
			window.location.replace( CANCEL );
		}
	}

	var timer = setInterval( function () {
		tries++;
		if ( ready() ) {
			clearInterval( timer );
			launch();
		} else if ( tries > 100 ) {
			clearInterval( timer );
			window.location.replace( CANCEL );
		}
	}, 100 );
}() );
