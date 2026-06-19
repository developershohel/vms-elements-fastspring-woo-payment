( function ( $, config ) {
	'use strict';

	config = config || {};

	var STORAGE_KEY = 'vms_efwp_pending';
	var STASH_TTL_MS = 900000;

	var state = {
		opened: false,
		done: false,
		current: null,
		handledOrders: {},
		pendingFastSpring: false,
		navigationGuardInstalled: false,
	};

	function log( msg ) {
		if ( window.console && config.debug ) {
			console.info( '[VMS FastSpring]', msg );
		}
	}

	function ready() {
		return (
			window.fastspring &&
			window.fastspring.builder &&
			typeof window.fastspring.builder.checkout === 'function'
		);
	}

	function whenBuilderReady( callback, maxTries ) {
		var tries = 0;
		var limit = maxTries || 150;

		if ( ready() ) {
			callback();
			return;
		}

		var timer = setInterval( function () {
			tries += 1;
			if ( ready() ) {
				clearInterval( timer );
				callback();
			} else if ( tries >= limit ) {
				clearInterval( timer );
				callback( new Error( 'FastSpring Store Builder did not load.' ) );
			}
		}, 100 );
	}

	function isOrderReceivedUrl( url ) {
		if ( ! url ) {
			return false;
		}
		url = String( url );
		return (
			url.indexOf( 'order-received' ) !== -1 ||
			url.indexOf( 'order-received/' ) !== -1
		);
	}

	function shouldBlockNavigation( url ) {
		return state.pendingFastSpring && isOrderReceivedUrl( url );
	}

	function installNavigationGuard() {
		if ( state.navigationGuardInstalled ) {
			return;
		}
		state.navigationGuardInstalled = true;

		var locationProto = window.Location && window.Location.prototype;
		if ( locationProto ) {
			[ 'assign', 'replace' ].forEach( function ( method ) {
				if ( typeof locationProto[ method ] !== 'function' ) {
					return;
				}
				var original = locationProto[ method ];
				locationProto[ method ] = function ( url ) {
					if ( shouldBlockNavigation( url ) ) {
						log( 'Blocked ' + method + ' to order-received while popup is pending.' );
						return;
					}
					return original.call( this, url );
				};
			} );

			var hrefDesc = Object.getOwnPropertyDescriptor( locationProto, 'href' );
			if ( hrefDesc && hrefDesc.set ) {
				Object.defineProperty( locationProto, 'href', {
					configurable: true,
					enumerable: hrefDesc.enumerable,
					get: hrefDesc.get,
					set: function ( url ) {
						if ( shouldBlockNavigation( url ) ) {
							log( 'Blocked href navigation to order-received while popup is pending.' );
							return;
						}
						hrefDesc.set.call( this, url );
					},
				} );
			}
		}

		if ( window.history && window.history.pushState ) {
			var origPushState = window.history.pushState.bind( window.history );
			window.history.pushState = function ( data, title, url ) {
				if ( shouldBlockNavigation( url ) ) {
					log( 'Blocked pushState to order-received while popup is pending.' );
					return;
				}
				return origPushState( data, title, url );
			};
		}

		window.addEventListener( 'beforeunload', function ( event ) {
			if ( state.opened && ! state.done ) {
				event.preventDefault();
				event.returnValue = '';
			}
		} );
	}

	function stashOverlay( payload ) {
		if ( ! payload || ! window.sessionStorage ) {
			return;
		}
		try {
			window.sessionStorage.setItem(
				STORAGE_KEY,
				JSON.stringify( {
					payload: payload,
					ts: Date.now(),
				} )
			);
		} catch ( e ) {
			// Ignore quota errors.
		}
	}

	function clearStash() {
		if ( ! window.sessionStorage ) {
			return;
		}
		try {
			window.sessionStorage.removeItem( STORAGE_KEY );
		} catch ( e ) {
			// Ignore.
		}
	}

	function hasSblAccessKey() {
		if ( config.accessKey ) {
			return true;
		}
		var script = document.getElementById( 'fsc-api' );
		return !!( script && script.getAttribute( 'data-access-key' ) );
	}

	function showError( message ) {
		state.opened = false;
		state.pendingFastSpring = false;
		state.current = null;
		clearStash();
		document.documentElement.classList.remove( 'vefwp-checkout-active' );
		window.alert( message || ( config.i18n && config.i18n.error ) || 'FastSpring checkout could not open.' );
	}

	function openOverlay( payload ) {
		if ( ! payload ) {
			return false;
		}

		if ( ! payload.pushPayload && ! payload.sessionId ) {
			return false;
		}

		var orderId = payload.orderId || 0;
		if ( orderId && state.handledOrders[ orderId ] && state.opened ) {
			return true;
		}

		if ( state.opened && state.current && state.current.sessionId === payload.sessionId ) {
			return true;
		}

		if ( ! config.popupStorefront ) {
			showError( ( config.i18n && config.i18n.missingPopup ) || 'FastSpring popup checkout path is not configured.' );
			return false;
		}

		installNavigationGuard();
		stashOverlay( payload );

		state.current = payload;
		state.opened = true;
		state.done = false;
		state.pendingFastSpring = true;

		if ( orderId ) {
			state.handledOrders[ orderId ] = true;
		}

		document.documentElement.classList.add( 'vefwp-checkout-active' );
		log( 'Opening popup for order ' + ( orderId || 'unknown' ) );

		whenBuilderReady( function ( err ) {
			if ( err ) {
				showError( ( config.i18n && config.i18n.loadFailed ) || err.message );
				return;
			}

			try {
				window.fastspring.builder.reset();

				if ( payload.tags && typeof window.fastspring.builder.tag === 'function' ) {
					window.fastspring.builder.tag( payload.tags );
				}

				if ( payload.pushPayload ) {
					if ( payload.useSecure && typeof window.fastspring.builder.secure === 'function' ) {
						if ( ! hasSblAccessKey() ) {
							showError(
								( config.i18n && config.i18n.missingAccessKey ) ||
									'FastSpring Store Builder access key is required for custom WooCommerce pricing. Add it in FastSpring → Settings (sandbox or live), from FastSpring App → Developer Tools → Store Builder Library.'
							);
							return;
						}
						// Empty secureKey = unencrypted test payload (FastSpring sandbox docs).
						window.fastspring.builder.secure( payload.pushPayload, '' );
					} else if ( typeof window.fastspring.builder.push === 'function' ) {
						var pushPayload = payload.pushPayload;
						window.fastspring.builder.push( {
							reset: true,
							products: ( pushPayload.items || [] ).map( function ( item ) {
								return {
									path: item.product,
									quantity: item.quantity || 1,
								};
							} ),
							paymentContact: pushPayload.contact || {},
							country: pushPayload.country,
							language: ( pushPayload.language || 'EN' ).toLowerCase(),
						} );
					}
				}

				window.fastspring.builder.checkout();
			} catch ( e ) {
				showError( e && e.message ? e.message : 'FastSpring checkout failed to open.' );
			}
		} );

		return true;
	}

	function finishOverlay( orderReference ) {
		if ( state.done || ! state.current ) {
			return;
		}

		var payload    = state.current;
		var successUrl = payload.successUrl;
		var cancelUrl  = payload.cancelUrl;
		var orderId    = payload.orderId || 0;
		var orderKey   = payload.orderKey || '';

		state.done = true;
		state.pendingFastSpring = false;
		document.documentElement.classList.remove( 'vefwp-checkout-active' );

		if (
			window.fastspring &&
			window.fastspring.builder &&
			typeof window.fastspring.builder.reset === 'function'
		) {
			try {
				window.fastspring.builder.reset();
			} catch ( e ) {
				// Ignore reset errors.
			}
		}

		state.opened = false;
		state.current = null;
		clearStash();

		if ( orderReference && orderReference.id ) {
			confirmPaymentAndRedirect( orderId, orderKey, orderReference.id, successUrl );
			return;
		}

		if ( cancelUrl ) {
			window.location.replace( cancelUrl );
		}
	}

	function confirmPaymentAndRedirect( orderId, orderKey, fsOrderId, successUrl ) {
		if ( ! orderId || ! fsOrderId || ! config.completeRestUrl ) {
			window.location.replace( successUrl );
			return;
		}

		var url = config.completeRestUrl + orderId;
		var headers = {
			'Content-Type': 'application/json',
		};

		if ( config.restNonce ) {
			headers['X-WP-Nonce'] = config.restNonce;
		}

		var attempts = 0;
		var maxAttempts = 4;

		function attemptComplete() {
			attempts += 1;

			return window.fetch( url, {
				method: 'POST',
				credentials: 'same-origin',
				headers: headers,
				body: JSON.stringify( {
					fs_order_id: fsOrderId,
					key: orderKey,
				} ),
			} )
				.then( function ( response ) {
					return response.json().then( function ( body ) {
						return {
							ok: response.ok,
							body: body,
						};
					} );
				} )
				.then( function ( result ) {
					var status = result.body && result.body.status ? result.body.status : '';
					if ( status === 'completed' || status === 'already_paid' || attempts >= maxAttempts ) {
						window.location.replace( successUrl );
						return;
					}

					return new Promise( function ( resolve ) {
						window.setTimeout( resolve, 1200 );
					} ).then( attemptComplete );
				} );
		}

		attemptComplete().catch( function ( err ) {
			log( 'Payment confirm failed: ' + ( err && err.message ? err.message : err ) );
			window.location.replace( successUrl );
		} );
	}

	function extractOverlay( data ) {
		if ( ! data || typeof data !== 'object' ) {
			return null;
		}

		if ( data.vms_efwp_overlay ) {
			return data.vms_efwp_overlay;
		}

		if ( data.payment_result ) {
			if ( data.payment_result.vms_efwp_overlay ) {
				return data.payment_result.vms_efwp_overlay;
			}
			if (
				data.payment_result.payment_details &&
				data.payment_result.payment_details.vms_efwp_overlay
			) {
				return data.payment_result.payment_details.vms_efwp_overlay;
			}
		}

		if ( data.payment_details && data.payment_details.vms_efwp_overlay ) {
			return data.payment_details.vms_efwp_overlay;
		}

		return null;
	}

	function handlePaymentResponse( data ) {
		var overlay = extractOverlay( data );
		if ( overlay ) {
			return openOverlay( overlay );
		}

		if ( data && data.order_id && ! state.handledOrders[ data.order_id ] ) {
			fetchOverlayForOrder( data.order_id, '' );
		}

		return false;
	}

	function tryParseJson( text ) {
		if ( ! text || typeof text !== 'string' ) {
			return null;
		}
		try {
			return JSON.parse( text );
		} catch ( e ) {
			return null;
		}
	}

	function isCheckoutRequest( url, method ) {
		if ( ! url ) {
			return false;
		}

		url = String( url );

		if ( url.indexOf( 'checkout' ) === -1 ) {
			return false;
		}

		if ( method && 'POST' !== String( method ).toUpperCase() ) {
			return false;
		}

		return (
			url.indexOf( 'wc/store' ) !== -1 ||
			url.indexOf( 'wc-ajax=checkout' ) !== -1
		);
	}

	function fetchOverlayForOrder( orderId, token ) {
		orderId = parseInt( orderId, 10 );
		if ( ! orderId || ! config.restUrl || state.handledOrders[ orderId ] ) {
			return Promise.resolve( false );
		}

		var url = config.restUrl + orderId;
		var query = [];

		if ( token ) {
			query.push( 'token=' + encodeURIComponent( token ) );
		}

		if ( query.length ) {
			url += ( url.indexOf( '?' ) === -1 ? '?' : '&' ) + query.join( '&' );
		}

		var headers = {};
		if ( config.restNonce ) {
			headers['X-WP-Nonce'] = config.restNonce;
		}

		return window.fetch( url, {
			method: 'GET',
			credentials: 'same-origin',
			headers: headers,
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( payload ) {
				if ( payload && ( payload.pushPayload || payload.sessionId ) ) {
					return openOverlay( payload );
				}
				return false;
			} )
			.catch( function ( err ) {
				log( 'Overlay REST fetch failed: ' + ( err && err.message ? err.message : err ) );
				return false;
			} );
	}

	function handlePendingFastSpring( orderId ) {
		orderId = parseInt( orderId, 10 );
		if ( ! orderId || state.handledOrders[ orderId ] ) {
			return;
		}
		fetchOverlayForOrder( orderId, '' );
	}

	function initPendingRecovery() {
		if ( window.sessionStorage ) {
			try {
				var raw = window.sessionStorage.getItem( STORAGE_KEY );
				if ( raw ) {
					var data = JSON.parse( raw );
					if ( data && data.payload && Date.now() - data.ts < STASH_TTL_MS ) {
						setTimeout( function () {
							openOverlay( data.payload );
						}, 50 );
						return;
					}
					clearStash();
				}
			} catch ( e ) {
				clearStash();
			}
		}

		if ( isOrderReceivedUrl( window.location.href ) ) {
			return;
		}

		var params = new URLSearchParams( window.location.search );
		var openId = params.get( 'vefwp_open' );
		if ( openId ) {
			fetchOverlayForOrder( openId, params.get( 'token' ) || '' );
		}
	}

	// FastSpring Store Builder popup-closed callback.
	window.vmsEfwpPopupClosed = function ( orderReference ) {
		finishOverlay( orderReference );
	};

	window.vmsEfwpErrorCallback = function ( code, message ) {
		log( 'SBL error: ' + code + ' — ' + message );
		if ( window.console && window.console.error ) {
			console.error( '[VMS FastSpring]', code, message );
		}
	};

	installNavigationGuard();
	initPendingRecovery();

	// Classic shortcode checkout (jQuery AJAX).
	if ( $ && $.fn ) {
		$( document ).ajaxSuccess( function ( event, xhr, settings ) {
			var method = settings && settings.type ? settings.type : 'GET';
			if ( ! isCheckoutRequest( settings && settings.url, method ) ) {
				return;
			}
			handlePaymentResponse( tryParseJson( xhr.responseText ) );
		} );

		$( document.body ).on( 'checkout_place_order_success', function ( event, result ) {
			if ( handlePaymentResponse( result ) && result ) {
				result.redirect = false;
			}
		} );
	}

	// Gutenberg block checkout uses wp.apiFetch (XMLHttpRequest under the hood).
	if ( window.XMLHttpRequest && ! window._vmsEfwpXhrPatched ) {
		window._vmsEfwpXhrPatched = true;

		var origOpen = XMLHttpRequest.prototype.open;
		var origSend = XMLHttpRequest.prototype.send;

		XMLHttpRequest.prototype.open = function ( method, url ) {
			this._vefwpMethod = method;
			this._vefwpUrl = url;
			return origOpen.apply( this, arguments );
		};

		XMLHttpRequest.prototype.send = function () {
			var xhr = this;

			xhr.addEventListener( 'load', function () {
				if ( ! isCheckoutRequest( xhr._vefwpUrl, xhr._vefwpMethod ) ) {
					return;
				}
				handlePaymentResponse( tryParseJson( xhr.responseText ) );
			} );

			return origSend.apply( this, arguments );
		};
	}

	// wp.apiFetch middleware — primary hook for WooCommerce Blocks checkout.
	if ( window.wp && window.wp.apiFetch && ! window._vmsEfwpApiFetchPatched ) {
		window._vmsEfwpApiFetchPatched = true;

		window.wp.apiFetch.use( function ( options, next ) {
			var path = options && ( options.path || options.url ) ? ( options.path || options.url ) : '';
			var method = options && options.method ? options.method : 'GET';

			return next( options ).then( function ( response ) {
				if ( isCheckoutRequest( path, method ) ) {
					handlePaymentResponse( response );
				}
				return response;
			} );
		} );
	}

	// Native fetch fallback.
	if ( window.fetch && ! window._vmsEfwpFetchPatched ) {
		window._vmsEfwpFetchPatched = true;
		var originalFetch = window.fetch.bind( window );

		window.fetch = function ( input, init ) {
			var url = typeof input === 'string' ? input : ( input && input.url ? input.url : '' );
			var method = init && init.method ? init.method : 'GET';

			return originalFetch( input, init ).then( function ( response ) {
				if ( ! isCheckoutRequest( url, method ) || ! response || ! response.clone ) {
					return response;
				}

				return response.clone().text().then( function ( body ) {
					handlePaymentResponse( tryParseJson( body ) );
					return response;
				} ).catch( function () {
					return response;
				} );
			} );
		};
	}

	window.VmsEfwpCheckout = {
		open: openOverlay,
		handlePaymentResponse: handlePaymentResponse,
		openForOrder: fetchOverlayForOrder,
		handlePendingFastSpring: handlePendingFastSpring,
		isPending: function () {
			return state.pendingFastSpring;
		},
	};

}( window.jQuery, window.vmsEfwpCheckout || {} ) );
