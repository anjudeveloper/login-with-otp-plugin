<?php
/*
Plugin Name: Custom Email OTP Login
Plugin URI: https://nightcoders.com/
Description: A custom plugin for user registration and login with email verification and OTP-based authentication.
Version: 1.0
Author: Anju Batta
Author URI: https://nightcoders.com/

*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

register_activation_hook(__FILE__, 'custom_email_otp_install');
function custom_email_otp_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'email_verification';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        otp varchar(6) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Enqueue the custom jQuery file
function enqueue_custom_otp_script() {
    wp_enqueue_script('custom-otp-login', plugin_dir_url(__FILE__) . 'custom-otp-login.js', array('jquery'), null, true);

    // Localize the script with AJAX URL
    wp_localize_script('custom-otp-login', 'customOtpVars', array(
        'ajax_url' => admin_url('admin-ajax.php') // This will point to admin-ajax.php
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_otp_script');

// Register form shortcode
function custom_email_otp_register_form_shortcode() {
    if (is_user_logged_in()) return '<p>You are already logged in.</p>';
    ob_start();
    ?>
	<h2>
		Register
</h2>
    <form id="registerForm" method="post">
		 <label for="username">Enter Your Email Address&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text">Required</span></label>
        <input type="email" id="registerEmail" name="email" placeholder="Enter your email" required>
		 <p>A verification link will be sent to your email address to confirm your account.</p>
		 <p class="form-row terms">
        <label for="terms">
            <input type="checkbox" class="input-checkbox" name="terms" id="terms" value="1" required="required"> 
            I have read and agree to the <a href="#" id="terms-link">Terms and Conditions</a>        </label>
    </p>
        <input type="submit" class="woocommerce-button button account_btn" value="Register">
    </form>
    <div id="registerMessage"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_register_form', 'custom_email_otp_register_form_shortcode');

// OTP login form shortcode
function custom_email_otp_login_form_shortcode() {
    if (is_user_logged_in()) return '<p>You are already logged in.</p>';
    ob_start();
    ?>
<h2>
	Login
</h2>
<div class="userlogin-form">
	 <form id="otpRequestForm" method="post">
		  <label for="username">Username or email address&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text">Required</span></label>
        <input type="email" id="otpEmail" name="email" placeholder="Enter your email" required>
        <input type="submit" class="woocommerce-button button account_btn" value="Send OTP">
    </form>
    <form id="otpVerifyForm" method="post" style="display:none;">
		 <label for="username">Enter OTP&nbsp;<span class="required" aria-hidden="true">*</span><span class="screen-reader-text">Required</span></label>
        <input type="text" id="otpCode" name="otp" placeholder="Enter OTP" required>
        <input type="submit" class="woocommerce-button button account_btn" value="Login">
    </form>
    <div id="otpMessage"></div>
</div>
   
    <?php
    return ob_get_clean();
}
add_shortcode('custom_login_form', 'custom_email_otp_login_form_shortcode');


// Remove WooCommerce Login form
remove_action( 'woocommerce_account_login_form', 'woocommerce_login_form' );

// Add Custom OTP login form using shortcode
add_action( 'woocommerce_account_login_form', 'add_custom_otp_login_form', 10 );

function add_custom_otp_login_form() {
    if ( is_user_logged_in() ) {
        // If the user is logged in, display WooCommerce My Account page
        return do_shortcode('[woocommerce_my_account]');
    } else {
        echo '<div class="login_register_form">';
        echo '<div class="row"><div class="col-sm-6">';
        echo do_shortcode('[custom_login_form]'); // This will display your custom login form
        echo '</div><div class="col-sm-6">'; // Fix the typo here with the missing '>'
        echo do_shortcode('[custom_register_form]'); // This will display your custom register form
        echo '</div></div></div>';
    }
}
add_shortcode('custom_otp_login_form', 'add_custom_otp_login_form');

// AJAX actions for email registration, OTP sending, and OTP verification
add_action('wp_ajax_custom_email_otp_register', 'custom_email_otp_register');
add_action('wp_ajax_nopriv_custom_email_otp_register', 'custom_email_otp_register');
function custom_email_otp_register() {
    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);
        $password = wp_generate_password();
        $user_id = wp_create_user($email, $password, $email);

        if (!is_wp_error($user_id)) {
            $verification_code = wp_generate_password(20, false);
            update_user_meta($user_id, 'email_verification_code', $verification_code);
            update_user_meta($user_id, 'email_verified', 'no');

            $verify_link = site_url('/verification/?code=' . $verification_code . '&user_id=' . $user_id);
        $subject = "Verify Your Email Address";
            $message = "
                <p>Hi {$user->user_login},</p>
                <p>Thank you for signing up! Please verify your email address by clicking the link below:</p>
                <p><a href='{$verify_link}' style='color:blue'>Verify Email</a></p>
                <p>This link will expire in 24 hours. If you didn’t create this account, please ignore this email.</p>
                <p>Welcome aboard,</p>
                <p><strong>APC</strong></p>
            ";
            
            // Send OTP email with formatted content
            $headers = array('Content-Type: text/html; charset=UTF-8');
            wp_mail($email, $subject, $message, $headers);

            echo 'Registration successful. Check your email for verification link.';
        } else {
            echo 'Error: ' . $user_id->get_error_message();
        }
    }
    wp_die();
}

add_action('wp_ajax_custom_email_otp_send', 'custom_email_otp_send'); 
add_action('wp_ajax_nopriv_custom_email_otp_send', 'custom_email_otp_send'); 
function custom_email_otp_send() {
    if (isset($_POST['email'])) {
        $email = sanitize_email($_POST['email']);

        // Check if the email is valid
        if (is_email($email)) {
            $user = get_user_by('email', $email);
        
        if ($user) {
            // Check if the email is verified
            $is_verified = get_user_meta($user->ID, 'email_verified', true);
            
            // If email is not verified, return an error
            if ($is_verified !== 'yes') {
                echo 'Your email is not verified yet. Please verify your email first.';
                wp_die();
            }
        }
            // Generate OTP and send email
            $otp = wp_generate_password(6, false);

            // Get the user by email
            $user = get_user_by('email', $email);

            if ($user) {
                // Store OTP in user meta
                update_user_meta($user->ID, 'otp', $otp);

                // Send OTP to email
                $subject = "Your OTP Code";
                $message = "Your OTP code is: $otp";
                wp_mail($email, $subject, $message);

                // Return success message
                echo "OTP has been sent to your email.";
            } else {
                echo "No user found with that email address.";
            }
        } else {
            echo "Invalid email address.";
        }
    } else {
        echo "Email is required.";
    }

    wp_die(); // Always call this after handling an AJAX request
}

add_action('wp_ajax_custom_email_otp_verify_otp', 'custom_email_otp_verify_otp');
add_action('wp_ajax_nopriv_custom_email_otp_verify_otp', 'custom_email_otp_verify_otp');

function custom_email_otp_verify_otp() {
    if (isset($_POST['email']) && isset($_POST['otp'])) {
        $email = sanitize_email($_POST['email']);
        $otp = sanitize_text_field($_POST['otp']);
        
        // Get user by email
        $user = get_user_by('email', $email);
        
        if ($user) {
            // Check if the email is verified
            $is_verified = get_user_meta($user->ID, 'email_verified', true);
            
            // If email is not verified, return an error
            if ($is_verified !== 'yes') {
                echo json_encode([
                    'success' => false,
                    'message' => 'Your email is not verified yet. Please verify your email first.'
                ]);
                wp_die();
            }

            // Get the stored OTP from user meta
            $stored_otp = get_user_meta($user->ID, 'otp', true);
            
            if ($stored_otp && $stored_otp === $otp) {
                // OTP is correct, log the user in
                wp_set_auth_cookie($user->ID);
                
                // Clear OTP after successful verification
                delete_user_meta($user->ID, 'otp');
                
                // Return a success message and redirect URL
                echo json_encode([
                    'success' => true,
                    'message' => 'OTP verified successfully. You are now logged in.',
                    'redirect_url' => site_url('/my-account/')
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid OTP.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No user found with that email address.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Email and OTP are required.'
        ]);
    }
    
    wp_die(); // Terminate the request after processing
}

// Create shortcode for email verification popup and redirection
function custom_email_verification_shortcode($atts) {
    ob_start(); // Start output buffering to capture HTML

    if (isset($_GET['code']) && isset($_GET['user_id'])) {
        $verification_token = sanitize_text_field($_GET['code']);
        $user_id = intval($_GET['user_id']);

        // Check if the user exists and the token is valid
        $user = get_user_by('ID', $user_id);
        $stored_token = get_user_meta($user_id, 'email_verification_code', true);

        // If the token matches, log the user in and display the success popup
        if ($user && $stored_token === $verification_token) {
            // Update email verification status
            update_user_meta($user_id, 'email_verified', 'yes');
            update_user_meta($user_id, 'email_verification_code', '');  // Clear the token after successful verification

             // Set cookies with the correct domain and path
            wp_set_auth_cookie($user_id, true, is_ssl());

            // Success popup and redirection script
            ?>
            <div id="verificationPopup" style="display:block;">
                <div id="popupContent" style="background-color: #d4edda; color: #155724; padding: 20px;">
                    <h2>Email Verified Successfully</h2>
                    <p>You have been automatically logged in.</p>
                    <a href="<?php echo site_url('/my-account'); ?>">Go to Dashboard</a>
                </div>
            </div>
            <script type="text/javascript">
                // Redirect the user to the 'My Account' page after 3 seconds
                setTimeout(function() {
                    window.location.href = "<?php echo site_url('/my-account'); ?>";
                }, 3000); // 3 seconds delay before redirect
            </script>
            <?php
        } else {
            // Token mismatch or invalid link, display error popup
            ?>
            <div id="verificationPopup" style="display:block;">
                <div id="popupContent" style="background-color: #f8d7da; color: #721c24; padding: 20px;">
                    <h2 style="color: red;">Invalid Verification Link</h2>
                    <p>The link you followed is invalid. Please check your email for the correct link or try again.</p>
                    <a href="<?php echo site_url(); ?>">Go to Home</a>
                </div>
            </div>
            <script type="text/javascript">
                // Redirect the user to the home page after 5 seconds
                setTimeout(function() {
                    window.location.href = "<?php echo site_url(); ?>";
                }, 5000); // 5 seconds delay before redirect
            </script>
            <?php
        }
    } else {
        // No code or user_id parameter, show message or handle differently
        ?>
        <div id="verificationPopup" style="display:none;">
            <div id="popupContent">
                <p>No verification link found. Please check your email for the correct link.</p>
            </div>
        </div>
        <?php
    }

    return ob_get_clean(); // Return the captured HTML
}

// Register the shortcode [email_verification_popup]
add_shortcode('email_verification_popup', 'custom_email_verification_shortcode');

// Add custom column to user list in dashboard
function add_email_verified_column($columns) {
    $columns['email_verified'] = 'Email Verified'; // Add the new column
    return $columns;
}
add_filter('manage_users_columns', 'add_email_verified_column');

// Display the email verified status in the custom column
function display_email_verified_column($value, $column_name, $user_id) {
    if ('email_verified' == $column_name) {
        // Check if the user email is verified by checking the user meta
        $email_verified = get_user_meta($user_id, 'email_verified', true);
        // Return the email verification status
        return ($email_verified == 'yes') ? 'Yes' : 'No';
    }
    return $value;
}
add_action('manage_users_custom_column', 'display_email_verified_column', 10, 3);

// Make the column sortable (optional)
function make_email_verified_column_sortable($columns) {
    $columns['email_verified'] = 'email_verified';
    return $columns;
}
add_filter('manage_edit-users_sortable_columns', 'make_email_verified_column_sortable');
