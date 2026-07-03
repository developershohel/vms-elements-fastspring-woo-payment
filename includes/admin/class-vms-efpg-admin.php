<?php
/**
 * Admin bootstrap.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Admin.
 */
class VMS_EFPG_Admin {

	const MENU_SLUG = 'vms-efpg';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter( 'allowed_redirect_hosts', array( $this, 'allow_fastspring_redirect_hosts' ) );
		add_action( 'wp_ajax_vms_efpg_dashboard_data', array( $this, 'ajax_dashboard_data' ) );
		add_action( 'wp_ajax_vms_efpg_test_connection', array( $this, 'ajax_test_connection' ) );
		add_action( 'admin_post_vms_efpg_save_settings', array( 'VMS_EFPG_Admin_Settings', 'handle_save' ) );
		add_action( 'admin_post_vms_efpg_provision_custom_price', array( 'VMS_EFPG_Admin_Settings', 'handle_provision_custom_price' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'FastSpring', 'vms-elements-fastspring-payment-gateway' ),
			__( 'FastSpring', 'vms-elements-fastspring-payment-gateway' ),
			'manage_options',
			self::MENU_SLUG,
			array( 'VMS_EFPG_Admin_Dashboard', 'render' ),
			'dashicons-chart-area',
			56
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'vms-elements-fastspring-payment-gateway' ),
			__( 'Dashboard', 'vms-elements-fastspring-payment-gateway' ),
			'manage_options',
			self::MENU_SLUG,
			array( 'VMS_EFPG_Admin_Dashboard', 'render' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Orders', 'vms-elements-fastspring-payment-gateway' ),
			__( 'Orders', 'vms-elements-fastspring-payment-gateway' ),
			'manage_options',
			'vms-efpg-orders',
			array( 'VMS_EFPG_Admin_Orders', 'render' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'vms-elements-fastspring-payment-gateway' ),
			__( 'Settings', 'vms-elements-fastspring-payment-gateway' ),
			'manage_options',
			'vms-efpg-settings',
			array( 'VMS_EFPG_Admin_Settings', 'render' )
		);
	}

	/**
	 * Enqueue admin scripts/styles only on plugin pages.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_assets( $hook ) {
		if ( false === strpos( $hook, 'vms-efpg' ) && false === strpos( $hook, 'fastspring' ) ) {
			return;
		}

		wp_enqueue_style(
			'vms-efpg-admin',
			VMS_EFPG_URL . 'assets/css/admin.css',
			array(),
			VMS_EFPG_VERSION
		);

		wp_enqueue_script(
			'vms-efpg-chartjs',
			VMS_EFPG_URL . 'assets/js/vendor/chart.umd.min.js',
			array(),
			'4.5.1',
			true
		);

		wp_enqueue_script(
			'vms-efpg-admin',
			VMS_EFPG_URL . 'assets/js/admin.js',
			array( 'jquery', 'vms-efpg-chartjs' ),
			VMS_EFPG_VERSION,
			true
		);

		wp_localize_script(
			'vms-efpg-admin',
			'VMS_EFPG',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'vms_efpg_admin' ),
				'currency' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'i18n'     => array(
					'test_ok'    => __( 'Connected to FastSpring successfully.', 'vms-elements-fastspring-payment-gateway' ),
					'test_fail'  => __( 'Connection failed.', 'vms-elements-fastspring-payment-gateway' ),
					'loading'    => __( 'Loading…', 'vms-elements-fastspring-payment-gateway' ),
					'no_data'    => __( 'No data yet.', 'vms-elements-fastspring-payment-gateway' ),
					'error'      => __( 'Could not load chart data.', 'vms-elements-fastspring-payment-gateway' ),
					'chart_fail' => __( 'Could not load chart data.', 'vms-elements-fastspring-payment-gateway' ),
					'revenue_label'  => __( 'Revenue', 'vms-elements-fastspring-payment-gateway' ),
					'orders_label'   => __( 'Orders', 'vms-elements-fastspring-payment-gateway' ),
					/* translators: %d: order count */
					'order_singular' => __( '%d order', 'vms-elements-fastspring-payment-gateway' ),
					/* translators: %d: order count */
					'order_plural'   => __( '%d orders', 'vms-elements-fastspring-payment-gateway' ),
					'mrr_prefix'     => __( 'MRR: ', 'vms-elements-fastspring-payment-gateway' ),
					'no_mrr'         => __( 'No active recurring revenue yet.', 'vms-elements-fastspring-payment-gateway' ),
					'sub_active'     => __( 'Active', 'vms-elements-fastspring-payment-gateway' ),
					'sub_paused'     => __( 'Paused', 'vms-elements-fastspring-payment-gateway' ),
					'sub_trial'      => __( 'Trial', 'vms-elements-fastspring-payment-gateway' ),
					'sub_overdue'    => __( 'Overdue', 'vms-elements-fastspring-payment-gateway' ),
					'sub_canceled'   => __( 'Canceled', 'vms-elements-fastspring-payment-gateway' ),
					'sub_deactivated' => __( 'Deactivated', 'vms-elements-fastspring-payment-gateway' ),
				),
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'vms-efpg-admin', 'vms-elements-fastspring-payment-gateway', VMS_EFPG_PATH . 'languages' );
		}
	}

	/**
	 * AJAX: dashboard data.
	 */
	public function ajax_dashboard_data() {
		check_ajax_referer( 'vms_efpg_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-payment-gateway' ) ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above.
		$range = isset( $_REQUEST['range'] ) ? max( 1, min( 365, absint( $_REQUEST['range'] ) ) ) : 30;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified; sanitized below.
		$include_test = isset( $_REQUEST['include_test'] ) && '1' === sanitize_text_field( wp_unslash( $_REQUEST['include_test'] ) );

		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$week_start  = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$range_start = gmdate( 'Y-m-d 00:00:00', time() - ( ( $range - 1 ) * DAY_IN_SECONDS ) );

		$recent = array();
		foreach ( VMS_EFPG_Stats::recent_orders( 8, $include_test ) as $row ) {
			$recent[] = array(
				'fs_order_id'   => isset( $row['fs_order_id'] ) ? (string) $row['fs_order_id'] : '',
				'customer_name' => isset( $row['customer_name'] ) ? (string) $row['customer_name'] : '',
				'email'         => isset( $row['email'] ) ? (string) $row['email'] : '',
				'currency'      => isset( $row['currency'] ) ? (string) $row['currency'] : '',
				'total'         => isset( $row['total'] ) ? (float) $row['total'] : 0,
				'status'        => isset( $row['status'] ) ? (string) $row['status'] : '',
				'created_at'    => isset( $row['created_at'] ) ? (string) $row['created_at'] : '',
			);
		}

		wp_send_json_success(
			array(
				'site'          => VMS_EFPG_Data_Store::get_site_context(),
				'kpis'          => array(
					'today'    => VMS_EFPG_Stats::sales_summary( $today_start, $today_end, $include_test ),
					'week'     => VMS_EFPG_Stats::sales_summary( $week_start, $today_end, $include_test ),
					'month'    => VMS_EFPG_Stats::sales_summary( $month_start, $today_end, $include_test ),
					'all_time' => VMS_EFPG_Stats::sales_summary( '1970-01-01 00:00:00', $today_end, $include_test ),
				),
				'daily'         => VMS_EFPG_Stats::daily_revenue( $range, $include_test ),
				'top_products'  => VMS_EFPG_Stats::top_products( 6, $include_test, $range_start, $today_end ),
				'top_countries' => VMS_EFPG_Stats::top_countries( 6, $include_test, $range_start, $today_end ),
				'subscriptions' => VMS_EFPG_Stats::subscriptions_summary( $include_test ),
				'recent_orders' => $recent,
			)
		);
	}

	/**
	 * AJAX: test API connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'vms_efpg_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-payment-gateway' ) ), 403 );
		}
		$result = vms_efpg()->api->test_connection();
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}
		wp_send_json_success( array( 'message' => __( 'Connected to FastSpring successfully.', 'vms-elements-fastspring-payment-gateway' ) ) );
	}

	/**
	 * Allow redirects to FastSpring account portals from wp-admin.
	 *
	 * @param string[] $hosts Allowed hosts.
	 * @return string[]
	 */
	public function allow_fastspring_redirect_hosts( $hosts ) {
		$settings = function_exists( 'vms_efpg' ) ? vms_efpg()->settings : null;
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
