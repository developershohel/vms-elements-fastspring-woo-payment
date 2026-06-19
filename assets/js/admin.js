/* global jQuery, Chart, VMSEFWP */
( function ( $ ) {
	'use strict';

	var revenueChart = null;
	var subscriptionChart = null;

	function fmtMoney( n ) {
		var sym = ( VMSEFWP && VMSEFWP.currency ) ? VMSEFWP.currency : '$';
		return sym + Number( n || 0 ).toFixed( 2 ).replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	function ajax( action, data ) {
		return $.post( VMSEFWP.ajax_url, $.extend( { action: action, nonce: VMSEFWP.nonce }, data || {} ) );
	}

	function buildRevenueChart( ctx, daily ) {
		if ( typeof Chart === 'undefined' ) {
			return false;
		}

		daily = Array.isArray( daily ) ? daily : [];
		var labels = daily.map( function ( r ) { return r.date ? r.date.slice( 5 ) : ''; } );
		var revenue = daily.map( function ( r ) { return Number( r.revenue ) || 0; } );
		var orders = daily.map( function ( r ) { return Number( r.orders ) || 0; } );

		if ( revenueChart ) { revenueChart.destroy(); }

		revenueChart = new Chart( ctx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: ( VMSEFWP.i18n && VMSEFWP.i18n.revenue_label ) ? VMSEFWP.i18n.revenue_label : 'Revenue',
						data: revenue,
						borderColor: '#4f46e5',
						backgroundColor: 'rgba(79, 70, 229, 0.12)',
						tension: 0.35,
						fill: true,
						yAxisID: 'y',
						pointRadius: 0,
						pointHoverRadius: 4,
						borderWidth: 2
					},
					{
						label: ( VMSEFWP.i18n && VMSEFWP.i18n.orders_label ) ? VMSEFWP.i18n.orders_label : 'Orders',
						data: orders,
						borderColor: '#16a34a',
						backgroundColor: 'rgba(22, 163, 74, 0.0)',
						tension: 0.35,
						fill: false,
						yAxisID: 'y1',
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
					x: {
						grid: { display: false }
					},
					y: {
						type: 'linear',
						beginAtZero: true,
						position: 'left',
						ticks: { callback: function ( v ) { return fmtMoney( v ); } }
					},
					y1: {
						type: 'linear',
						beginAtZero: true,
						position: 'right',
						grid: { drawOnChartArea: false },
						ticks: { precision: 0 }
					}
				}
			}
		} );
		return true;
	}

	function buildSubscriptionChart( ctx, subs ) {
		if ( typeof Chart === 'undefined' ) {
			return false;
		}
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
		return true;
	}

	function orderLabel( count ) {
		var tpl = ( count === 1 )
			? ( ( VMSEFWP.i18n && VMSEFWP.i18n.order_singular ) ? VMSEFWP.i18n.order_singular : '%d order' )
			: ( ( VMSEFWP.i18n && VMSEFWP.i18n.order_plural ) ? VMSEFWP.i18n.order_plural : '%d orders' );
		return tpl.replace( '%d', count );
	}

	function renderKpis( kpis, subs ) {
		if ( ! kpis ) {
			return;
		}

		$.each( kpis, function ( key, summary ) {
			var $card = $( '[data-kpi="' + key + '"]' );
			if ( ! $card.length || ! summary ) {
				return;
			}
			$card.find( '[data-kpi-value="revenue"]' ).text( fmtMoney( summary.revenue ) );
			$card.find( '[data-kpi-value="orders"]' ).text( orderLabel( Number( summary.orders ) || 0 ) );
		} );

		if ( kpis.all_time ) {
			$( '[data-kpi="refunded"] [data-kpi-value="refunded"]' ).text( fmtMoney( kpis.all_time.refunded ) );
		}

		if ( subs ) {
			var $subCard = $( '[data-kpi="subscriptions"]' );
			$subCard.find( '[data-kpi-value="active"]' ).text( Number( subs.active || 0 ).toLocaleString() );
			var mrrStrings = [];
			$.each( subs.mrr || {}, function ( cur, value ) {
				mrrStrings.push( cur + ' ' + Number( value ).toFixed( 2 ) );
			} );
			$subCard.find( '[data-kpi-value="mrr"]' ).text(
				mrrStrings.length
					? ( ( VMSEFWP.i18n && VMSEFWP.i18n.mrr_prefix ) ? VMSEFWP.i18n.mrr_prefix : 'MRR: ' ) + mrrStrings.join( ' / ' )
					: ( ( VMSEFWP.i18n && VMSEFWP.i18n.no_mrr ) ? VMSEFWP.i18n.no_mrr : 'No active recurring revenue yet.' )
			);
		}
	}

	function setChartError( message ) {
		var $err = $( '#vefwp-chart-error' );
		if ( ! $err.length ) {
			return;
		}
		if ( message ) {
			$err.text( message ).prop( 'hidden', false );
		} else {
			$err.text( '' ).prop( 'hidden', true );
		}
	}

	function setSpinner( visible ) {
		var $spinner = $( '#vefwp-trend-spinner' );
		if ( ! $spinner.length ) {
			return;
		}
		$spinner.prop( 'hidden', ! visible ).attr( 'aria-hidden', visible ? 'false' : 'true' );
	}

	function renderTopProducts( rows ) {
		var $tbody = $( '#vefwp-top-products tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + VMSEFWP.i18n.no_data + '</td></tr>' );
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
		var $tbody = $( '#vefwp-top-countries tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + VMSEFWP.i18n.no_data + '</td></tr>' );
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
		var $tbody = $( '#vefwp-recent-orders tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="5">' + VMSEFWP.i18n.no_data + '</td></tr>' );
			return;
		}
		$.each( rows, function ( _, o ) {
			$tbody.append(
				$( '<tr/>' )
					.append( $( '<td/>' ).html( $( '<code/>' ).text( o.fs_order_id || '' ) ) )
					.append( $( '<td/>' ).text( ( o.customer_name || '' ) + ( o.email ? ' (' + o.email + ')' : '' ) ) )
					.append( $( '<td/>' ).text( ( o.currency || '' ) + ' ' + Number( o.total ).toFixed( 2 ) ) )
					.append( $( '<td/>' ).html( $( '<span/>' ).addClass( 'vefwp-status vefwp-status--' + ( o.status || '' ) ).text( o.status || '' ) ) )
					.append( $( '<td/>' ).text( o.created_at || '' ) )
			);
		} );
	}

	function loadDashboard() {
		setSpinner( true );
		setChartError( '' );

		var range = $( '#vefwp-range' ).val() || 30;
		var includeTest = $( '#vefwp-include-test' ).is( ':checked' ) ? 1 : 0;

		ajax( 'vms_efwp_dashboard_data', { range: range, include_test: includeTest } )
			.done( function ( resp ) {
				if ( ! resp || ! resp.success || ! resp.data ) {
					setChartError( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error );
					return;
				}
				var d = resp.data;
				var revCanvas = document.getElementById( 'vefwp-revenue-chart' );
				var subCanvas = document.getElementById( 'vefwp-subscription-chart' );
				if ( revCanvas && ! buildRevenueChart( revCanvas.getContext( '2d' ), d.daily ) ) {
					setChartError( VMSEFWP.i18n.error );
				}
				if ( subCanvas ) { buildSubscriptionChart( subCanvas.getContext( '2d' ), d.subscriptions ); }
				renderKpis( d.kpis, d.subscriptions );
				renderTopProducts( d.top_products );
				renderTopCountries( d.top_countries );
				renderRecentOrders( d.recent_orders );
			} )
			.fail( function () {
				setChartError( VMSEFWP.i18n.error );
			} )
			.always( function () {
				setSpinner( false );
			} );
	}

	$( function () {
		// Dashboard.
		if ( document.getElementById( 'vefwp-revenue-chart' ) ) {
			loadDashboard();
			$( '#vefwp-range, #vefwp-include-test' ).on( 'change', loadDashboard );
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

		$( document ).on( 'click', '.vefwp-generate-secret', function () {
			var target = $( this ).data( 'target' );
			var secret = generateSecret();
			$( '#' + target ).val( secret );
			flash( $( this ), 'Generated' );
		} );

		$( document ).on( 'click', '.vefwp-copy-secret', function () {
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
		$( '#vefwp-test-connection' ).on( 'click', function () {
			var $btn = $( this );
			var $r = $( '#vefwp-test-result' ).removeClass( 'is-ok is-err' ).text( VMSEFWP.i18n.loading );
			$btn.prop( 'disabled', true );
			ajax( 'vms_efwp_test_connection' )
				.done( function ( resp ) {
					if ( resp.success ) {
						$r.addClass( 'is-ok' ).text( resp.data.message );
					} else {
						$r.addClass( 'is-err' ).text( ( resp.data && resp.data.message ) || VMSEFWP.i18n.error );
					}
				} )
				.fail( function () { $r.addClass( 'is-err' ).text( VMSEFWP.i18n.error ); } )
				.always( function () { $btn.prop( 'disabled', false ); } );
		} );

		// Mode switch active styling.
		$( document ).on( 'change', 'input[name="mode"]', function () {
			$( '.vefwp-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.vefwp-mode-option' ).addClass( 'is-active' );
		} );

		// Pricing strategy: toggle Custom Price product path field + active styling.
		$( document ).on( 'change', 'input[name="pricing_strategy"]', function () {
			$( '.vefwp-pricing-strategy .vefwp-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.vefwp-mode-option' ).addClass( 'is-active' );
			$( '.vefwp-custom-price-row' ).toggle( 'single_custom_price' === $( this ).val() );
		} );

		// JSON modal: open.
		$( document ).on( 'click', '.vefwp-view-json', function () {
			var json = $( this ).attr( 'data-json' );
			try {
				var parsed = JSON.parse( json );
				json = JSON.stringify( parsed, null, 2 );
			} catch ( e ) {}
			$( '#vefwp-json-modal-body' ).text( json );
			$( '#vefwp-json-modal' ).removeAttr( 'hidden' );
		} );

		$( document ).on( 'click', '[data-vefwp-close]', function () {
			$( '#vefwp-json-modal' ).attr( 'hidden', true );
		} );

		$( document ).on( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				$( '#vefwp-json-modal' ).attr( 'hidden', true );
			}
		} );

		// JSON copy.
		$( document ).on( 'click', '#vefwp-json-copy', function () {
			var text = $( '#vefwp-json-modal-body' ).text();
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text );
			} else {
				var $tmp = $( '<textarea>' ).val( text ).appendTo( 'body' ).select();
				document.execCommand( 'copy' );
				$tmp.remove();
			}
			$( this ).text( 'Copied!' );
			var self = this;
			setTimeout( function () { $( self ).text( VMSEFWP.i18n.copy_json || 'Copy JSON' ); }, 1500 );
		} );

		$( document ).on( 'change', '[name="same_as_bill_to"]', function () {
			$( '[data-vefwp-deliver-fields]' ).attr( 'hidden', $( this ).is( ':checked' ) );
		} );
		$( '[name="same_as_bill_to"]' ).trigger( 'change' );

		function syncPartialReturnFields() {
			var isPartial = 'PARTIAL' === $( '#vefwp-return-refund-type' ).val();
			$( '[data-vefwp-partial-return-fields]' ).attr( 'hidden', ! isPartial );
		}
		$( document ).on( 'change', '#vefwp-return-refund-type', syncPartialReturnFields );
		syncPartialReturnFields();

		// Toggle inline create forms.
		$( document ).on( 'click', '[data-vefwp-open-form]', function () {
			var key = $( this ).data( 'vefwp-open-form' );
			var $form = $( '[data-vefwp-form="' + key + '"]' );
			$form.attr( 'hidden', false ).get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'nearest' } );

			// If opened from "New", reset to create mode.
			if ( 'vefwp-new-product' === this.id ) {
				resetProductForm();
			}
		} );

		/**
		 * Convert display text to a FastSpring product path (WordPress-style slug).
		 *
		 * @param {string} text Raw input.
		 * @return {string}
		 */
		function vefwpSlugify( text ) {
			if ( ! text ) {
				return '';
			}
			var slug = text.toString().trim().toLowerCase();
			if ( slug.normalize ) {
				slug = slug.normalize( 'NFD' ).replace( /[\u0300-\u036f]/g, '' );
			}
			return slug
				.replace( /[^a-z0-9\s-]/g, '' )
				.replace( /\s+/g, '-' )
				.replace( /-+/g, '-' )
				.replace( /^-+|-+$/g, '' );
		}

		/**
		 * Bind auto-slug behaviour: name field generates path until the user edits the path.
		 *
		 * @param {jQuery} $form Form element.
		 */
		function bindSlugForm( $form ) {
			if ( ! $form.length || $form.data( 'vefwpSlugBound' ) ) {
				return;
			}

			var $source = $form.find( '[data-vefwp-slug-source]' );
			var $target = $form.find( '[data-vefwp-slug-target]' ).first();
			if ( ! $source.length || ! $target.length ) {
				return;
			}

			$form.data( 'vefwpSlugBound', true );
			$form.data( 'vefwpSlugManual', false );

			$source.on( 'input.vefwpSlug', function () {
				if ( $target.prop( 'readonly' ) || $form.data( 'vefwpSlugManual' ) ) {
					return;
				}
				$target.val( vefwpSlugify( $source.val() ) );
			} );

			$target.on( 'input.vefwpSlug', function () {
				if ( ! $target.prop( 'readonly' ) ) {
					$form.data( 'vefwpSlugManual', true );
				}
			} );

			$target.on( 'blur.vefwpSlug', function () {
				if ( $target.prop( 'readonly' ) ) {
					return;
				}
				$target.val( vefwpSlugify( $target.val() ) );
			} );
		}

		function resetSlugForm( $form ) {
			$form.data( 'vefwpSlugManual', false );
		}

		$( '[data-vefwp-slug-form]' ).each( function () {
			bindSlugForm( $( this ) );
		} );

		// Standalone slug fields (e.g. settings catch-all path) — sanitize on blur only.
		$( document ).on( 'blur', '[data-vefwp-slug-target]', function () {
			var $field = $( this );
			if ( $field.prop( 'readonly' ) || $field.closest( '[data-vefwp-slug-form]' ).length ) {
				return;
			}
			$field.val( vefwpSlugify( $field.val() ) );
		} );

		function syncCouponDiscountUi() {
			var isFlat = 'flat' === $( '#vefwp-coupon-discount-type' ).val() || $( '#vefwp-order-level-discount' ).is( ':checked' );
			$( '[data-vefwp-flat-only]' ).toggle( isFlat );
			if ( $( '#vefwp-order-level-discount' ).is( ':checked' ) ) {
				$( '#vefwp-coupon-discount-type' ).val( 'flat' );
			}
		}
		$( document ).on( 'change', '#vefwp-coupon-discount-type, #vefwp-order-level-discount', syncCouponDiscountUi );
		syncCouponDiscountUi();

		$( document ).on( 'click', '[data-vefwp-close-form]', function () {
			var key = $( this ).data( 'vefwp-close-form' );
			$( '[data-vefwp-form="' + key + '"]' ).attr( 'hidden', true );
		} );

		// Product edit: prefill form from row data.
		$( document ).on( 'click', '.vefwp-edit-product', function () {
			var raw = $( this ).attr( 'data-product' );
			if ( ! raw ) { return; }
			try {
				var p = JSON.parse( raw );
				prefillProductForm( p );
				$( '[data-vefwp-form="save-product"]' )
					.attr( 'hidden', false )
					.get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} catch ( e ) {}
		} );

		function prefillProductForm( p ) {
			var $form = $( '#vefwp-product-form' );
			resetSlugForm( $form );
			$form.find( '[data-vefwp-field="product"]' )
				.val( p.product || '' )
				.prop( 'readonly', true ); // path is the identifier; cannot change after creation
			$form.find( '[data-vefwp-field="display"]' ).val(
				p.display ? ( p.display.en || Object.values( p.display )[ 0 ] || '' ) : ''
			);
			$form.find( '[data-vefwp-field="sku"]' ).val( p.sku || '' );
			var format = p.format || 'digital';
			if ( format === 'service' ) {
				format = 'digital';
			}
			$form.find( '[data-vefwp-field="format"]' ).val( format );
			$form.find( '[data-vefwp-field="image"]' ).val( p.image || '' );
			$form.find( '[data-vefwp-field="badge"]' ).val(
				p.badge ? ( p.badge.en || Object.values( p.badge )[ 0 ] || '' ) : ''
			);
			$form.find( '[data-vefwp-field="rank"]' ).val( p.rank || 0 );
			var summary = '', full = '', action = '', fulfillment = '';
			if ( p.description ) {
				summary = ( p.description.summary && ( p.description.summary.en || Object.values( p.description.summary )[ 0 ] ) ) || '';
				full    = ( p.description.full    && ( p.description.full.en    || Object.values( p.description.full )[ 0 ]    ) ) || '';
				action  = ( p.description.action  && ( p.description.action.en  || Object.values( p.description.action )[ 0 ]  ) ) || '';
			}
			if ( p.fulfillment && p.fulfillment.instructions ) {
				fulfillment = p.fulfillment.instructions.en || Object.values( p.fulfillment.instructions )[ 0 ] || '';
			}
			$form.find( '[data-vefwp-field="summary"]' ).val( summary );
			$form.find( '[data-vefwp-field="action"]' ).val( action );
			$form.find( '[data-vefwp-field="full"]' ).val( full );
			$form.find( '[data-vefwp-field="fulfillment"]' ).val( fulfillment );

			// Rebuild pricing rows.
			var $rows = $( '#vefwp-pricing-rows' ).empty();
			var prices = ( p.pricing && p.pricing.price ) ? p.pricing.price : { USD: 0 };
			$.each( prices, function ( cur, val ) {
				$rows.append( buildPricingRow( cur, val ) );
			} );

			$( '#vefwp-product-form-title' ).text( 'Edit product' );
			$( '#vefwp-product-submit' ).text( 'Update product' );
		}

		function resetProductForm() {
			var $form = $( '#vefwp-product-form' );
			if ( ! $form.length ) { return; }
			$form[ 0 ].reset();
			resetSlugForm( $form );
			$form.find( '[data-vefwp-field="product"]' ).prop( 'readonly', false );
			$( '#vefwp-pricing-rows' ).html( buildPricingRow( 'USD', '0' ) );
			$( '#vefwp-product-form-title' ).text( 'Create product' );
			$( '#vefwp-product-submit' ).text( 'Create product' );
		}

		function buildPricingRow( cur, val ) {
			return '<tr>' +
				'<td><input type="text" name="pricing[currency][]" maxlength="3" value="' + ( cur || '' ) + '" /></td>' +
				'<td><input type="number" step="0.01" name="pricing[price][]" required value="' + ( typeof val === 'undefined' ? '0' : val ) + '" /></td>' +
				'<td><button type="button" class="button button-small vefwp-pricing-remove">&times;</button></td>' +
				'</tr>';
		}

		$( document ).on( 'click', '#vefwp-pricing-add', function () {
			$( '#vefwp-pricing-rows' ).append( buildPricingRow( '', '' ) );
		} );
		$( document ).on( 'click', '.vefwp-pricing-remove', function () {
			var $tr = $( this ).closest( 'tr' );
			if ( $( '#vefwp-pricing-rows tr' ).length > 1 ) { $tr.remove(); }
		} );

		// Subscription actions.
		function showSubActionNotice( message, isError ) {
			var $notice = $( '<div class="notice ' + ( isError ? 'notice-error' : 'notice-success' ) + ' is-dismissible"><p></p></div>' );
			$notice.find( 'p' ).text( message );
			$( '.vefwp-wrap' ).first().prepend( $notice );
		}

		$( document ).on( 'click', '.vefwp-sync-sub', function () {
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_sync_subscription', { id: $btn.data( 'id' ) } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						window.location.reload();
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );
		$( document ).on( 'click', '.vefwp-cancel-sub', function () {
			var immediate = $( this ).data( 'immediate' ) ? '1' : '0';
			var message = immediate ? VMSEFWP.i18n.confirm_immediate_cancel : VMSEFWP.i18n.confirm_cancel;
			if ( ! window.confirm( message ) ) { return; }
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_cancel_subscription', { id: $btn.data( 'id' ), immediate: immediate } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						window.location.reload();
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );

		function bindSubAction( selector, action, confirmKey, extraData ) {
			$( document ).on( 'click', selector, function () {
				if ( confirmKey && ! window.confirm( VMSEFWP.i18n[ confirmKey ] ) ) { return; }
				var $btn = $( this ).prop( 'disabled', true );
				var data = $.extend( { id: $btn.data( 'id' ) }, extraData || {} );
				if ( 'vms_efwp_pause_subscription' === action ) {
					var periods = window.prompt( VMSEFWP.i18n.pause_period_prompt, '1' );
					if ( null === periods ) {
						$btn.prop( 'disabled', false );
						return;
					}
					data.pause_period_count = Math.max( 1, parseInt( periods, 10 ) || 1 );
				}
				ajax( action, data )
					.done( function ( resp ) {
						if ( resp && resp.success ) {
							window.location.reload();
							return;
						}
						showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error, true );
						$btn.prop( 'disabled', false );
					} )
					.fail( function () {
						showSubActionNotice( VMSEFWP.i18n.error, true );
						$btn.prop( 'disabled', false );
					} );
			} );
		}

		bindSubAction( '.vefwp-pause-sub', 'vms_efwp_pause_subscription' );
		bindSubAction( '.vefwp-resume-sub', 'vms_efwp_resume_subscription', 'confirm_resume' );
		bindSubAction( '.vefwp-uncancel-sub', 'vms_efwp_uncancel_subscription', 'confirm_uncancel' );
		bindSubAction( '.vefwp-charge-sub', 'vms_efwp_charge_subscription', 'confirm_charge' );
		bindSubAction( '.vefwp-convert-sub', 'vms_efwp_convert_subscription', 'confirm_convert' );
		bindSubAction( '.vefwp-cancel-quote', 'vms_efwp_cancel_quote', 'confirm_cancel_quote' );

		$( document ).on( 'click', '.vefwp-sync-order', function () {
			if ( ! window.confirm( VMSEFWP.i18n.confirm_sync_order ) ) { return; }
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_sync_order', { id: $btn.data( 'id' ), is_test: $btn.data( 'test' ) } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error, false );
						$btn.prop( 'disabled', false );
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMSEFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );
	} );
} )( jQuery );
