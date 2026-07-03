<?php
/**
 * Invoice email actions on the stored orders screen.
 *
 * @package VMS_EFPG
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class VMS_EFPG_Admin_Invoice_Actions.
 */
class VMS_EFPG_Admin_Invoice_Actions {

	/**
	 * Handle resend invoice POST requests.
	 */
	public static function handle_resend_invoice() {
		$fs_order_id = VMS_EFPG_Admin_Resource_Base::post_text( 'fs_order_id' );
		$recipient   = VMS_EFPG_Admin_Resource_Base::post_email( 'recipient_email' );

		if ( $fs_order_id ) {
			$result = vms_efpg()->api->resend_order_invoice_email( $fs_order_id, $recipient );
		} else {
			$result = new WP_Error(
				'vms_efpg_invoice_resend',
				__( 'Missing FastSpring order ID for resend.', 'vms-elements-fastspring-payment-gateway' )
			);
		}

		VMS_EFPG_Admin_Resource_Base::render_result_notice( $result );
	}

	/**
	 * Render a resend-invoice submit button.
	 *
	 * @param array $args Button args.
	 */
	public static function render_resend_invoice_button( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'fs_order_id'     => '',
				'invoice_id'      => '',
				'recipient_email' => '',
				'page'            => 'vms-efpg-orders',
				'tab'             => 'stored',
				'label'           => __( 'Resend', 'vms-elements-fastspring-payment-gateway' ),
				'class'           => 'button button-small',
			)
		);

		if ( empty( $args['fs_order_id'] ) && empty( $args['invoice_id'] ) ) {
			return;
		}

		$form_action = add_query_arg(
			array(
				'page' => (string) $args['page'],
				'tab'  => (string) $args['tab'],
			),
			admin_url( 'admin.php' )
		);
		?>
		<form method="post" class="vms-efpg-inline-form" action="<?php echo esc_url( $form_action ); ?>">
			<?php wp_nonce_field( 'vms_efpg_resend_invoice' ); ?>
			<input type="hidden" name="vms_efpg_resend_invoice" value="1" />
			<?php if ( ! empty( $args['fs_order_id'] ) ) : ?>
				<input type="hidden" name="fs_order_id" value="<?php echo esc_attr( (string) $args['fs_order_id'] ); ?>" />
			<?php endif; ?>
			<?php if ( ! empty( $args['invoice_id'] ) ) : ?>
				<input type="hidden" name="invoice_id" value="<?php echo esc_attr( (string) $args['invoice_id'] ); ?>" />
			<?php endif; ?>
			<?php if ( ! empty( $args['recipient_email'] ) ) : ?>
				<input type="hidden" name="recipient_email" value="<?php echo esc_attr( (string) $args['recipient_email'] ); ?>" />
			<?php endif; ?>
			<button type="submit" class="<?php echo esc_attr( (string) $args['class'] ); ?>"><?php echo esc_html( (string) $args['label'] ); ?></button>
		</form>
		<?php
	}
}
