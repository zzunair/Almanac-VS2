<?php
/** Contact / form + contact info sidebar. */
$address_lines     = vss_lines( vss_field( 'contact', 'form_info', 'address_text' ) );
$phone_email_lines = vss_lines( vss_field( 'contact', 'form_info', 'phone_email_text' ) );
$hours_lines       = vss_lines( vss_field( 'contact', 'form_info', 'hours_text' ) );
?>
<div class="tw-container" style="max-width:1000px;padding-bottom:60px;display:flex;gap:44px;flex-wrap:wrap;">
	<form class="tw-form tw-contact-form" style="flex:1;min-width:280px;">
		<label class="tw-field"><?php echo esc_html( vss_field( 'contact', 'form_info', 'name_label' ) ); ?><input type="text" name="name" required placeholder="<?php echo esc_attr( vss_field( 'contact', 'form_info', 'name_placeholder' ) ); ?>" /></label>
		<label class="tw-field"><?php echo esc_html( vss_field( 'contact', 'form_info', 'email_label' ) ); ?><input type="email" name="email" required placeholder="<?php echo esc_attr( vss_field( 'contact', 'form_info', 'email_placeholder' ) ); ?>" /></label>
		<label class="tw-field"><?php echo esc_html( vss_field( 'contact', 'form_info', 'phone_label' ) ); ?><input type="tel" name="phone" placeholder="<?php echo esc_attr( vss_field( 'contact', 'form_info', 'phone_placeholder' ) ); ?>" /></label>
		<label class="tw-field"><?php echo esc_html( vss_field( 'contact', 'form_info', 'message_label' ) ); ?><textarea name="message" required placeholder="<?php echo esc_attr( vss_field( 'contact', 'form_info', 'message_placeholder' ) ); ?>" rows="5"></textarea></label>
		<p class="tw-form-error" data-contact-error hidden><?php echo esc_html( vss_field( 'contact', 'form_info', 'error_text' ) ); ?></p>
		<button type="submit" class="tw-btn tw-btn-primary" style="width:fit-content;"><?php echo esc_html( vss_field( 'contact', 'form_info', 'send_button_text' ) ); ?></button>
		<div class="tw-form-success" data-contact-success hidden>
			<h3><?php echo esc_html( vss_field( 'contact', 'form_info', 'success_heading' ) ); ?></h3>
			<p><?php echo esc_html( vss_field( 'contact', 'form_info', 'success_text' ) ); ?></p>
		</div>
	</form>

	<div style="flex:0 0 260px;display:flex;flex-direction:column;gap:26px;">
		<div>
			<div style="font-size:0.75rem;font-weight:700;color:var(--tw-text-faint);margin-bottom:6px;"><?php echo esc_html( vss_field( 'contact', 'form_info', 'address_label' ) ); ?></div>
			<p style="font-size:0.875rem;line-height:1.6;color:var(--tw-text);margin:0;"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $address_lines ) ) ); ?></p>
		</div>
		<div>
			<div style="font-size:0.75rem;font-weight:700;color:var(--tw-text-faint);margin-bottom:6px;"><?php echo esc_html( vss_field( 'contact', 'form_info', 'phone_email_label' ) ); ?></div>
			<p style="font-size:0.875rem;line-height:1.6;color:var(--tw-text);margin:0;"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $phone_email_lines ) ) ); ?></p>
		</div>
		<div>
			<div style="font-size:0.75rem;font-weight:700;color:var(--tw-text-faint);margin-bottom:6px;"><?php echo esc_html( vss_field( 'contact', 'form_info', 'hours_label' ) ); ?></div>
			<p style="font-size:0.875rem;line-height:1.8;color:var(--tw-text);margin:0;"><?php echo wp_kses_post( implode( '<br />', array_map( 'esc_html', $hours_lines ) ) ); ?></p>
		</div>
	</div>
</div>
