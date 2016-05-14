<?php
/*
 * Connection details
 */
?>

<?php if ( 'ap_settings[basic][field-hostname]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-hostname]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-hostname]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-hostname'] ); ?>" />
	<br />
	<span class="example"> 	Ex: https://api.avangate.com</span>

<?php endif; ?>

<?php if ( 'ap_settings[basic][field-proxy]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-proxy]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-proxy]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-proxy'] ); ?>" />
	<br />
	<span class="example"> 	Ex: proxy.avangate.local:8080</span>

<?php endif; ?>

<?php if ( 'ap_settings[basic][field-location]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-location]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-location]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-location'] ); ?>" />
	<br />
	<span class="example"> Ex: /soap/3.0/</span>

<?php endif; ?>

<?php if ( 'ap_settings[basic][field-merchant-code]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-merchant-code]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-merchant-code]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-merchant-code'] ); ?>" />
	<br />
	<span class="example"> Your account's merchant code available in the 'System settings' area of the cPanel.</span>

<?php endif; ?>

<?php if ( 'ap_settings[basic][field-merchant-key]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-merchant-key]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-merchant-key]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-merchant-key'] ); ?>" />
	<br />
	<span class="example"> Your account's secret key available in the 'System settings' area of the cPanel.</span>

<?php endif; ?>

<?php
/*
 * Advanced Section
 */
?>


<?php if ( 'ap_settings[advanced][field-import-button]' == $field['label_for'] ) : ?>
	<input type="button" id="<?php esc_attr_e( 'ap_settings[advanced][field-import-button]' ); ?>" value="Import products" class="ap-import-products button-primary" name="<?php esc_attr_e( 'ap_settings[advanced][field-import-button]' ); ?>" />
<?php endif; ?>
