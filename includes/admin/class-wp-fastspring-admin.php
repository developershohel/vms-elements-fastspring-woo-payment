<?php
/**
 * Admin bootstrap.
 *
 * @package WP_FastSpring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WP_FastSpring_Admin.
 */
class WP_FastSpring_Admin {

	const MENU_SLUG = 'wp-fastspring';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wp_fastspring_dashboard_data', array( $this, 'ajax_dashboard_data' ) );
		add_action( 'wp_ajax_wp_fastspring_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'wp_ajax_wp_fastspring_sync_subscription', array( $this, 'ajax_sync_subscription' ) );
		add_action( 'wp_ajax_wp_fastspring_cancel_subscription', array( $this, 'ajax_cancel_subscription' ) );
		add_action( 'admin_post_wp_fastspring_save_settings', array( 'WP_FastSpring_Admin_Settings', 'handle_save' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'FastSpring', 'wp-fastspring' ),
			__( 'FastSpring', 'wp-fastspring' ),
			'manage_options',
			self::MENU_SLUG,
			array( 'WP_FastSpring_Admin_Dashboard', 'render' ),
			'dashicons-chart-area',
			56
		);

		$pages = array(
			array( self::MENU_SLUG,                __( 'Dashboard', 'wp-fastspring' ),     array( 'WP_FastSpring_Admin_Dashboard', 'render' ) ),
			array( 'wp-fastspring-orders',         __( 'Orders', 'wp-fastspring' ),        array( 'WP_FastSpring_Admin_Orders', 'render' ) ),
			array( 'wp-fastspring-subscriptions',  __( 'Subscriptions', 'wp-fastspring' ), array( 'WP_FastSpring_Admin_Subscriptions', 'render' ) ),
			array( 'wp-fastspring-accounts',       __( 'Accounts', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Accounts', 'render' ) ),
			array( 'wp-fastspring-products',       __( 'Products', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Products', 'render' ) ),
			array( 'wp-fastspring-coupons',        __( 'Coupons', 'wp-fastspring' ),       array( 'WP_FastSpring_Admin_Coupons', 'render' ) ),
			array( 'wp-fastspring-invoices',       __( 'Invoices', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Invoices', 'render' ) ),
			array( 'wp-fastspring-quotes',         __( 'Quotes', 'wp-fastspring' ),        array( 'WP_FastSpring_Admin_Quotes', 'render' ) ),
			array( 'wp-fastspring-returns',        __( 'Returns', 'wp-fastspring' ),       array( 'WP_FastSpring_Admin_Returns', 'render' ) ),
			array( 'wp-fastspring-sessions',       __( 'Sessions', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Sessions', 'render' ) ),
			array( 'wp-fastspring-events',         __( 'Events', 'wp-fastspring' ),        array( 'WP_FastSpring_Admin_Events', 'render' ) ),
			array( 'wp-fastspring-reports',        __( 'Reports', 'wp-fastspring' ),       array( 'WP_FastSpring_Admin_Reports', 'render' ) ),
			array( 'wp-fastspring-webhooks',       __( 'Webhooks', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Webhooks', 'render' ) ),
			array( 'wp-fastspring-tools',          __( 'Tools', 'wp-fastspring' ),         array( 'WP_FastSpring_Admin_Tools', 'render' ) ),
			array( 'wp-fastspring-settings',       __( 'Settings', 'wp-fastspring' ),      array( 'WP_FastSpring_Admin_Settings', 'render' ) ),
		);

		foreach ( $pages as $page ) {
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
		if ( false === strpos( $hook, 'wp-fastspring' ) && false === strpos( $hook, 'fastspring' ) ) {
			return;
		}

		wp_enqueue_style(
			'wp-fastspring-admin',
			WP_FASTSPRING_URL . 'assets/css/admin.css',
			array(),
			WP_FASTSPRING_VERSION
		);

		/**
		 * Chart.js is loaded from a CDN by default to keep the plugin lightweight.
		 * Filter `wp_fastspring_chartjs_url` to point at a self-hosted copy.
		 */
		$chart_url = apply_filters(
			'wp_fastspring_chartjs_url',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js'
		);
		wp_enqueue_script(
			'wp-fastspring-chartjs',
			$chart_url,
			array(),
			'4.4.4',
			true
		);

		wp_enqueue_script(
			'wp-fastspring-admin',
			WP_FASTSPRING_URL . 'assets/js/admin.js',
			array( 'jquery', 'wp-fastspring-chartjs' ),
			WP_FASTSPRING_VERSION,
			true
		);

		wp_localize_script(
			'wp-fastspring-admin',
			'WPFastSpring',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wp_fastspring_admin' ),
				'currency' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'i18n'     => array(
					'loading'  => __( 'Loading...', 'wp-fastspring' ),
					'no_data'  => __( 'No data yet.', 'wp-fastspring' ),
					'error'    => __( 'Something went wrong.', 'wp-fastspring' ),
					'confirm_cancel' => __( 'Cancel this subscription?', 'wp-fastspring' ),
					'copy_json'      => __( 'Copy JSON', 'wp-fastspring' ),
				),
			)
		);
	}

	/**
	 * AJAX: dashboard data.
	 */
	public function ajax_dashboard_data() {
		check_ajax_referer( 'wp_fastspring_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-fastspring' ) ), 403 );
		}

		$range        = isset( $_REQUEST['range'] ) ? (int) $_REQUEST['range'] : 30;
		$include_test = isset( $_REQUEST['include_test'] ) && '1' === $_REQUEST['include_test'];

		$start = gmdate( 'Y-m-d 00:00:00', time() - ( ( $range - 1 ) * DAY_IN_SECONDS ) );
		$end   = gmdate( 'Y-m-d 23:59:59' );

		$summary       = WP_FastSpring_Stats::sales_summary( $start, $end, $include_test );
		$daily         = WP_FastSpring_Stats::daily_revenue( $range, $include_test );
		$top_products  = WP_FastSpring_Stats::top_products( 6, $include_test );
		$top_countries = WP_FastSpring_Stats::top_countries( 6, $include_test );
		$subs          = WP_FastSpring_Stats::subscriptions_summary( $include_test );
		$recent        = WP_FastSpring_Stats::recent_orders( 8 );

		wp_send_json_success(
			array(
				'summary'       => $summary,
				'daily'         => $daily,
				'top_products'  => $top_products,
				'top_countries' => $top_countries,
				'subscriptions' => $subs,
				'recent_orders' => $recent,
			)
		);
	}

	/**
	 * AJAX: test API connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'wp_fastspring_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-fastspring' ) ), 403 );
		}
		$result = wp_fastspring()->api->test_connection();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connected to FastSpring successfully.', 'wp-fastspring' ) ) );
	}

	/**
	 * AJAX: pull a fresh copy of a subscription from the API.
	 */
	public function ajax_sync_subscription() {
		check_ajax_referer( 'wp_fastspring_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-fastspring' ) ), 403 );
		}
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'wp-fastspring' ) ) );
		}
		$result = wp_fastspring()->api->get_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		WP_FastSpring_Data_Store::upsert_subscription( $result, wp_fastspring()->settings->is_sandbox() );
		wp_send_json_success( array( 'message' => __( 'Subscription synced.', 'wp-fastspring' ) ) );
	}

	/**
	 * AJAX: cancel a subscription.
	 */
	public function ajax_cancel_subscription() {
		check_ajax_referer( 'wp_fastspring_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'wp-fastspring' ) ), 403 );
		}
		$id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Missing subscription id.', 'wp-fastspring' ) ) );
		}
		$result = wp_fastspring()->api->cancel_subscription( $id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		WP_FastSpring_Data_Store::set_subscription_status( $id, 'canceled' );
		wp_send_json_success( array( 'message' => __( 'Subscription cancellation requested.', 'wp-fastspring' ) ) );
	}
}
