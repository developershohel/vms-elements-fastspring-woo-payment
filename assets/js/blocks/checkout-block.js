( function () {
	'use strict';

	var wc = window.wc;
	var wp = window.wp;

	if ( ! wc || ! wc.wcBlocksRegistry || ! wc.wcSettings ) {
		console.error( '[VMS Elements Fastspring Woo Payment] wc.wcBlocksRegistry or wc.wcSettings is missing — Blocks JS deps did not load.' );
		return;
	}
	if ( ! wp || ! wp.element ) {
		console.error( '[VMS Elements Fastspring Woo Payment] wp.element is missing — Blocks JS deps did not load.' );
		return;
	}

	var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting = wc.wcSettings.getSetting;
	var createElement = wp.element.createElement;
	var decodeEntities = ( wp.htmlEntities && wp.htmlEntities.decodeEntities ) || function ( s ) { return s; };

	var settings = getSetting( 'vms_efwp_data', null );

	if ( ! settings ) {
		console.warn( '[VMS Elements Fastspring Woo Payment] No "vms_efwp_data" was injected into wc.wcSettings. The Blocks payment method type did not register on the server side.' );
		return;
	}

	var label = decodeEntities( settings.title || 'FastSpring' );
	var description = decodeEntities( settings.description || '' );

	var Label = function () {
		return createElement( 'span', { className: 'vms-efwp-block-label' }, label );
	};

	var Content = function () {
		return createElement( 'div', { className: 'vms-efwp-block-method' }, description );
	};

	registerPaymentMethod( {
		name: 'vms_efwp',
		label: createElement( Label, null ),
		ariaLabel: label,
		content: createElement( Content, null ),
		edit: createElement( Content, null ),
		canMakePayment: function () {
			return !! settings.available;
		},
		supports: {
			features: ( settings.supports && settings.supports.length ) ? settings.supports : [ 'products' ]
		}
	} );

	if ( ! wp.data || ! wp.data.subscribe ) {
		return;
	}

	var clearedRedirectForOrder = {};
	var openedForOrder = {};
	var wasProcessing = false;

	function isFastSpringSelected() {
		var paymentStore = wp.data.select( 'wc/store/payment' );
		if ( ! paymentStore || ! paymentStore.getActivePaymentMethod ) {
			return false;
		}
		return 'vms_efwp' === paymentStore.getActivePaymentMethod();
	}

	function clearBlocksRedirect( orderId ) {
		if ( ! orderId || clearedRedirectForOrder[ orderId ] ) {
			return;
		}

		var checkoutDispatch = wp.data.dispatch( 'wc/store/checkout' );
		if ( ! checkoutDispatch ) {
			return;
		}

		clearedRedirectForOrder[ orderId ] = true;

		if ( typeof checkoutDispatch.setRedirectUrl === 'function' ) {
			checkoutDispatch.setRedirectUrl( '' );
		}

		if ( typeof checkoutDispatch.__internalSetIdle === 'function' ) {
			checkoutDispatch.__internalSetIdle();
		}
	}

	function openPopupForOrder( orderId ) {
		if ( ! orderId || openedForOrder[ orderId ] ) {
			return;
		}

		if ( ! window.VMS_EFWP_CheckoutApi ) {
			return;
		}

		openedForOrder[ orderId ] = true;

		if ( window.VMS_EFWP_CheckoutApi.handlePendingFastSpring ) {
			window.VMS_EFWP_CheckoutApi.handlePendingFastSpring( orderId );
		} else if ( window.VMS_EFWP_CheckoutApi.openForOrder ) {
			window.VMS_EFWP_CheckoutApi.openForOrder( orderId, '' );
		}
	}

	wp.data.subscribe( function () {
		if ( ! isFastSpringSelected() ) {
			wasProcessing = false;
			return;
		}

		var checkoutStore = wp.data.select( 'wc/store/checkout' );
		if ( ! checkoutStore ) {
			return;
		}

		var processing = checkoutStore.isProcessing && checkoutStore.isProcessing();
		var orderId = checkoutStore.getOrderId && checkoutStore.getOrderId();
		var hasError = checkoutStore.hasError && checkoutStore.hasError();
		var redirectUrl = checkoutStore.getRedirectUrl && checkoutStore.getRedirectUrl();

		if ( orderId && redirectUrl ) {
			clearBlocksRedirect( orderId );
		}

		if ( wasProcessing && ! processing && orderId && ! hasError ) {
			clearBlocksRedirect( orderId );
			openPopupForOrder( orderId );
		}

		wasProcessing = processing;
	} );

	console.info( '[VMS Elements Fastspring Woo Payment] Registered Blocks payment method: vms_efwp' );
} )();
