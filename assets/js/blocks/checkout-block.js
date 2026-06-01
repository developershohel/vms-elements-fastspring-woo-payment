( function () {
	'use strict';

	var wc = window.wc;
	var wp = window.wp;

	if ( ! wc || ! wc.wcBlocksRegistry || ! wc.wcSettings ) {
		console.error( '[WP FastSpring] wc.wcBlocksRegistry or wc.wcSettings is missing — Blocks JS deps did not load.' );
		return;
	}
	if ( ! wp || ! wp.element ) {
		console.error( '[WP FastSpring] wp.element is missing — Blocks JS deps did not load.' );
		return;
	}

	var registerPaymentMethod = wc.wcBlocksRegistry.registerPaymentMethod;
	var getSetting = wc.wcSettings.getSetting;
	var createElement = wp.element.createElement;
	var decodeEntities = ( wp.htmlEntities && wp.htmlEntities.decodeEntities ) || function ( s ) { return s; };

	var settings = getSetting( 'wp_fastspring_data', null );

	if ( ! settings ) {
		console.warn( '[WP FastSpring] No "wp_fastspring_data" was injected into wc.wcSettings. The Blocks payment method type did not register on the server side.' );
		return;
	}

	var label = decodeEntities( settings.title || 'FastSpring' );
	var description = decodeEntities( settings.description || '' );

	var Label = function () {
		return createElement( 'span', { className: 'wpfs-block-label' }, label );
	};

	var Content = function () {
		return createElement( 'div', { className: 'wpfs-block-method' }, description );
	};

	registerPaymentMethod( {
		name: 'wp_fastspring',
		label: createElement( Label, null ),
		ariaLabel: label,
		content: createElement( Content, null ),
		edit: createElement( Content, null ),
		canMakePayment: function () { return true; },
		supports: {
			features: ( settings.supports && settings.supports.length ) ? settings.supports : [ 'products' ]
		}
	} );

	console.info( '[WP FastSpring] Registered Blocks payment method: wp_fastspring' );
} )();
