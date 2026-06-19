<?php
/**
 * Admin bootstrap.
 *
 * @package VMS_EFWP
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFWP_Admin.
 */
class VMS_EFWP_Admin {

	const MENU_SLUG = 'vms-efwp';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'allowed_redirect_hosts', array( $this, 'allow_fastspring_redirect_hosts' ) );
		add_action( 'wp_ajax_vms_efwp_dashboard_data', array( $this, 'ajax_dashboard_data' ) );
		add_action( 'wp_ajax_vms_efwp_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_vms_efwp_sync_subscription', array( $this, 'ajax_sync_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_pause_subscription', array( $this, 'ajax_pause_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_resume_subscription', array( $this, 'ajax_resume_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_uncancel_subscription', array( $this, 'ajax_uncancel_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_charge_subscription', array( $this, 'ajax_charge_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_convert_subscription', array( $this, 'ajax_convert_subscription' ) );
		add_action( 'wp_ajax_vms_efwp_sync_order', array( $this, 'ajax_sync_order' ) );
		add_action( 'wp_ajax_vms_efwp_cancel_quote', array( $this, 'ajax_cancel_quote' ) );
		add_action( 'admin_post_vms_efwp_sync_all_products', array( 'VMS_EFWP_Admin_Tools', 'handle_sync_all_products' ) );
		add_action( 'admin_post_vms_efwp_save_settings', array( 'VMS_EFWP_Admin_Settings', 'handle_save' ) );
		add_action( 'admin_post_vms_efwp_provision_custom_price', array( 'VMS_EFWP_Admin_Settings', 'handle_provision_custom_price' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'FastSpring', 'vms-elements-fastspring-woo-payment' ),
			__( 'FastSpring', 'vms-elements-fastspring-woo-payment' ),
			'manage_options',
			self::MENU_SLUG,
			array( 'VMS_EFWP_Admin_Dashboard', 'render' ),
			'dashicons-chart-area',
			56
		);

		$pages = array(
			array( self::MENU_SLUG,                __( 'Dashboard', 'vms-elements-fastspring-woo-payment' ),     array( 'VMS_EFWP_Admin_Dashboard', 'render' ) ),
			array( 'separator', 'vms-efwp-sep-sales', __( '— Sales —', 'vms-elements-fastspring-woo-payment' ) ),
			array( 'vms-efwp-orders',         __( 'Orders', 'vms-elements-fastspring-woo-payment' ),        array( 'VMS_EFWP_Admin_Orders', 'render' ) ),
			array( 'vms-efwp-subscriptions',  __( 'Subscriptions', 'vms-elements-fastspring-woo-payment' ), array( 'VMS_EFWP_Admin_Subscriptions', 'render' ) ),
			array( 'vms-efwp-invoices',       __( 'Invoices', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Invoices', 'render' ) ),
			array( 'vms-efwp-quotes',         __( 'Quotes', 'vms-elements-fastspring-woo-payment' ),        array( 'VMS_EFWP_Admin_Quotes', 'render' ) ),
			array( 'vms-efwp-returns',        __( 'Returns', 'vms-elements-fastspring-woo-payment' ),       array( 'VMS_EFWP_Admin_Returns', 'render' ) ),
			array( 'separator', 'vms-efwp-sep-catalog', __( '— Catalog —', 'vms-elements-fastspring-woo-payment' ) ),
			array( 'vms-efwp-products',       __( 'Products', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Products', 'render' ) ),
			array( 'vms-efwp-coupons',        __( 'Coupons', 'vms-elements-fastspring-woo-payment' ),       array( 'VMS_EFWP_Admin_Coupons', 'render' ) ),
			array( 'separator', 'vms-efwp-sep-customers', __( '— Customers —', 'vms-elements-fastspring-woo-payment' ) ),
			array( 'vms-efwp-accounts',       __( 'Accounts', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Accounts', 'render' ) ),
			array( 'vms-efwp-sessions',       __( 'Sessions', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Sessions', 'render' ) ),
			array( 'separator', 'vms-efwp-sep-integrations', __( '— Integrations —', 'vms-elements-fastspring-woo-payment' ) ),
			array( 'vms-efwp-events',         __( 'Events', 'vms-elements-fastspring-woo-payment' ),        array( 'VMS_EFWP_Admin_Events', 'render' ) ),
			array( 'vms-efwp-reports',        __( 'Reports', 'vms-elements-fastspring-woo-payment' ),       array( 'VMS_EFWP_Admin_Reports', 'render' ) ),
			array( 'vms-efwp-webhooks',       __( 'Webhooks', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Webhooks', 'render' ) ),
			array( 'separator', 'vms-efwp-sep-system', __( '— System —', 'vms-elements-fastspring-woo-payment' ) ),
			array( 'vms-efwp-tools',          __( 'Tools', 'vms-elements-fastspring-woo-payment' ),         array( 'VMS_EFWP_Admin_Tools', 'render' ) ),
			array( 'vms-efwp-settings',       __( 'Settings', 'vms-elements-fastspring-woo-payment' ),      array( 'VMS_EFWP_Admin_Settings', 'render' ) ),
		);

		foreach ( $pages as $page ) {
			if ( 'separator' === $page[0] ) {
				add_submenu_page( self::MENU_SLUG, '', $page[2], 'manage_options', $page[1], '__return_false' );
				continue;
			}
			list( $slug, $title, $callback ) = $page;
			add_submenu_page( self::MENU_SLUG, $title, $title, 'manage_options', $slug, $callback );
		}
	}

	/**
	 * Enqueue admin scripts/styles only on plugin pages.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'vms-efwp' ) && false === strpos( $hook, 'fastspring' ) ) {
			return;
		}

		wp_enqueue_style(
			'vms-efwp-admin',
			VMS_EFWP_URL . 'assets/css/admin.css',
			array(),
			VMS_EFWP_VERSION
		);

		/**
		 * Chart.js is loaded from a CDN by default to keep the plugin lightweight.
		 * Filter `vms_efwp_chartjs_url` to point at a self-hosted copy.
		 */
		$chart_url = apply_filters(
			'vms_efwp_chartjs_url',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'
		);
		wp_enqueue_script(
			'vms-efwp-chartjs',
			$chart_url,
			array(),
			'4.4.4',
			true
		);

		wp_enqueue_script(
			'vms-efwp-admin',
			VMS_EFWP_URL . 'assets/js/admin.js',
			array( 'jquery', 'vms-efwp-chartjs' ),
			VMS_EFWP_VERSION,
			true
		);

		wp_localize_script(
			'vms-efwp-admin',
			'VMSEFWP',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'vms_efwp_admin' ),
				'currency' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'i18n'     => array(
					'loading'  => __( 'Loading...', 'vms-elements-fastspring-woo-payment' ),
					'no_data'  => __( 'No data yet.', 'vms-elements-fastspring-woo-payment' ),
					'error'    => __( 'Something went wrong.', 'vms-elements-fastspring-woo-payment' ),
					'confirm_cancel' => __( 'Cancel this subscription?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_pause'  => __( 'Pause this subscription?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_resume' => __( 'Resume this subscription?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_uncancel' => __( 'Uncancel this subscription?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_charge' => __( 'Charge this managed subscription now?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_convert' => __( 'Create a conversion session for this trial subscription?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_immediate_cancel' => __( 'Cancel this subscription immediately?', 'vms-elements-fastspring-woo-payment' ),
					'pause_period_prompt' => __( 'Pause for how many billing periods?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_sync_order' => __( 'Save this order to the local database?', 'vms-elements-fastspring-woo-payment' ),
					'confirm_cancel_quote' => __( 'Cancel this quote?', 'vms-elements-fastspring-woo-payment' ),
					'copy_json'      => __( 'Copy JSON', 'vms-elements-fastspring-woo-payment' ),
					'revenue_label'  => __( 'Revenue', 'vms-elements-fastspring-woo-payment' ),
					'orders_label'   => __( 'Orders', 'vms-elements-fastspring-woo-payment' ),
					/* translators: %d: order count */
					'order_singular' => __( '%d order', 'vms-elements-fastspring-woo-payment' ),
					/* translators: %d: order count */
					'order_plural'   => __( '%d orders', 'vms-elements-fastspring-woo-payment' ),
					'mrr_prefix'     => __( 'MRR: ', 'vms-elements-fastspring-woo-payment' ),
					'no_mrr'         => __( 'No active recurring revenue yet.', 'vms-elements-fastspring-woo-payment' ),
				),
			)
		);
	}

	/**
	 * AJAX: dashboard data.
	 */
	public function ajax_dashboard_data() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}

		$range        = isset( $_REQUEST['range'] ) ? max( 1, min( 365, (int) $_REQUEST['range'] ) ) : 30;
		$include_test = isset( $_REQUEST['include_test'] ) && '1' === $_REQUEST['include_test'];

		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$week_start  = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$range_start = gmdate( 'Y-m-d 00:00:00', time() - ( ( $range - 1 ) * DAY_IN_SECONDS ) );

		wp_send_json_success(
			array(
				'kpis'          => array(
					'today'    => VMS_EFWP_Stats::sales_summary( $today_start, $today_end, $include_test ),
					'week'     => VMS_EFWP_Stats::sales_summary( $week_start, $today_end, $include_test ),
					'month'    => VMS_EFWP_Stats::sales_summary( $month_start, $today_end, $include_test ),
					'all_time' => VMS_EFWP_Stats::sales_summary( '1970-01-01 00:00:00', $today_end, $include_test ),
				),
				'daily'         => VMS_EFWP_Stats::daily_revenue( $range, $include_test ),
				'top_products'  => VMS_EFWP_Stats::top_products( 6, $include_test, $range_start, $today_end ),
				'top_countries' => VMS_EFWP_Stats::top_countries( 6, $include_test, $range_start, $today_end ),
				'subscriptions' => VMS_EFWP_Stats::subscriptions_summary( $include_test ),
				'recent_orders' => VMS_EFWP_Stats::recent_orders( 8, $include_test ),
			)
		);
	}

	/**
	 * AJAX: test API connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		$result = vms_efwp()->api->test_connection();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connected to FastSpring successfully.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: pull a fresh copy of a subscription from the API.
	 */
	public function ajax_sync_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->get_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		$subscription = vms_efwp()->api->parse_subscription( $result );
		if ( is_wp_error( $subscription ) ) {
			wp_send_json_error( array( 'message' => $subscription->get_error_message() ) );
		}
		$is_test = ! empty( $subscription['live'] ) ? ! $subscription['live'] : vms_efwp()->settings->is_sandbox();
		VMS_EFWP_Data_Store::upsert_subscription( $subscription, $is_test );
		wp_send_json_success( array( 'message' => __( 'Subscription synced.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: cancel a subscription.
	 */
	public function ajax_cancel_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$immediate = isset( $_POST['immediate'] ) && '1' === $_POST['immediate'];
		$result    = vms_efwp()->api->cancel_subscription( $id, $immediate );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		VMS_EFWP_Data_Store::set_subscription_status( $id, 'canceled' );
		wp_send_json_success( array( 'message' => __( 'Subscription cancellation requested.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: pause a subscription.
	 */
	public function ajax_pause_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$periods = isset( $_POST['pause_period_count'] ) ? max( 1, (int) $_POST['pause_period_count'] ) : 1;
		$result  = vms_efwp()->api->pause_subscription( $id, array( 'pausePeriodCount' => $periods ) );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		$subscription = vms_efwp()->api->parse_subscription( $result );
		if ( ! is_wp_error( $subscription ) ) {
			VMS_EFWP_Data_Store::upsert_subscription( $subscription, vms_efwp()->settings->is_sandbox() );
		} else {
			VMS_EFWP_Data_Store::set_subscription_status( $id, 'paused' );
		}
		wp_send_json_success( array( 'message' => __( 'Subscription paused.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: resume a subscription.
	 */
	public function ajax_resume_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->resume_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		$subscription = vms_efwp()->api->parse_subscription( $result );
		if ( ! is_wp_error( $subscription ) ) {
			VMS_EFWP_Data_Store::upsert_subscription( $subscription, vms_efwp()->settings->is_sandbox() );
		} else {
			VMS_EFWP_Data_Store::set_subscription_status( $id, 'active' );
		}
		wp_send_json_success( array( 'message' => __( 'Subscription resumed.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: uncancel a subscription.
	 */
	public function ajax_uncancel_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->uncancel_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		VMS_EFWP_Data_Store::set_subscription_status( $id, 'active' );
		wp_send_json_success( array( 'message' => __( 'Subscription uncanceled.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: charge a managed subscription.
	 */
	public function ajax_charge_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->charge_subscriptions( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Subscription charge requested.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: convert a deactivated trial subscription.
	 */
	public function ajax_convert_subscription() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->convert_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success(
			array(
				'message' => __( 'Conversion session created.', 'vms-elements-fastspring-woo-payment' ),
				'session' => $result,
			)
		);
	}

	/**
	 * AJAX: save an API order to the local database.
	 */
	public function ajax_sync_order() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing order id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->get_order( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		$order = vms_efwp()->api->parse_order( $result );
		if ( is_wp_error( $order ) ) {
			wp_send_json_error( array( 'message' => $order->get_error_message() ) );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$is_test = isset( $_POST['is_test'] ) && '1' === $_POST['is_test'];
		VMS_EFWP_Data_Store::upsert_order( $order, $is_test );
		wp_send_json_success( array( 'message' => __( 'Order saved locally.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * AJAX: cancel a quote.
	 */
	public function ajax_cancel_quote() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified above; value sanitized below.
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing quote id.', 'vms-elements-fastspring-woo-payment' ) ) );
		}
		$result = vms_efwp()->api->cancel_quote( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Quote canceled.', 'vms-elements-fastspring-woo-payment' ) ) );
	}

	/**
	 * Allow redirects to FastSpring account portals from wp-admin.
	 *
	 * @param string[] $hosts Allowed hosts.
	 * @return string[]
	 */
	public function allow_fastspring_redirect_hosts( $hosts ) {
		$settings = function_exists( 'vms_efwp' ) ? vms_efwp()->settings : null;
		if ( $settings ) {
			$storefront = $settings->storefront();
			if ( $storefront ) {
				$hosts[] = $storefront;
			}
		}
		$hosts[] = 'fastspring.com';
		$hosts[] = 'onfastspring.com';
		return array_values( array_unique( array_filter( $hosts ) ) );
	}
}
