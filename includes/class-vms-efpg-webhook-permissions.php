<?php
/**
 * FastSpring webhook event permissions (subscribed events per receiver URL).
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Webhook_Permissions.
 */
class VMS_EFPG_Webhook_Permissions {

	const CACHE_TTL = 600;

	/**
	 * API client.
	 *
	 * @var VMS_EFPG_API
	 */
	private $api;

	/**
	 * Settings.
	 *
	 * @var VMS_EFPG_Settings
	 */
	private $settings;

	/**
	 * In-memory cache for the current request.
	 *
	 * @var string[]|null|null
	 */
	private $runtime_cache = null;

	/**
	 * Constructor.
	 *
	 * @param VMS_EFPG_API      $api      API.
	 * @param VMS_EFPG_Settings $settings Settings.
	 */
	public function __construct( VMS_EFPG_API $api, VMS_EFPG_Settings $settings ) {
		$this->api      = $api;
		$this->settings = $settings;
	}

	/**
	 * Events the plugin knows how to handle, grouped for admin display.
	 *
	 * @return array<string, array{label:string,category:string,required:bool,description:string}>
	 */
	public static function handler_catalog() {
		return array(
			'order.completed'               => array(
				'label'       => __( 'Order completed', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'order',
				'required'    => true,
				'description' => __( 'Completes linked WooCommerce orders and stores revenue.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'order.canceled'                => array(
				'label'       => __( 'Order canceled', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'order',
				'required'    => false,
				'description' => __( 'Cancels linked WooCommerce orders.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'order.approval.pending'        => array(
				'label'       => __( 'Order approval pending', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'order',
				'required'    => false,
				'description' => __( 'Stores pending invoice orders.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'order.payment.pending'         => array(
				'label'       => __( 'Order payment pending', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'order',
				'required'    => false,
				'description' => __( 'Stores orders awaiting payment.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'return.created'                => array(
				'label'       => __( 'Return created', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'return',
				'required'    => true,
				'description' => __( 'Marks orders refunded in WooCommerce.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'order.refund'                  => array(
				'label'       => __( 'Order refund', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'return',
				'required'    => false,
				'description' => __( 'Legacy refund event — marks orders refunded.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.activated'        => array(
				'label'       => __( 'Subscription activated', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Stores new subscriptions.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.charge.completed' => array(
				'label'       => __( 'Subscription charge completed', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Updates subscription rebill data.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.updated'          => array(
				'label'       => __( 'Subscription updated', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Syncs subscription edits.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.trial.reminder'   => array(
				'label'       => __( 'Subscription trial reminder', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Syncs trial reminder notifications.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.payment.overdue'  => array(
				'label'       => __( 'Subscription payment overdue', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Syncs overdue payment notifications.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.payment.reminder' => array(
				'label'       => __( 'Subscription payment reminder', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Syncs renewal reminder notifications.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.canceled'         => array(
				'label'       => __( 'Subscription canceled', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Marks subscriptions canceled.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'subscription.deactivated'      => array(
				'label'       => __( 'Subscription deactivated', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'subscription',
				'required'    => false,
				'description' => __( 'Marks subscriptions deactivated.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'account.created'               => array(
				'label'       => __( 'Account created', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'account',
				'required'    => false,
				'description' => __( 'Acknowledged — no local persistence.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'account.updated'               => array(
				'label'       => __( 'Account updated', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'account',
				'required'    => false,
				'description' => __( 'Acknowledged — no local persistence.', 'vms-elements-fastspring-payment-gateway' ),
			),
			'mailingListEntry.updated'      => array(
				'label'       => __( 'Mailing list updated', 'vms-elements-fastspring-payment-gateway' ),
				'category'    => 'mailing',
				'required'    => false,
				'description' => __( 'Acknowledged — no local persistence.', 'vms-elements-fastspring-payment-gateway' ),
			),
		);
	}

	/**
	 * Whether permissions have been synced at least once for the active mode.
	 *
	 * @return bool
	 */
	public function has_synced_permissions() {
		$synced_at = (int) $this->settings->get( $this->storage_key() . '_synced_at', 0 );
		return $synced_at > 0;
	}

	/**
	 * Fetch current permissions from FastSpring and persist them.
	 *
	 * @return string[]|WP_Error Enabled event types, or ['*'] when all events are selected.
	 */
	public function refresh() {
		if ( ! $this->settings->has_credentials() ) {
			return new WP_Error(
				'vms_efpg_webhook_permissions',
				__( 'Add FastSpring API credentials before syncing webhook permissions.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$response = $this->api->get_webhooks();
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$events = $this->api->extract_webhook_event_permissions( $response, $this->settings->webhook_url() );
		if ( null === $events ) {
			return new WP_Error(
				'vms_efpg_webhook_permissions',
				__( 'No FastSpring webhook endpoint matches this plugin receiver URL. Add the URL under FastSpring → Integrations → Webhooks first.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		$this->persist_permissions( $events );
		$this->runtime_cache = $events;

		return $events;
	}

	/**
	 * Enabled event types for the active mode.
	 *
	 * Returns null when permissions were never synced (permissive fallback in is_event_enabled()).
	 *
	 * @param bool $force_refresh Skip caches and call the API.
	 * @return string[]|null
	 */
	public function get_enabled_events( $force_refresh = false ) {
		if ( ! $force_refresh && null !== $this->runtime_cache ) {
			return $this->runtime_cache;
		}

		if ( ! $force_refresh ) {
			$cached = get_transient( $this->transient_key() );
			if ( is_array( $cached ) ) {
				$this->runtime_cache = $cached;
				return $cached;
			}
		}

		if ( $force_refresh && $this->settings->has_credentials() ) {
			$refreshed = $this->refresh();
			if ( ! is_wp_error( $refreshed ) ) {
				return $refreshed;
			}
		}

		$stored = $this->settings->get( $this->storage_key(), null );
		if ( is_array( $stored ) ) {
			$this->runtime_cache = $stored;
			set_transient( $this->transient_key(), $stored, self::CACHE_TTL );
			return $stored;
		}

		return null;
	}

	/**
	 * Whether the plugin should apply handlers for an event type.
	 *
	 * @param string $event_type FastSpring event type.
	 * @return bool
	 */
	public function is_event_enabled( $event_type ) {
		$event_type = sanitize_key( (string) $event_type );
		if ( '' === $event_type ) {
			return false;
		}

		$enabled = $this->get_enabled_events();
		if ( null === $enabled ) {
			return true;
		}

		if ( $this->events_include_all( $enabled ) ) {
			return true;
		}

		return in_array( $event_type, $enabled, true );
	}

	/**
	 * Build an admin status map for handler catalog entries.
	 *
	 * @return array<string, array{enabled:bool,required:bool,label:string,category:string,description:string}>
	 */
	public function get_handler_statuses() {
		$enabled   = $this->get_enabled_events();
		$all       = null === $enabled ? null : $this->events_include_all( $enabled );
		$statuses  = array();

		foreach ( self::handler_catalog() as $type => $meta ) {
			$is_enabled = null === $enabled ? null : ( $all || in_array( $type, $enabled, true ) );
			$statuses[ $type ] = array(
				'enabled'     => $is_enabled,
				'required'    => ! empty( $meta['required'] ),
				'label'       => $meta['label'],
				'category'    => $meta['category'],
				'description' => $meta['description'],
			);
		}

		return $statuses;
	}

	/**
	 * Persist permissions to settings + transient.
	 *
	 * @param string[] $events Event types.
	 */
	private function persist_permissions( $events ) {
		$events = array_values( array_unique( array_filter( array_map( 'strval', (array) $events ) ) ) );
		$this->settings->set( $this->storage_key(), $events );
		$this->settings->set( $this->storage_key() . '_synced_at', time() );
		$this->settings->refresh();
		set_transient( $this->transient_key(), $events, self::CACHE_TTL );
	}

	/**
	 * Whether an events list represents "all events".
	 *
	 * @param string[] $events Event types.
	 * @return bool
	 */
	private function events_include_all( $events ) {
		foreach ( (array) $events as $event ) {
			$event = strtolower( (string) $event );
			if ( in_array( $event, array( '*', 'all', 'all events', 'all_events' ), true ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Settings key for stored permissions.
	 *
	 * @return string
	 */
	private function storage_key() {
		return $this->settings->is_sandbox() ? 'webhook_enabled_events_sandbox' : 'webhook_enabled_events_live';
	}

	/**
	 * Transient key for cached permissions.
	 *
	 * @return string
	 */
	private function transient_key() {
		return 'vms_efpg_webhook_permissions_' . ( $this->settings->is_sandbox() ? 'sandbox' : 'live' );
	}
}
