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

<?php if ( 'ap_settings[basic][field-proxy-host]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-proxy-host]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-proxy-host]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-proxy-host'] ); ?>" />
	<br />
	<span class="example"> 	Ex: proxy.avangate.local</span>
<?php endif; ?>

<?php if ( 'ap_settings[basic][field-proxy-port]' == $field['label_for'] ) : ?>

	<input id="<?php esc_attr_e( 'ap_settings[basic][field-proxy-port]' ); ?>" name="<?php esc_attr_e( 'ap_settings[basic][field-proxy-port]' ); ?>" class="regular-text" value="<?php esc_attr_e( $settings['basic']['field-proxy-port'] ); ?>" />
	<br />
	<span class="example"> 	Ex: 8080</span>

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