<?php
/**
 * Maintenance mode. While it is on, every public URL redirects to /maintenance/.
 * A timer brings the site back on its own when the end time is reached.
 */

defined( 'ABSPATH' ) || exit;

function vss_maintenance_mod( $key, $default = '' ) {
	return get_theme_mod( 'vss_maintenance_' . $key, $default );
}

function vss_sanitize_maintenance_end( $value ) {
	$value = sanitize_text_field( $value );
	if ( '' === $value ) {
		return '';
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $value, wp_timezone() );
	if ( ! $dt ) {
		$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i:s', $value, wp_timezone() );
	}
	return $dt ? $dt->format( 'Y-m-d\TH:i' ) : '';
}

function vss_maintenance_end_timestamp() {
	$raw = (string) vss_maintenance_mod( 'timer_end', '' );
	if ( '' === $raw ) {
		return 0;
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $raw, wp_timezone() );
	return $dt ? $dt->getTimestamp() : 0;
}

function vss_maintenance_active() {
	if ( ! vss_maintenance_mod( 'enabled', false ) ) {
		return false;
	}
	if ( ! vss_maintenance_mod( 'timer_enabled', false ) ) {
		return true;
	}
	$end = vss_maintenance_end_timestamp();
	if ( ! $end ) {
		return true;
	}
	return time() < $end;
}

