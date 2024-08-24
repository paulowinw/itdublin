<?php
/**
 * Global AutomatePlug Functions.
 *
 * @package  Automateplug
 */

/**
 * Get or prepare user id.
 *
 * @return int|mixed|string|void
 */
function ap_get_current_user_id() {

	$user_id = get_current_user_id();

	if ( $user_id ) {
		return $user_id;
	}

	if ( ! session_id() ) { //phpcs:ignore
		session_start(); //phpcs:ignore
	}

	if ( isset( $_SESSION['ap_user_identifier'] ) ) {
		return $_SESSION['ap_user_identifier']; //phpcs:ignore
	}

	$ap_user_id                     = wp_generate_password( 16, false );
	$_SESSION['ap_user_identifier'] = $ap_user_id; //phpcs:ignore

	return $_SESSION['ap_user_identifier']; //phpcs:ignore

}

/**
 * Get or prepare user id.
 *
 * @param string $email user email.
 *
 * @return int|mixed|string|void
 */
function ap_get_user_id_from_email( $email ) {

	if ( empty( $email ) || ! email_exists( $email ) ) {
		return false;
	}

	$get_user = get_user_by( 'email', $email );
	return $get_user->ID;

}

add_action(
	'in_admin_header',
	function () {
		if ( isset( $_GET['page'] ) && 'suretriggers' === sanitize_text_field( $_GET['page'] ) ) { // phpcs:ignore
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}
	},
	999
);

add_action( 'wp_login', 'suretrigger_capture_login_time', 10, 2 );

/**
 * Login time.
 *
 * @param string $user_login user login.
 * @param object $user user.
 * @return void
 */
function suretrigger_capture_login_time( $user_login, $user ) {
	update_user_meta( $user->ID, 'st_last_login', time() );
}

/**
 * SureTrigger Trigger Button shortcode.
 *
 * @param array $atts Attributes.
 * @param null  $content Content.
 * @return string|bool
 */
function suretrigger_button( $atts, $content = null ) {
	$atts = shortcode_atts(
		[
			'id'                   => 0,
			'button_label'         => __( 'Click here', 'suretriggers' ),
			'user_redirect_url'    => '',
			'visitor_redirect_url' => '',
			'button_class'         => 'suretrigger_button',
			'button_id'            => 'suretrigger_button',
			'click_loading_label'  => __( 'Clicking...', 'suretriggers' ),
			'after_clicked_label'  => __( 'Clicked!!', 'suretriggers' ),
			'click_once'           => 'true',
			'cookie_duration'      => '15',
		],
		$atts,
		'trigger_button' 
	);
	ob_start();
	$user_id = get_current_user_id();
	?>

	<form method="post" class="suretrigger_button_form" id="suretrigger_button_form_<?php echo esc_attr( (string) $atts['id'] ); ?>">
		<input type="hidden" name="st_trigger_id" value="<?php echo esc_attr( (string) $atts['id'] ); ?>" />
		<input type="hidden" name="st_nonce" value="<?php echo esc_attr( wp_create_nonce( 'suretrigger_form' ) ); ?>"/>
		<input type="hidden" name="st_login_url" value="<?php echo esc_attr( $atts['user_redirect_url'] ); ?>"/>
		<input type="hidden" name="st_non_login_url" value="<?php echo esc_attr( $atts['visitor_redirect_url'] ); ?>"/>
		<input type="hidden" name="st_click" value="<?php echo esc_attr( $atts['click_once'] ); ?>"/>
		<input type="hidden" name="st_button_label" value="<?php echo esc_attr( $atts['button_label'] ); ?>"/>
		<input type="hidden" name="st_loading_label" value="<?php echo esc_attr( $atts['click_loading_label'] ); ?>"/>
		<input type="hidden" name="st_clicked_label" value="<?php echo esc_attr( $atts['after_clicked_label'] ); ?>"/>
		<input type="hidden" name="action" value="handle_trigger_button_click"/>
		<input type="hidden" name="st_cookie_duration" value="<?php echo esc_attr( $atts['cookie_duration'] ); ?>"/>
		<input type="hidden" name="st_user_id" value="<?php echo esc_attr( $user_id ); ?>"/>
		<?php
		global $post;
		if ( ! empty( $post ) && is_object( $post ) && isset( $post->ID ) && isset( $post->post_title ) ) {
			?>
			<input type="hidden" name="st_button_post_id" value="<?php echo esc_attr( $post->ID ); ?>"/>
			<input type="hidden" name="st_button_post_title" value="<?php echo esc_attr( $post->post_title ); ?>"/>
			<?php
		}
		$cookie_name = 'st_trigger_button_clicked_' . esc_attr( (string) $atts['id'] );
		if ( isset( $_COOKIE[ $cookie_name ] ) && 'yes_' . $user_id == $_COOKIE[ $cookie_name ] ) {
			?>
			<button type="button" class="<?php echo esc_attr( $atts['button_class'] ); ?>" id="<?php echo esc_attr( $atts['button_id'] ); ?>"><?php echo esc_html( $atts['after_clicked_label'] ); ?></button>
			<?php
		} else {
			?>
			<button type="button" class="<?php echo esc_attr( $atts['button_class'] ); ?>" id="<?php echo esc_attr( $atts['button_id'] ); ?>" onclick="st_trigger_ajax(this);return false;"><?php echo esc_html( $atts['button_label'] ); ?></button>
			<?php
		}
		?>
	</form>
	<script>
		function getCookie(cookieName) { 
			const regex = new RegExp(cookieName + '=([^;]+)'); 
			const cookieValue = document.cookie.match(regex); 
			return cookieValue ? cookieValue[1] : null; 
		}

		function st_trigger_ajax(element) {
			var button = element;
			button.disabled = true;
			var form = button.closest("form");
			var formData = new FormData(form);

			var inputTriggerId = button.parentNode.querySelector('input[name="st_trigger_id"]');
			var inputLoadingLabel = button.parentNode.querySelector('input[name="st_loading_label"]');
			var inputClickedLabel = button.parentNode.querySelector('input[name="st_clicked_label"]');
			var inputButtonLabel = button.parentNode.querySelector('input[name="st_button_label"]');
			var inputUserId = button.parentNode.querySelector('input[name="st_user_id"]');

			var cookiename = 'st_trigger_button_clicked_' + inputTriggerId.value;
			var cookie = 'yes_' + inputUserId.value;
			var cookieValue = getCookie(cookiename);

			if (cookieValue === null || cookieValue !== cookie) {
				button.classList.add('st_trigger_button_loading');
				if (inputLoadingLabel.value !== '') {
				button.textContent = inputLoadingLabel.value;
				}
				var xhr = new XMLHttpRequest();
				xhr.open('POST', '<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>');
				xhr.onreadystatechange = function() {
					if (xhr.readyState === XMLHttpRequest.DONE) {
						if (xhr.status === 200) {
							button.classList.remove('st_trigger_button_loading');
							button.disabled = false;
							if (inputClickedLabel.value !== '') {
								button.textContent = inputClickedLabel.value;
							} else {
								button.textContent = inputButtonLabel.value;
							}
							if( xhr.responseText != '' ){
								var response = JSON.parse(xhr.responseText);
								if (response.data) {
									location.href = response.data;
								}
							}
						}
					}
				};
				xhr.send(formData);
			} else {
				if (inputClickedLabel.value !== '') {
					button.textContent = inputClickedLabel.value;
				}
			}
		}
	</script>

	<?php
	return ob_get_clean();
}
add_shortcode( 'st_trigger_button', 'suretrigger_button' );

