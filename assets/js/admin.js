/* global jQuery, Chart, WPFastSpring */
( function ( $ ) {
	'use strict';

	var revenueChart = null;
	var subscriptionChart = null;

	function fmtMoney( n ) {
		var sym = ( WPFastSpring && WPFastSpring.currency ) ? WPFastSpring.currency : '$';
		return sym + Number( n || 0 ).toFixed( 2 ).replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	function ajax( action, data ) {
		return $.post( WPFastSpring.ajax_url, $.extend( { action: action, nonce: WPFastSpring.nonce }, data || {} ) );
	}

	function buildRevenueChart( ctx, daily ) {
		var labels = daily.map( function ( r ) { return r.date.slice( 5 ); } );
		var revenue = daily.map( function ( r ) { return Number( r.revenue ); } );
		var orders = daily.map( function ( r ) { return Number( r.orders ); } );

		if ( revenueChart ) { revenueChart.destroy(); }

		revenueChart = new Chart( ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Revenue',
						data: revenue,
						borderColor: '#4f46e5',
						backgroundColor: 'rgba(79, 70, 229, 0.12)',
						tension: 0.35,
						fill: true,
						yAxisID: 'y1',
						pointRadius: 0,
						pointHoverRadius: 4,
						borderWidth: 2
					},
					{
						label: 'Orders',
						data: orders,
						borderColor: '#16a34a',
						backgroundColor: 'rgba(22, 163, 74, 0.0)',
						tension: 0.35,
						fill: false,
						yAxisID: 'y2',
						pointRadius: 0,
						pointHoverRadius: 4,
						borderWidth: 2,
						borderDash: [ 4, 4 ]
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				interaction: { mode: 'index', intersect: false },
				plugins: { legend: { position: 'bottom' } },
				scales: {
					y1: { beginAtZero: true, position: 'left', ticks: { callback: function ( v ) { return fmtMoney( v ); } } },
					y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } }
				}
			}
		} );
	}

	function buildSubscriptionChart( ctx, subs ) {
		if ( subscriptionChart ) { subscriptionChart.destroy(); }
		var labels = [ 'Active', 'Paused', 'Trial', 'Overdue', 'Canceled', 'Deactivated' ];
		var values = [
			subs.active || 0,
			subs.paused || 0,
			subs.trial || 0,
			subs.overdue || 0,
			subs.canceled || 0,
			subs.deactivated || 0
		];
		subscriptionChart = new Chart( ctx, {
			type: 'doughnut',
			data: {
				labels: labels,
				datasets: [ {
					data: values,
					backgroundColor: [ '#4f46e5', '#f59e0b', '#0ea5e9', '#fb923c', '#dc2626', '#94a3b8' ],
					borderWidth: 0
				} ]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: { legend: { position: 'bottom' } },
				cutout: '60%'
			}
		} );
	}

	function renderTopProducts( rows ) {
		var $tbody = $( '#wpfs-top-products tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + WPFastSpring.i18n.no_data + '</td></tr>' );
			return;
		}
		$.each( rows, function ( _, p ) {
			$tbody.append( $( '<tr/>' )
				.append( $( '<td/>' ).text( p.display || p.product ) )
				.append( $( '<td/>' ).text( p.quantity ) )
				.append( $( '<td/>' ).text( fmtMoney( p.revenue ) ) )
			);
		} );
	}

	function renderTopCountries( rows ) {
		var $tbody = $( '#wpfs-top-countries tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + WPFastSpring.i18n.no_data + '</td></tr>' );
			return;
		}
		$.each( rows, function ( _, c ) {
			$tbody.append( $( '<tr/>' )
				.append( $( '<td/>' ).text( c.country ) )
				.append( $( '<td/>' ).text( c.orders ) )
				.append( $( '<td/>' ).text( fmtMoney( c.revenue ) ) )
			);
		} );
	}

	function renderRecentOrders( rows ) {
		var $tbody = $( '#wpfs-recent-orders tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="5">' + WPFastSpring.i18n.no_data + '</td></tr>' );
			return;
		}
		$.each( rows, function ( _, o ) {
			$tbody.append(
				$( '<tr/>' )
					.append( $( '<td/>' ).html( '<code>' + o.fs_order_id + '</code>' ) )
					.append( $( '<td/>' ).text( ( o.customer_name || '' ) + ( o.email ? ' (' + o.email + ')' : '' ) ) )
					.append( $( '<td/>' ).text( ( o.currency || '' ) + ' ' + Number( o.total ).toFixed( 2 ) ) )
					.append( $( '<td/>' ).html( '<span class="wpfs-status wpfs-status--' + o.status + '">' + o.status + '</span>' ) )
					.append( $( '<td/>' ).text( o.created_at ) )
			);
		} );
	}

	function loadDashboard() {
		var $spinner = $( '#wpfs-trend-spinner' );
		$spinner.show();

		var range = $( '#wpfs-range' ).val() || 30;
		var includeTest = $( '#wpfs-include-test' ).is( ':checked' ) ? 1 : 0;

		ajax( 'wp_fastspring_dashboard_data', { range: range, include_test: includeTest } )
			.done( function ( resp ) {
				if ( ! resp || ! resp.success ) {
					return;
				}
				var d = resp.data;
				var revCanvas = document.getElementById( 'wpfs-revenue-chart' );
				var subCanvas = document.getElementById( 'wpfs-subscription-chart' );
				if ( revCanvas ) { buildRevenueChart( revCanvas.getContext( '2d' ), d.daily ); }
				if ( subCanvas ) { buildSubscriptionChart( subCanvas.getContext( '2d' ), d.subscriptions ); }
				renderTopProducts( d.top_products );
				renderTopCountries( d.top_countries );
				renderRecentOrders( d.recent_orders );
			} )
			.always( function () {
				$spinner.hide();
			} );
	}

	$( function () {
		// Dashboard.
		if ( document.getElementById( 'wpfs-revenue-chart' ) ) {
			loadDashboard();
			$( '#wpfs-range, #wpfs-include-test' ).on( 'change', loadDashboard );
		}

		// Settings: generate / copy webhook secret.
		function generateSecret() {
			var bytes = new Uint8Array( 32 );
			if ( window.crypto && window.crypto.getRandomValues ) {
				window.crypto.getRandomValues( bytes );
			} else {
				for ( var i = 0; i < 32; i++ ) { bytes[ i ] = Math.floor( Math.random() * 256 ); }
			}
			var hex = '';
			for ( var j = 0; j < bytes.length; j++ ) {
				hex += ( '0' + bytes[ j ].toString( 16 ) ).slice( -2 );
			}
			return hex;
		}

		function flash( $btn, label ) {
			var orig = $btn.text();
			$btn.text( label );
			setTimeout( function () { $btn.text( orig ); }, 1500 );
		}

		$( document ).on( 'click', '.wpfs-generate-secret', function () {
			var target = $( this ).data( 'target' );
			var secret = generateSecret();
			$( '#' + target ).val( secret );
			flash( $( this ), 'Generated' );
		} );

		$( document ).on( 'click', '.wpfs-copy-secret', function () {
			var target = $( this ).data( 'target' );
			var $input = $( '#' + target );
			if ( ! $input.val() ) { return; }
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( $input.val() );
			} else {
				$input.select();
				document.execCommand( 'copy' );
			}
			flash( $( this ), 'Copied!' );
		} );

		// Settings: test connection.
		$( '#wpfs-test-connection' ).on( 'click', function () {
			var $btn = $( this );
			var $r = $( '#wpfs-test-result' ).removeClass( 'is-ok is-err' ).text( WPFastSpring.i18n.loading );
			$btn.prop( 'disabled', true );
			ajax( 'wp_fastspring_test_connection' )
				.done( function ( resp ) {
					if ( resp.success ) {
						$r.addClass( 'is-ok' ).text( resp.data.message );
					} else {
						$r.addClass( 'is-err' ).text( ( resp.data && resp.data.message ) || WPFastSpring.i18n.error );
					}
				} )
				.fail( function () { $r.addClass( 'is-err' ).text( WPFastSpring.i18n.error ); } )
				.always( function () { $btn.prop( 'disabled', false ); } );
		} );

		// Mode switch active styling.
		$( document ).on( 'change', 'input[name="mode"]', function () {
			$( '.wpfs-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.wpfs-mode-option' ).addClass( 'is-active' );
		} );

		// Pricing strategy: toggle Custom Price product path field + active styling.
		$( document ).on( 'change', 'input[name="pricing_strategy"]', function () {
			$( '.wpfs-pricing-strategy .wpfs-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.wpfs-mode-option' ).addClass( 'is-active' );
			$( '.wpfs-custom-price-row' ).toggle( 'single_custom_price' === $( this ).val() );
		} );

		// JSON modal: open.
		$( document ).on( 'click', '.wpfs-view-json', function () {
			var json = $( this ).attr( 'data-json' );
			try {
				var parsed = JSON.parse( json );
				json = JSON.stringify( parsed, null, 2 );
			} catch ( e ) {}
			$( '#wpfs-json-modal-body' ).text( json );
			$( '#wpfs-json-modal' ).removeAttr( 'hidden' );
		} );

		$( document ).on( 'click', '[data-wpfs-close]', function () {
			$( '#wpfs-json-modal' ).attr( 'hidden', true );
		} );

		$( document ).on( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				$( '#wpfs-json-modal' ).attr( 'hidden', true );
			}
		} );

		// JSON copy.
		$( document ).on( 'click', '#wpfs-json-copy', function () {
			var text = $( '#wpfs-json-modal-body' ).text();
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text );
			} else {
				var $tmp = $( '<textarea>' ).val( text ).appendTo( 'body' ).select();
				document.execCommand( 'copy' );
				$tmp.remove();
			}
			$( this ).text( 'Copied!' );
			var self = this;
			setTimeout( function () { $( self ).text( WPFastSpring.i18n.copy_json || 'Copy JSON' ); }, 1500 );
		} );

		// Toggle inline create forms.
		$( document ).on( 'click', '[data-wpfs-open-form]', function () {
			var key = $( this ).data( 'wpfs-open-form' );
			var $form = $( '[data-wpfs-form="' + key + '"]' );
			$form.attr( 'hidden', false ).get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'nearest' } );

			// If opened from "New", reset to create mode.
			if ( 'wpfs-new-product' === this.id ) {
				resetProductForm();
			}
		} );

		$( document ).on( 'click', '[data-wpfs-close-form]', function () {
			var key = $( this ).data( 'wpfs-close-form' );
			$( '[data-wpfs-form="' + key + '"]' ).attr( 'hidden', true );
		} );

		// Product edit: prefill form from row data.
		$( document ).on( 'click', '.wpfs-edit-product', function () {
			var raw = $( this ).attr( 'data-product' );
			if ( ! raw ) { return; }
			try {
				var p = JSON.parse( raw );
				prefillProductForm( p );
				$( '[data-wpfs-form="save-product"]' )
					.attr( 'hidden', false )
					.get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} catch ( e ) {}
		} );

		function prefillProductForm( p ) {
			var $form = $( '#wpfs-product-form' );
			$form.find( '[data-wpfs-field="product"]' )
				.val( p.product || '' )
				.prop( 'readonly', true ); // path is the identifier; cannot change after creation
			$form.find( '[data-wpfs-field="display"]' ).val(
				p.display ? ( p.display.en || Object.values( p.display )[ 0 ] || '' ) : ''
			);
			$form.find( '[data-wpfs-field="sku"]' ).val( p.sku || '' );
			$form.find( '[data-wpfs-field="format"]' ).val( p.format || 'digital' );
			$form.find( '[data-wpfs-field="image"]' ).val( p.image || '' );
			var summary = '', full = '';
			if ( p.description ) {
				summary = ( p.description.summary && ( p.description.summary.en || Object.values( p.description.summary )[ 0 ] ) ) || '';
				full    = ( p.description.full    && ( p.description.full.en    || Object.values( p.description.full )[ 0 ]    ) ) || '';
			}
			$form.find( '[data-wpfs-field="summary"]' ).val( summary );
			$form.find( '[data-wpfs-field="full"]' ).val( full );

			// Rebuild pricing rows.
			var $rows = $( '#wpfs-pricing-rows' ).empty();
			var prices = ( p.pricing && p.pricing.price ) ? p.pricing.price : { USD: 0 };
			$.each( prices, function ( cur, val ) {
				$rows.append( buildPricingRow( cur, val ) );
			} );

			$( '#wpfs-product-form-title' ).text( 'Edit product' );
			$( '#wpfs-product-submit' ).text( 'Update product' );
		}

		function resetProductForm() {
			var $form = $( '#wpfs-product-form' );
			if ( ! $form.length ) { return; }
			$form[ 0 ].reset();
			$form.find( '[data-wpfs-field="product"]' ).prop( 'readonly', false );
			$( '#wpfs-pricing-rows' ).html( buildPricingRow( 'USD', '0' ) );
			$( '#wpfs-product-form-title' ).text( 'Create product' );
			$( '#wpfs-product-submit' ).text( 'Create product' );
		}

		function buildPricingRow( cur, val ) {
			return '<tr>' +
				'<td><input type="text" name="pricing[currency][]" maxlength="3" value="' + ( cur || '' ) + '" /></td>' +
				'<td><input type="number" step="0.01" name="pricing[price][]" required value="' + ( typeof val === 'undefined' ? '0' : val ) + '" /></td>' +
				'<td><button type="button" class="button button-small wpfs-pricing-remove">&times;</button></td>' +
				'</tr>';
		}

		$( document ).on( 'click', '#wpfs-pricing-add', function () {
			$( '#wpfs-pricing-rows' ).append( buildPricingRow( '', '' ) );
		} );
		$( document ).on( 'click', '.wpfs-pricing-remove', function () {
			var $tr = $( this ).closest( 'tr' );
			if ( $( '#wpfs-pricing-rows tr' ).length > 1 ) { $tr.remove(); }
		} );

		// Subscription actions.
		$( document ).on( 'click', '.wpfs-sync-sub', function () {
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'wp_fastspring_sync_subscription', { id: $btn.data( 'id' ) } )
				.always( function () {
					window.location.reload();
				} );
		} );
		$( document ).on( 'click', '.wpfs-cancel-sub', function () {
			if ( ! window.confirm( WPFastSpring.i18n.confirm_cancel ) ) { return; }
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'wp_fastspring_cancel_subscription', { id: $btn.data( 'id' ) } )
				.always( function () {
					window.location.reload();
				} );
		} );
	} );
} )( jQuery );
