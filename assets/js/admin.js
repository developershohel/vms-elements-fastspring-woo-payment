/* global jQuery, Chart, VMS_EFWP */
( function ( $ ) {
	'use strict';

	var revenueChart = null;
	var subscriptionChart = null;

	function i18n( key, fallback ) {
		return ( VMS_EFWP && VMS_EFWP.i18n && VMS_EFWP.i18n[ key ] ) ? VMS_EFWP.i18n[ key ] : fallback;
	}

	function fmtMoney( n ) {
		var sym = ( VMS_EFWP && VMS_EFWP.currency ) ? VMS_EFWP.currency : '$';
		return sym + Number( n || 0 ).toFixed( 2 ).replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	function ajax( action, data ) {
		return $.post( VMS_EFWP.ajax_url, $.extend( { action: action, nonce: VMS_EFWP.nonce }, data || {} ) );
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
						label: i18n( 'revenue_label', 'Revenue' ),
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
						label: i18n( 'orders_label', 'Orders' ),
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
		var labels = [
			i18n( 'sub_active', 'Active' ),
			i18n( 'sub_paused', 'Paused' ),
			i18n( 'sub_trial', 'Trial' ),
			i18n( 'sub_overdue', 'Overdue' ),
			i18n( 'sub_canceled', 'Canceled' ),
			i18n( 'sub_deactivated', 'Deactivated' )
		];
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
			? ( ( VMS_EFWP.i18n && VMS_EFWP.i18n.order_singular ) ? VMS_EFWP.i18n.order_singular : '%d order' )
			: ( ( VMS_EFWP.i18n && VMS_EFWP.i18n.order_plural ) ? VMS_EFWP.i18n.order_plural : '%d orders' );
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
					? i18n( 'mrr_prefix', 'MRR: ' ) + mrrStrings.join( ' / ' )
					: i18n( 'no_mrr', 'No active recurring revenue yet.' )
			);
		}
	}

	function setChartError( message ) {
		var $err = $( '#vms-efwp-chart-error' );
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
		var $spinner = $( '#vms-efwp-trend-spinner' );
		if ( ! $spinner.length ) {
			return;
		}
		$spinner.prop( 'hidden', ! visible ).attr( 'aria-hidden', visible ? 'false' : 'true' );
	}

	function renderTopProducts( rows ) {
		var $tbody = $( '#vms-efwp-top-products tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + VMS_EFWP.i18n.no_data + '</td></tr>' );
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
		var $tbody = $( '#vms-efwp-top-countries tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="3">' + VMS_EFWP.i18n.no_data + '</td></tr>' );
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
		var $tbody = $( '#vms-efwp-recent-orders tbody' ).empty();
		if ( ! rows || ! rows.length ) {
			$tbody.append( '<tr><td colspan="5">' + VMS_EFWP.i18n.no_data + '</td></tr>' );
			return;
		}
		$.each( rows, function ( _, o ) {
			$tbody.append(
				$( '<tr/>' )
					.append( $( '<td/>' ).html( $( '<code/>' ).text( o.fs_order_id || '' ) ) )
					.append( $( '<td/>' ).text( ( o.customer_name || '' ) + ( o.email ? ' (' + o.email + ')' : '' ) ) )
					.append( $( '<td/>' ).text( ( o.currency || '' ) + ' ' + Number( o.total ).toFixed( 2 ) ) )
					.append( $( '<td/>' ).html( $( '<span/>' ).addClass( 'vms-efwp-status vms-efwp-status--' + ( o.status || '' ) ).text( o.status || '' ) ) )
					.append( $( '<td/>' ).text( o.created_at || '' ) )
			);
		} );
	}

	function loadDashboard() {
		setSpinner( true );
		setChartError( '' );

		var range = $( '#vms-efwp-range' ).val() || 30;
		var includeTest = $( '#vms-efwp-include-test' ).is( ':checked' ) ? 1 : 0;

		ajax( 'vms_efwp_dashboard_data', { range: range, include_test: includeTest } )
			.done( function ( resp ) {
				if ( ! resp || ! resp.success || ! resp.data ) {
					setChartError( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error );
					return;
				}
				var d = resp.data;
				var revCanvas = document.getElementById( 'vms-efwp-revenue-chart' );
				var subCanvas = document.getElementById( 'vms-efwp-subscription-chart' );
				if ( revCanvas ) {
					if ( typeof Chart === 'undefined' ) {
						setChartError( VMS_EFWP.i18n.chart_fail || VMS_EFWP.i18n.error );
					} else if ( ! buildRevenueChart( revCanvas.getContext( '2d' ), d.daily ) ) {
						setChartError( VMS_EFWP.i18n.chart_fail || VMS_EFWP.i18n.error );
					}
				}
				if ( subCanvas && typeof Chart !== 'undefined' ) {
					buildSubscriptionChart( subCanvas.getContext( '2d' ), d.subscriptions );
				}
				renderKpis( d.kpis, d.subscriptions );
				renderTopProducts( d.top_products );
				renderTopCountries( d.top_countries );
				renderRecentOrders( d.recent_orders );
			} )
			.fail( function ( xhr ) {
				var msg = VMS_EFWP.i18n.error;
				if ( xhr && xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ) {
					msg = xhr.responseJSON.data.message;
				}
				setChartError( msg );
			} )
			.always( function () {
				setSpinner( false );
			} );
	}

	$( function () {
		// Dashboard.
		if ( document.getElementById( 'vms-efwp-revenue-chart' ) ) {
			loadDashboard();
			$( '#vms-efwp-range, #vms-efwp-include-test' ).on( 'change', loadDashboard );
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

		$( document ).on( 'click', '.vms-efwp-generate-secret', function () {
			var target = $( this ).data( 'target' );
			var secret = generateSecret();
			$( '#' + target ).val( secret );
			flash( $( this ), i18n( 'generated', 'Generated' ) );
		} );

		$( document ).on( 'click', '.vms-efwp-copy-secret', function () {
			var target = $( this ).data( 'target' );
			var $input = $( '#' + target );
			if ( ! $input.val() ) { return; }
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( $input.val() );
			} else {
				$input.select();
				document.execCommand( 'copy' );
			}
			flash( $( this ), i18n( 'checkout_link_copied', 'Copied!' ) );
		} );

		// Settings: test connection.
		$( '#vms-efwp-test-connection' ).on( 'click', function () {
			var $btn = $( this );
			var $r = $( '#vms-efwp-test-result' ).removeClass( 'is-ok is-err' ).text( VMS_EFWP.i18n.loading );
			$btn.prop( 'disabled', true );
			ajax( 'vms_efwp_test_connection' )
				.done( function ( resp ) {
					if ( resp.success ) {
						$r.addClass( 'is-ok' ).text( resp.data.message );
					} else {
						$r.addClass( 'is-err' ).text( ( resp.data && resp.data.message ) || VMS_EFWP.i18n.error );
					}
				} )
				.fail( function () { $r.addClass( 'is-err' ).text( VMS_EFWP.i18n.error ); } )
				.always( function () { $btn.prop( 'disabled', false ); } );
		} );

		// Mode switch active styling.
		$( document ).on( 'change', 'input[name="mode"]', function () {
			$( '.vms-efwp-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.vms-efwp-mode-option' ).addClass( 'is-active' );
		} );

		// Pricing strategy: toggle Custom Price product path field + active styling.
		$( document ).on( 'change', 'input[name="pricing_strategy"]', function () {
			$( '.vms-efwp-pricing-strategy .vms-efwp-mode-option' ).removeClass( 'is-active' );
			$( this ).closest( '.vms-efwp-mode-option' ).addClass( 'is-active' );
			$( '.vms-efwp-custom-price-row' ).toggle( 'single_custom_price' === $( this ).val() );
		} );

		// JSON modal: open.
		$( document ).on( 'click', '.vms-efwp-view-json', function () {
			var json = $( this ).attr( 'data-json' );
			try {
				var parsed = JSON.parse( json );
				json = JSON.stringify( parsed, null, 2 );
			} catch ( e ) {}
			$( '#vms-efwp-json-modal-body' ).text( json );
			$( '#vms-efwp-json-modal' ).removeAttr( 'hidden' );
		} );

		$( document ).on( 'click', '[data-vms-efwp-close]', function () {
			$( '#vms-efwp-json-modal' ).attr( 'hidden', true );
		} );

		$( document ).on( 'click', '[data-vms-efwp-close-checkout-link]', function () {
			$( '#vms-efwp-checkout-link-modal' ).attr( 'hidden', true );
		} );

		$( document ).on( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) {
				$( '#vms-efwp-json-modal' ).attr( 'hidden', true );
				$( '#vms-efwp-checkout-link-modal' ).attr( 'hidden', true );
			}
		} );

		// JSON copy.
		$( document ).on( 'click', '#vms-efwp-json-copy', function () {
			var text = $( '#vms-efwp-json-modal-body' ).text();
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text );
			} else {
				var $tmp = $( '<textarea>' ).val( text ).appendTo( 'body' ).select();
				document.execCommand( 'copy' );
				$tmp.remove();
			}
			$( this ).text( i18n( 'checkout_link_copied', 'Copied!' ) );
			var self = this;
			setTimeout( function () { $( self ).text( i18n( 'copy_json', 'Copy JSON' ) ); }, 1500 );
		} );

		var checkoutLinkProductPath = '';

		function copyCheckoutField( $input ) {
			var text = $input.val ? $input.val() : $input.text();
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text );
			} else {
				var $tmp = $( '<textarea>' ).val( text ).appendTo( 'body' ).select();
				document.execCommand( 'copy' );
				$tmp.remove();
			}
		}

		function setCheckoutLinkStatus( message, isError ) {
			var $status = $( '#vms-efwp-checkout-link-status' );
			if ( ! message ) {
				$status.attr( 'hidden', true ).text( '' ).removeClass( 'notice-error notice-success' );
				return;
			}
			$status
				.removeAttr( 'hidden' )
				.text( message )
				.toggleClass( 'notice-error', !! isError )
				.toggleClass( 'notice-success', ! isError );
		}

		function fillCheckoutLinkModal( data ) {
			data = data || {};
			var paymentUrl = data.paymentUrl || data.previewUrl || '';
			$( '#vms-efwp-checkout-link-product' ).text( data.productPath || checkoutLinkProductPath || '' );
			$( '#vms-efwp-checkout-payment-url' ).val( paymentUrl );
			$( '#vms-efwp-checkout-overlay-url' ).val( data.overlayUrl || '' );
			$( '#vms-efwp-checkout-preview-url' ).val( paymentUrl );
			$( '#vms-efwp-checkout-embed-html' ).val( data.embedHtml || '' );
			$( '#vms-efwp-checkout-open-preview' ).attr( 'href', paymentUrl || '#' );

			if ( data.sessionUrl ) {
				$( '#vms-efwp-checkout-session-url' ).val( data.sessionUrl );
				$( '#vms-efwp-checkout-session-wrap' ).removeAttr( 'hidden' );
			} else {
				$( '#vms-efwp-checkout-session-url' ).val( '' );
				$( '#vms-efwp-checkout-session-wrap' ).attr( 'hidden', true );
			}
		}

		function loadCheckoutLinks( productPath, createSession ) {
			setCheckoutLinkStatus( createSession ? i18n( 'checkout_session_loading', 'Generating session link…' ) : i18n( 'checkout_link_loading', 'Loading payment link…' ), false );
			return ajax( 'vms_efwp_get_checkout_link', {
				product_path: productPath,
				create_session: createSession ? '1' : '0'
			} );
		}

		$( document ).on( 'click', '.vms-efwp-get-checkout-link', function () {
			var $btn = $( this );
			var productPath = $btn.data( 'product-path' ) || $btn.data( 'productPath' );
			if ( ! productPath ) {
				return;
			}

			checkoutLinkProductPath = productPath;
			fillCheckoutLinkModal( { productPath: productPath } );
			$( '#vms-efwp-checkout-link-modal' ).removeAttr( 'hidden' );
			$btn.prop( 'disabled', true );

			loadCheckoutLinks( productPath, false )
				.done( function ( resp ) {
					if ( resp && resp.success && resp.data ) {
						fillCheckoutLinkModal( resp.data );
						setCheckoutLinkStatus( '', false );
						return;
					}
					setCheckoutLinkStatus( ( resp && resp.data && resp.data.message ) ? resp.data.message : ( VMS_EFWP.i18n.checkout_link_error || VMS_EFWP.i18n.error ), true );
				} )
				.fail( function () {
					setCheckoutLinkStatus( VMS_EFWP.i18n.checkout_link_error || VMS_EFWP.i18n.error, true );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );

		$( document ).on( 'click', '#vms-efwp-checkout-generate-session', function () {
			if ( ! checkoutLinkProductPath ) {
				return;
			}
			var $btn = $( this ).prop( 'disabled', true );
			loadCheckoutLinks( checkoutLinkProductPath, true )
				.done( function ( resp ) {
					if ( resp && resp.success && resp.data ) {
						fillCheckoutLinkModal( resp.data );
						setCheckoutLinkStatus( '', false );
						return;
					}
					setCheckoutLinkStatus( ( resp && resp.data && resp.data.message ) ? resp.data.message : ( VMS_EFWP.i18n.checkout_link_error || VMS_EFWP.i18n.error ), true );
				} )
				.fail( function () {
					setCheckoutLinkStatus( VMS_EFWP.i18n.checkout_link_error || VMS_EFWP.i18n.error, true );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );
		} );

		$( document ).on( 'click', '.vms-efwp-copy-checkout-field', function () {
			var target = $( this ).data( 'target' );
			var $field = target ? $( target ) : $();
			if ( ! $field.length ) {
				return;
			}
			copyCheckoutField( $field );
			flash( $( this ), i18n( 'checkout_link_copied', 'Copied!' ) );
		} );

		$( document ).on( 'change', '[name="same_as_bill_to"]', function () {
			$( '[data-vms-efwp-deliver-fields]' ).attr( 'hidden', $( this ).is( ':checked' ) );
		} );
		$( '[name="same_as_bill_to"]' ).trigger( 'change' );

		function syncPartialReturnFields() {
			var isPartial = 'PARTIAL' === $( '#vms-efwp-return-refund-type' ).val();
			$( '[data-vms-efwp-partial-return-fields]' ).attr( 'hidden', ! isPartial );
		}
		$( document ).on( 'change', '#vms-efwp-return-refund-type', syncPartialReturnFields );
		syncPartialReturnFields();

		// Toggle inline create forms.
		$( document ).on( 'click', '[data-vms-efwp-open-form]', function () {
			var key = $( this ).data( 'vms-efwp-open-form' );
			var $form = $( '[data-vms-efwp-form="' + key + '"]' );
			$form.attr( 'hidden', false ).get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'nearest' } );

			// If opened from "New", reset to create mode.
			if ( 'vms-efwp-new-product' === this.id ) {
				resetProductForm();
			}
		} );

		/**
		 * Convert display text to a FastSpring product path (WordPress-style slug).
		 *
		 * @param {string} text Raw input.
		 * @return {string}
		 */
		function vms_efwp_slugify( text ) {
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
			if ( ! $form.length || $form.data( 'vms-efwp-slugBound' ) ) {
				return;
			}

			var $source = $form.find( '[data-vms-efwp-slug-source]' );
			var $target = $form.find( '[data-vms-efwp-slug-target]' ).first();
			if ( ! $source.length || ! $target.length ) {
				return;
			}

			$form.data( 'vms-efwp-slugBound', true );
			$form.data( 'vms-efwp-slugManual', false );

			$source.on( 'input.vms-efwp-slug', function () {
				if ( $target.prop( 'readonly' ) || $form.data( 'vms-efwp-slugManual' ) ) {
					return;
				}
				$target.val( vms_efwp_slugify( $source.val() ) );
			} );

			$target.on( 'input.vms-efwp-slug', function () {
				if ( ! $target.prop( 'readonly' ) ) {
					$form.data( 'vms-efwp-slugManual', true );
				}
			} );

			$target.on( 'blur.vms-efwp-slug', function () {
				if ( $target.prop( 'readonly' ) ) {
					return;
				}
				$target.val( vms_efwp_slugify( $target.val() ) );
			} );
		}

		function resetSlugForm( $form ) {
			$form.data( 'vms-efwp-slugManual', false );
		}

		$( '[data-vms-efwp-slug-form]' ).each( function () {
			bindSlugForm( $( this ) );
		} );

		// Standalone slug fields (e.g. settings catch-all path) — sanitize on blur only.
		$( document ).on( 'blur', '[data-vms-efwp-slug-target]', function () {
			var $field = $( this );
			if ( $field.prop( 'readonly' ) || $field.closest( '[data-vms-efwp-slug-form]' ).length ) {
				return;
			}
			$field.val( vms_efwp_slugify( $field.val() ) );
		} );

		function syncCouponDiscountUi() {
			var isFlat = 'flat' === $( '#vms-efwp-coupon-discount-type' ).val() || $( '#vms-efwp-order-level-discount' ).is( ':checked' );
			$( '[data-vms-efwp-flat-only]' ).toggle( isFlat );
			if ( $( '#vms-efwp-order-level-discount' ).is( ':checked' ) ) {
				$( '#vms-efwp-coupon-discount-type' ).val( 'flat' );
			}
		}
		$( document ).on( 'change', '#vms-efwp-coupon-discount-type, #vms-efwp-order-level-discount', syncCouponDiscountUi );
		syncCouponDiscountUi();

		$( document ).on( 'click', '[data-vms-efwp-close-form]', function () {
			var key = $( this ).data( 'vms-efwp-close-form' );
			$( '[data-vms-efwp-form="' + key + '"]' ).attr( 'hidden', true );
		} );

		// Product edit: prefill form from row data.
		$( document ).on( 'click', '.vms-efwp-edit-product', function () {
			var raw = $( this ).attr( 'data-product' );
			if ( ! raw ) { return; }
			try {
				var p = JSON.parse( raw );
				prefillProductForm( p );
				$( '[data-vms-efwp-form="save-product"]' )
					.attr( 'hidden', false )
					.get( 0 ).scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} catch ( e ) {}
		} );

		function prefillProductForm( p ) {
			var $form = $( '#vms-efwp-product-form' );
			resetSlugForm( $form );
			$form.find( '[data-vms-efwp-field="product"]' )
				.val( p.product || '' )
				.prop( 'readonly', true ); // path is the identifier; cannot change after creation
			$form.find( '[data-vms-efwp-field="display"]' ).val(
				p.display ? ( p.display.en || Object.values( p.display )[ 0 ] || '' ) : ''
			);
			$form.find( '[data-vms-efwp-field="sku"]' ).val( p.sku || '' );
			var format = p.format || 'digital';
			if ( format === 'service' ) {
				format = 'digital';
			}
			$form.find( '[data-vms-efwp-field="format"]' ).val( format );
			$form.find( '[data-vms-efwp-field="image"]' ).val( p.image || '' );
			$form.find( '[data-vms-efwp-field="badge"]' ).val(
				p.badge ? ( p.badge.en || Object.values( p.badge )[ 0 ] || '' ) : ''
			);
			$form.find( '[data-vms-efwp-field="rank"]' ).val( p.rank || 0 );
			var summary = '', full = '', action = '', fulfillment = '';
			if ( p.description ) {
				summary = ( p.description.summary && ( p.description.summary.en || Object.values( p.description.summary )[ 0 ] ) ) || '';
				full    = ( p.description.full    && ( p.description.full.en    || Object.values( p.description.full )[ 0 ]    ) ) || '';
				action  = ( p.description.action  && ( p.description.action.en  || Object.values( p.description.action )[ 0 ]  ) ) || '';
			}
			if ( p.fulfillment && p.fulfillment.instructions ) {
				fulfillment = p.fulfillment.instructions.en || Object.values( p.fulfillment.instructions )[ 0 ] || '';
			}
			$form.find( '[data-vms-efwp-field="summary"]' ).val( summary );
			$form.find( '[data-vms-efwp-field="action"]' ).val( action );
			$form.find( '[data-vms-efwp-field="full"]' ).val( full );
			$form.find( '[data-vms-efwp-field="fulfillment"]' ).val( fulfillment );

			// Rebuild pricing rows.
			var $rows = $( '#vms-efwp-pricing-rows' ).empty();
			var prices = ( p.pricing && p.pricing.price ) ? p.pricing.price : { USD: 0 };
			$.each( prices, function ( cur, val ) {
				$rows.append( buildPricingRow( cur, val ) );
			} );

			$( '#vms-efwp-product-form-title' ).text( i18n( 'edit_product', 'Edit product' ) );
			$( '#vms-efwp-product-submit' ).text( i18n( 'update_product', 'Update product' ) );
		}

		function resetProductForm() {
			var $form = $( '#vms-efwp-product-form' );
			if ( ! $form.length ) { return; }
			$form[ 0 ].reset();
			resetSlugForm( $form );
			$form.find( '[data-vms-efwp-field="product"]' ).prop( 'readonly', false );
			$( '#vms-efwp-pricing-rows' ).html( buildPricingRow( 'USD', '0' ) );
			$( '#vms-efwp-product-form-title' ).text( i18n( 'create_product', 'Create product' ) );
			$( '#vms-efwp-product-submit' ).text( i18n( 'create_product', 'Create product' ) );
		}

		function buildPricingRow( cur, val ) {
			return '<tr>' +
				'<td><input type="text" name="pricing[currency][]" maxlength="3" value="' + ( cur || '' ) + '" /></td>' +
				'<td><input type="number" step="0.01" name="pricing[price][]" required value="' + ( typeof val === 'undefined' ? '0' : val ) + '" /></td>' +
				'<td><button type="button" class="button button-small vms-efwp-pricing-remove">&times;</button></td>' +
				'</tr>';
		}

		$( document ).on( 'click', '#vms-efwp-pricing-add', function () {
			$( '#vms-efwp-pricing-rows' ).append( buildPricingRow( '', '' ) );
		} );
		$( document ).on( 'click', '.vms-efwp-pricing-remove', function () {
			var $tr = $( this ).closest( 'tr' );
			if ( $( '#vms-efwp-pricing-rows tr' ).length > 1 ) { $tr.remove(); }
		} );

		// Subscription actions.
		function showSubActionNotice( message, isError ) {
			var $notice = $( '<div class="notice ' + ( isError ? 'notice-error' : 'notice-success' ) + ' is-dismissible"><p></p></div>' );
			$notice.find( 'p' ).text( message );
			$( '.vms-efwp-wrap' ).first().prepend( $notice );
		}

		$( document ).on( 'click', '.vms-efwp-sync-sub', function () {
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_sync_subscription', { id: $btn.data( 'id' ) } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						window.location.reload();
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );
		$( document ).on( 'click', '.vms-efwp-cancel-sub', function () {
			var immediate = $( this ).data( 'immediate' ) ? '1' : '0';
			var message = immediate ? VMS_EFWP.i18n.confirm_immediate_cancel : VMS_EFWP.i18n.confirm_cancel;
			if ( ! window.confirm( message ) ) { return; }
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_cancel_subscription', { id: $btn.data( 'id' ), immediate: immediate } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						window.location.reload();
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );

		function bindSubAction( selector, action, confirmKey, extraData ) {
			$( document ).on( 'click', selector, function () {
				if ( confirmKey && ! window.confirm( VMS_EFWP.i18n[ confirmKey ] ) ) { return; }
				var $btn = $( this ).prop( 'disabled', true );
				var data = $.extend( { id: $btn.data( 'id' ) }, extraData || {} );
				if ( 'vms_efwp_pause_subscription' === action ) {
					var periods = window.prompt( VMS_EFWP.i18n.pause_period_prompt, '1' );
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
						showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error, true );
						$btn.prop( 'disabled', false );
					} )
					.fail( function () {
						showSubActionNotice( VMS_EFWP.i18n.error, true );
						$btn.prop( 'disabled', false );
					} );
			} );
		}

		bindSubAction( '.vms-efwp-pause-sub', 'vms_efwp_pause_subscription' );
		bindSubAction( '.vms-efwp-resume-sub', 'vms_efwp_resume_subscription', 'confirm_resume' );
		bindSubAction( '.vms-efwp-uncancel-sub', 'vms_efwp_uncancel_subscription', 'confirm_uncancel' );
		bindSubAction( '.vms-efwp-charge-sub', 'vms_efwp_charge_subscription', 'confirm_charge' );
		bindSubAction( '.vms-efwp-convert-sub', 'vms_efwp_convert_subscription', 'confirm_convert' );
		bindSubAction( '.vms-efwp-cancel-quote', 'vms_efwp_cancel_quote', 'confirm_cancel_quote' );

		$( document ).on( 'click', '.vms-efwp-sync-order', function () {
			if ( ! window.confirm( VMS_EFWP.i18n.confirm_sync_order ) ) { return; }
			var $btn = $( this ).prop( 'disabled', true );
			ajax( 'vms_efwp_sync_order', { id: $btn.data( 'id' ), is_test: $btn.data( 'test' ) } )
				.done( function ( resp ) {
					if ( resp && resp.success ) {
						showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error, false );
						$btn.prop( 'disabled', false );
						return;
					}
					showSubActionNotice( ( resp && resp.data && resp.data.message ) ? resp.data.message : VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} )
				.fail( function () {
					showSubActionNotice( VMS_EFWP.i18n.error, true );
					$btn.prop( 'disabled', false );
				} );
		} );

		function escapeAttr( value ) {
			return String( value || '' )
				.replace( /&/g, '&amp;' )
				.replace( /"/g, '&quot;' )
				.replace( /</g, '&lt;' );
		}

		function buildShortcodeString( type, fields ) {
			var tag = type === 'subscription' ? 'fastspring_subscription' : 'fastspring_product';
			var parts = [];
			var styleFields = [ 'bg', 'color', 'font_size', 'padding', 'margin', 'border_radius', 'border_width', 'border_color' ];

			if ( fields.id ) {
				parts.push( 'id="' + escapeAttr( fields.id ) + '"' );
			}
			if ( fields.redirect ) {
				parts.push( 'redirect="' + escapeAttr( fields.redirect ) + '"' );
			}
			if ( fields.details === '1' ) {
				parts.push( 'details="1"' );
			} else if ( fields.details === '0' ) {
				parts.push( 'details="0"' );
			}
			if ( fields.text ) {
				parts.push( 'text="' + escapeAttr( fields.text ) + '"' );
			}
			if ( fields.style && fields.style !== 'primary' ) {
				parts.push( 'style="' + escapeAttr( fields.style ) + '"' );
			}
			if ( fields.className ) {
				parts.push( 'class="' + escapeAttr( fields.className ) + '"' );
			}
			if ( fields.align && fields.align !== 'left' ) {
				parts.push( 'align="' + escapeAttr( fields.align ) + '"' );
			}
			if ( fields.quantity && parseInt( fields.quantity, 10 ) > 1 ) {
				parts.push( 'quantity="' + parseInt( fields.quantity, 10 ) + '"' );
			}

			styleFields.forEach( function ( key ) {
				if ( fields[ key ] ) {
					parts.push( key + '="' + escapeAttr( fields[ key ] ) + '"' );
				}
			} );

			return '[' + tag + ( parts.length ? ' ' + parts.join( ' ' ) : '' ) + ']';
		}

		function buildPreviewStyles( fields ) {
			var styles = [];
			if ( fields.bg ) {
				styles.push( 'background-color:' + fields.bg );
			}
			if ( fields.color ) {
				styles.push( 'color:' + fields.color );
			}
			if ( fields.font_size ) {
				styles.push( 'font-size:' + fields.font_size );
			}
			if ( fields.padding ) {
				styles.push( 'padding:' + fields.padding );
			}
			if ( fields.border_radius ) {
				styles.push( 'border-radius:' + fields.border_radius );
			}
			if ( fields.border_width || fields.border_color ) {
				styles.push( 'border-style:solid' );
				if ( fields.border_width ) {
					styles.push( 'border-width:' + fields.border_width );
				}
				if ( fields.border_color ) {
					styles.push( 'border-color:' + fields.border_color );
				}
			}
			return styles.join( ';' );
		}

		function renderShortcodePreview( $builder, fields ) {
			var type = $builder.data( 'vms-efwp-shortcode-type' ) || 'product';
			var $preview = $( '#vms-efwp-shortcode-preview-' + type );
			var $output = $( '#vms-efwp-shortcode-output-' + type );
			var shortcode = buildShortcodeString( type, fields );

			$output.val( shortcode );

			if ( ! fields.id ) {
				$preview.html(
					'<p class="description">' +
					i18n( 'shortcode_select_id', 'Select or enter a product path to generate a shortcode.' ) +
					'</p>'
				);
				return;
			}

			var buttonClasses = [
				'vms-efwp-shortcode-btn',
				'vms-efwp-shortcode-btn--' + ( fields.style || 'primary' ),
			];
			if ( fields.className ) {
				buttonClasses.push( fields.className );
			}

			var buttonStyle = buildPreviewStyles( fields );
			var wrapperStyle = fields.margin ? ' style="margin:' + fields.margin + '"' : '';

			$preview.html(
				'<div class="vms-efwp-shortcode vms-efwp-shortcode--' + type + ' vms-efwp-shortcode--align-' + ( fields.align || 'left' ) + '"' + wrapperStyle + '>' +
					'<button type="button" class="' + buttonClasses.join( ' ' ) + '"' + ( buttonStyle ? ' style="' + buttonStyle + '"' : '' ) + ' disabled>' +
						( fields.text || ( type === 'subscription' ? i18n( 'subscribe_now', 'Subscribe now' ) : i18n( 'buy_now', 'Buy now' ) ) ) +
					'</button>' +
				'</div>'
			);
		}

		function collectShortcodeFields( $builder ) {
			var fields = {
				id: '',
				redirect: '',
				details: '',
				text: '',
				style: 'primary',
				className: '',
				align: 'left',
				quantity: '1',
				bg: '',
				color: '',
				font_size: '',
				padding: '',
				margin: '',
				border_radius: '',
				border_width: '',
				border_color: '',
			};

			$builder.find( '.vms-efwp-shortcode-field' ).each( function () {
				var $field = $( this );
				var key = $field.data( 'field' );
				if ( ! key || key === 'id' || key === 'id_manual' ) {
					return;
				}
				if ( $field.attr( 'type' ) === 'color' ) {
					return;
				}
				if ( $field.is( ':checkbox' ) ) {
					fields[ key === 'class' ? 'className' : key ] = $field.is( ':checked' ) ? '1' : '0';
					return;
				}
				fields[ key === 'class' ? 'className' : key ] = $field.val();
			} );

			var selectId = $builder.find( 'select[data-field="id"]' ).val() || '';
			var manualId = $builder.find( 'input[data-field="id_manual"]' ).val() || '';
			fields.id = manualId || selectId;

			return fields;
		}

		function refreshShortcodeBuilder( $builder ) {
			renderShortcodePreview( $builder, collectShortcodeFields( $builder ) );
		}

		$( '.vms-efwp-shortcode-builder' ).each( function () {
			refreshShortcodeBuilder( $( this ) );
		} );

		$( document ).on( 'input change', '.vms-efwp-shortcode-builder .vms-efwp-shortcode-field', function () {
			refreshShortcodeBuilder( $( this ).closest( '.vms-efwp-shortcode-builder' ) );
		} );

		$( document ).on( 'input', '.vms-efwp-shortcode-builder .vms-efwp-shortcode-color', function () {
			var field = $( this ).data( 'field' );
			$( this ).closest( '.vms-efwp-color-field' ).find( 'input[type="text"][data-field="' + field + '"]' ).val( $( this ).val() );
			refreshShortcodeBuilder( $( this ).closest( '.vms-efwp-shortcode-builder' ) );
		} );

		$( document ).on( 'input', '.vms-efwp-shortcode-builder .vms-efwp-color-field input[type="text"]', function () {
			var field = $( this ).data( 'field' );
			var value = $( this ).val();
			if ( /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( value ) ) {
				$( this ).closest( '.vms-efwp-color-field' ).find( 'input[type="color"][data-field="' + field + '"]' ).val( value );
			}
		} );

		$( document ).on( 'click', '.vms-efwp-copy-shortcode', function () {
			var target = $( this ).data( 'target' );
			var $field = target ? $( target ) : $();
			if ( ! $field.length ) {
				return;
			}
			var text = $field.val();
			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text );
			} else {
				var $tmp = $( '<textarea>' ).val( text ).appendTo( 'body' ).select();
				document.execCommand( 'copy' );
				$tmp.remove();
			}
			flash( $( this ), i18n( 'shortcode_copied', 'Copied!' ) );
		} );
	} );
} )( jQuery );
