<?php
/**
 * Admin bootstrap (free core screens only).
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
		add_action( 'admin_post_vms_efwp_save_settings', array( 'VMS_EFWP_Admin_Settings', 'handle_save' ) );
		add_action( 'admin_post_vms_efwp_provision_custom_price', array( 'VMS_EFWP_Admin_Settings', 'handle_provision_custom_price' ) );

		/**
		 * Pro add-on registers extra menus, AJAX, and admin_post handlers here.
		 */
		do_action( 'vms_efwp_register_admin', $this );
	}

	/**
	 * Register free admin menu.
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

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Dashboard', 'vms-elements-fastspring-woo-payment' ),
			__( 'Dashboard', 'vms-elements-fastspring-woo-payment' ),
			'manage_options',
			self::MENU_SLUG,
			array( 'VMS_EFWP_Admin_Dashboard', 'render' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Orders', 'vms-elements-fastspring-woo-payment' ),
			__( 'Orders', 'vms-elements-fastspring-woo-payment' ),
			'manage_options',
			'vms-efwp-orders',
			array( 'VMS_EFWP_Admin_Orders', 'render' )
		);

		add_submenu_page(
			self::MENU_SLUG,
			__( 'Settings', 'vms-elements-fastspring-woo-payment' ),
			__( 'Settings', 'vms-elements-fastspring-woo-payment' ),
			'manage_options',
			'vms-efwp-settings',
			array( 'VMS_EFWP_Admin_Settings', 'render' )
		);

		if ( ! vms_efwp_is_pro() ) {
			add_submenu_page(
				self::MENU_SLUG,
				__( 'Upgrade to Pro', 'vms-elements-fastspring-woo-payment' ),
				__( 'Upgrade to Pro', 'vms-elements-fastspring-woo-payment' ),
				'manage_options',
				'vms-efwp-upgrade',
				array( $this, 'render_upgrade_page' )
			);
		}

		/**
		 * Pro add-on registers Sales / Catalog / Integrations submenus here.
		 */
		do_action( 'vms_efwp_register_admin_menu', self::MENU_SLUG );
	}

	/**
	 * Render the in-admin Pro upgrade page.
	 */
	public function render_upgrade_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap vms-efwp-wrap">
			<div class="vms-efwp-header">
				<div class="vms-efwp-header__title">
					<h1><?php esc_html_e( 'Upgrade to Pro', 'vms-elements-fastspring-woo-payment' ); ?></h1>
				</div>
			</div>
			<div class="vms-efwp-card" style="max-width:720px;">
				<h2><?php esc_html_e( 'Full FastSpring operations hub', 'vms-elements-fastspring-woo-payment' ); ?></h2>
				<p><?php esc_html_e( 'Pro is a separate plugin from VMS Elements. Install it after purchase, then activate your license under FastSpring → Pro License.', 'vms-elements-fastspring-woo-payment' ); ?></p>
				<ul style="list-style:disc;margin-left:1.5em;">
					<li><?php esc_html_e( 'Subscription products and customer self-service', 'vms-elements-fastspring-woo-payment' ); ?></li>
					<li><?php esc_html_e( 'Shortcodes, checkout links, and payment success pages', 'vms-elements-fastspring-woo-payment' ); ?></li>
					<li><?php esc_html_e( 'Full FastSpring API admin: products, coupons, invoices, reports, and more', 'vms-elements-fastspring-woo-payment' ); ?></li>
				</ul>
				<p>
					<a class="button button-primary button-hero" href="<?php echo esc_url( VMS_EFWP_Features::pro_url() ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Get Pro on VMS Elements', 'vms-elements-fastspring-woo-payment' ); ?>
					</a>
				</p>
			</div>
		</div>
		<?php
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

		$chartjs_url = apply_filters( 'vms_efwp_chartjs_url', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js' );
		wp_enqueue_script( 'vms-efwp-chartjs', $chartjs_url, array(), '4.4.4', true );

		wp_enqueue_script(
			'vms-efwp-admin',
			VMS_EFWP_URL . 'assets/js/admin.js',
			array( 'jquery', 'vms-efwp-chartjs' ),
			VMS_EFWP_VERSION,
			true
		);

		wp_localize_script(
			'vms-efwp-admin',
			'VMS_EFWP',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'vms_efwp_admin' ),
				'currency' => function_exists( 'get_woocommerce_currency_symbol' ) ? get_woocommerce_currency_symbol() : '$',
				'is_pro'   => vms_efwp_is_pro(),
				'i18n'     => array(
					'test_ok'    => __( 'Connected to FastSpring successfully.', 'vms-elements-fastspring-woo-payment' ),
					'test_fail'  => __( 'Connection failed.', 'vms-elements-fastspring-woo-payment' ),
					'loading'    => __( 'Loading…', 'vms-elements-fastspring-woo-payment' ),
					'no_data'    => __( 'No data yet.', 'vms-elements-fastspring-woo-payment' ),
					'error'      => __( 'Could not load chart data.', 'vms-elements-fastspring-woo-payment' ),
					'chart_fail' => __( 'Could not load chart data.', 'vms-elements-fastspring-woo-payment' ),
					'revenue_label'  => __( 'Revenue', 'vms-elements-fastspring-woo-payment' ),
					'orders_label'   => __( 'Orders', 'vms-elements-fastspring-woo-payment' ),
					/* translators: %d: order count */
					'order_singular' => __( '%d order', 'vms-elements-fastspring-woo-payment' ),
					/* translators: %d: order count */
					'order_plural'   => __( '%d orders', 'vms-elements-fastspring-woo-payment' ),
					'mrr_prefix'     => __( 'MRR: ', 'vms-elements-fastspring-woo-payment' ),
					'no_mrr'         => __( 'No active recurring revenue yet.', 'vms-elements-fastspring-woo-payment' ),
					'sub_active'     => __( 'Active', 'vms-elements-fastspring-woo-payment' ),
					'sub_paused'     => __( 'Paused', 'vms-elements-fastspring-woo-payment' ),
					'sub_trial'      => __( 'Trial', 'vms-elements-fastspring-woo-payment' ),
					'sub_overdue'    => __( 'Overdue', 'vms-elements-fastspring-woo-payment' ),
					'sub_canceled'   => __( 'Canceled', 'vms-elements-fastspring-woo-payment' ),
					'sub_deactivated' => __( 'Deactivated', 'vms-elements-fastspring-woo-payment' ),
				),
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'vms-efwp-admin', 'vms-elements-fastspring-woo-payment', VMS_EFWP_PATH . 'languages' );
		}

		do_action( 'vms_efwp_admin_enqueue_assets', $hook );
	}

	/**
	 * AJAX: dashboard data.
	 */
	public function ajax_dashboard_data() {
		check_ajax_referer( 'vms_efwp_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'vms-elements-fastspring-woo-payment' ) ), 403 );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above.
		$range = isset( $_REQUEST['range'] ) ? max( 1, min( 365, (int) $_REQUEST['range'] ) ) : 30;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above.
		$include_test = isset( $_REQUEST['include_test'] ) && '1' === $_REQUEST['include_test'];

		$today_end   = gmdate( 'Y-m-d 23:59:59' );
		$today_start = gmdate( 'Y-m-d 00:00:00' );
		$week_start  = gmdate( 'Y-m-d 00:00:00', time() - 6 * DAY_IN_SECONDS );
		$month_start = gmdate( 'Y-m-d 00:00:00', time() - 29 * DAY_IN_SECONDS );
		$range_start = gmdate( 'Y-m-d 00:00:00', time() - ( ( $range - 1 ) * DAY_IN_SECONDS ) );

		$recent = array();
		foreach ( VMS_EFWP_Stats::recent_orders( 8, $include_test ) as $row ) {
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
				'site'          => VMS_EFWP_Data_Store::get_site_context(),
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
				'recent_orders' => $recent,
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