function vss_maintenance_request_path() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = wp_parse_url( $uri, PHP_URL_PATH );
	$path = is_string( $path ) ? $path : '';
	$home = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home = is_string( $home ) ? untrailingslashit( $home ) : '';
	if ( $home && '/' !== $home && 0 === strpos( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	return trim( $path, '/' );
}

function vss_maintenance_customize( $wp_customize ) {
	$wp_customize->add_section(
		'vss_maintenance',
		array(
			'title'       => 'Maintenance Mode',
			'description' => 'While this is on, every public URL redirects to the maintenance page. Administrators can still browse the site. With the timer on, the site opens again at the end time.',
			'panel'       => 'vss_integrations',
			'priority'    => 70,
		)
	);

	$wp_customize->add_setting( 'vss_maintenance_enabled', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'vss_maintenance_enabled', array( 'label' => 'Enable maintenance mode', 'section' => 'vss_maintenance', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'vss_maintenance_headline', array( 'default' => "We'll be back soon.", 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'vss_maintenance_headline', array( 'label' => 'Headline', 'section' => 'vss_maintenance', 'type' => 'text' ) );

	$wp_customize->add_setting( 'vss_maintenance_message', array( 'default' => "We're making some updates. Check back shortly.", 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control( 'vss_maintenance_message', array( 'label' => 'Message', 'section' => 'vss_maintenance', 'type' => 'textarea' ) );

	$wp_customize->add_setting( 'vss_maintenance_timer_enabled', array( 'default' => false, 'sanitize_callback' => 'wp_validate_boolean' ) );
	$wp_customize->add_control( 'vss_maintenance_timer_enabled', array( 'label' => 'Enable countdown timer', 'description' => 'Shows a countdown and turns maintenance off when the end time is reached.', 'section' => 'vss_maintenance', 'type' => 'checkbox' ) );

	$wp_customize->add_setting( 'vss_maintenance_timer_end', array( 'default' => '', 'sanitize_callback' => 'vss_sanitize_maintenance_end' ) );
	$wp_customize->add_control(
		'vss_maintenance_timer_end',
		array(
			'label'           => 'Timer end',
			'description'     => 'Uses the site timezone. Leave empty to keep maintenance on until you turn it off.',
			'section'         => 'vss_maintenance',
			'type'            => 'datetime-local',
			'active_callback' => function () {
				return (bool) get_theme_mod( 'vss_maintenance_timer_enabled', false );
			},
		)
	);
}
add_action( 'customize_register', 'vss_maintenance_customize', 20 );

function vss_render_maintenance_page() {
	$end     = vss_maintenance_mod( 'timer_enabled', false ) ? vss_maintenance_end_timestamp() : 0;
	$retry   = ( $end && $end > time() ) ? ( $end - time() ) : 3600;
	$headline = vss_maintenance_mod( 'headline', "We'll be back soon." );
	$message  = vss_maintenance_mod( 'message', "We're making some updates. Check back shortly." );
	$home     = home_url( '/' );

	status_header( 503 );
	nocache_headers();
	header( 'Retry-After: ' . (int) $retry, true );
	header( 'X-Robots-Tag: noindex, nofollow', true );

	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> — Maintenance</title>
<style>
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 20px;background:#fbfaf7;color:#241c16;font-family:Manrope,Segoe UI,sans-serif;text-align:center}
main{max-width:560px}
h1{font-size:2rem;line-height:1.2;margin:0 0 12px}
p{margin:0;color:#6b5e52;font-size:1.05rem;line-height:1.6}
.timer{display:flex;gap:14px;justify-content:center;margin-top:28px}
.timer div{min-width:68px;background:#fff;border:1px solid #ece4d8;border-radius:12px;padding:12px 8px}
.timer strong{display:block;font-size:1.6rem;color:#f2600c}
.timer span{font-size:.72rem;letter-spacing:.06em;text-transform:uppercase;color:#6b5e52}
</style>
</head>
<body<?php echo $end ? ' data-end="' . esc_attr( $end ) . '" data-home="' . esc_url( $home ) . '"' : ''; ?>>
<main>
<h1><?php echo esc_html( $headline ); ?></h1>
<p><?php echo esc_html( $message ); ?></p>
<?php if ( $end ) : ?>
<div class="timer" aria-live="polite">
<div><strong data-d>0</strong><span>Days</span></div>
<div><strong data-h>0</strong><span>Hours</span></div>
<div><strong data-m>0</strong><span>Minutes</span></div>
<div><strong data-s>0</strong><span>Seconds</span></div>
</div>
<script>
(function(){
	var end = parseInt(document.body.getAttribute('data-end'), 10) * 1000;
	var home = document.body.getAttribute('data-home') || '/';
	var d = document.querySelector('[data-d]'), h = document.querySelector('[data-h]'), m = document.querySelector('[data-m]'), s = document.querySelector('[data-s]');
	function tick(){
		var left = Math.max(0, end - Date.now()), sec = Math.floor(left / 1000);
		d.textContent = String(Math.floor(sec / 86400));
		h.textContent = String(Math.floor((sec % 86400) / 3600));
		m.textContent = String(Math.floor((sec % 3600) / 60));
		s.textContent = String(sec % 60);
		if (left <= 0) { window.location.replace(home); }
	}
	tick();
	setInterval(tick, 1000);
})();
</script>
<?php endif; ?>
</main>
</body>
</html>
	<?php
	exit;
}

function vss_maintenance_guard() {
	$is_page = ( 'maintenance' === vss_maintenance_request_path() );

	if ( ! vss_maintenance_active() ) {
		if ( $is_page ) {
			wp_safe_redirect( home_url( '/' ) );
			exit;
		}
		return;
	}

	if ( $is_page ) {
		vss_render_maintenance_page();
	}

	if ( is_customize_preview() || current_user_can( 'manage_options' ) ) {
		return;
	}

	wp_safe_redirect( home_url( '/maintenance/' ), 302 );
	exit;
}
add_action( 'template_redirect', 'vss_maintenance_guard', 0 );

function vss_maintenance_block_rest( $result ) {
	if ( is_wp_error( $result ) || ! vss_maintenance_active() || current_user_can( 'manage_options' ) ) {
		return $result;
	}
	return new WP_Error( 'maintenance', 'Site is under maintenance.', array( 'status' => 503 ) );
}
add_filter( 'rest_authentication_errors', 'vss_maintenance_block_rest', 120 );

function vss_maintenance_block_ajax() {
	if ( ! wp_doing_ajax() || ! vss_maintenance_active() || current_user_can( 'manage_options' ) ) {
		return;
	}
	status_header( 503 );
	header( 'X-Robots-Tag: noindex, nofollow', true );
	wp_die( esc_html__( 'Site is under maintenance.', 'targetweb' ), '', array( 'response' => 503 ) );
}
add_action( 'admin_init', 'vss_maintenance_block_ajax' );