/**
 * SureTrigger Trigger Button custom style.
 *
 * @return void
 */
function suretrigger_button_custom_style() {
	wp_enqueue_style( 'st-trigger-button-style', SURE_TRIGGERS_URL . 'assets/css/st-trigger-button.css', [], SURE_TRIGGERS_VER );
}
add_action( 'wp_enqueue_scripts', 'suretrigger_button_custom_style' );

/**
 * SureTrigger Trigger Button action.
 * 
 * @return void
 */
function suretrigger_trigger_button_action() {

	// Trigger the custom hook before ajax response.
	do_action( 'st_trigger_button_before_click_hook' );

	if ( ! isset( $_POST['st_nonce'] ) && ! wp_verify_nonce( wp_strip_all_tags( $_POST['st_nonce'] ), 'suretrigger_form' ) ) {
		wp_send_json_error();
	}

	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();

		if ( isset( $_POST['st_trigger_id'] ) && ! empty( $_POST['st_trigger_id'] ) ) {

			$st_trigger_id = sanitize_text_field( $_POST['st_trigger_id'] );

			if ( isset( $_POST['st_cookie_duration'] ) || isset( $_POST['st_click'] ) ) {
				if ( isset( $_POST['st_button_post_id'] ) || isset( $_POST['st_button_post_title'] ) ) {
					$post_data = [
						'parent_post_id'    => sanitize_text_field( $_POST['st_button_post_id'] ),
						'parent_post_title' => sanitize_text_field( $_POST['st_button_post_title'] ),
					];
				} else {
					$post_data = [];
				}
				do_action( 'st_trigger_button_action', $st_trigger_id, $user_id, sanitize_text_field( $_POST['st_cookie_duration'] ), $_POST['st_click'], $post_data );
			}
			
			if ( isset( $_POST['st_login_url'] ) && ! empty( $_POST['st_login_url'] ) ) {
				wp_send_json_success( esc_url( $_POST['st_login_url'] ) );
			}
		}
	} else {
		if ( isset( $_POST['st_non_login_url'] ) && ! empty( $_POST['st_non_login_url'] ) ) {
			wp_send_json_success( esc_url( $_POST['st_non_login_url'] ) );
		} else {
			wp_send_json_success( wp_login_url() );
		}
	}

	// Trigger the custom hook after ajax response.
	do_action( 'st_trigger_button_after_click_hook' );

	wp_die();
}
add_action( 'wp_ajax_handle_trigger_button_click', 'suretrigger_trigger_button_action' );
add_action( 'wp_ajax_nopriv_handle_trigger_button_click', 'suretrigger_trigger_button_action' );

/**
 * SureTrigger Trigger Button set cookie.
 * 
 * @param int $st_trigger_id Trigger ID.
 * @param int $user_id User ID.
 * @param int $cookie_duration Cookie Duration.
 * 
 * @return void
 */
function st_trigger_button_set_cookie( $st_trigger_id, $user_id, $cookie_duration ) {
	// Set the cookie.
	$cookie_name  = 'st_trigger_button_clicked_' . $st_trigger_id;
	$cookie_value = 'yes_' . $user_id;
	if ( isset( $cookie_duration ) ) {
		$expiration = time() + 60 * 60 * 24 * intval( $cookie_duration ); // Set the expiration time as per user requested.
	} else {
		$expiration = time() + 60 * 60 * 24 * 15;
	}

	if ( ! defined( 'COOKIEPATH' ) ) {
		define( 'COOKIEPATH', '/' );
	}

	if ( ! defined( 'COOKIE_DOMAIN' ) ) {
		define( 'COOKIE_DOMAIN', false );
	}

	setcookie( $cookie_name, $cookie_value, $expiration, COOKIEPATH, COOKIE_DOMAIN );
}
add_action( 'st_trigger_button_set_cookie', 'st_trigger_button_set_cookie', 10, 3 );
