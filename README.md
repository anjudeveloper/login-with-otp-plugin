=== Plugin Name: Custom Email OTP Verification ===
Contributors: [Your Name], [Other Contributors (if any)]
Tags: otp, email verification, custom login, user authentication, security
Requires at least: 5.0
Tested up to: 6.0
Stable tag: trunk
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: [Your donation link (if applicable)]

== Description ==

Custom Email OTP Verification allows you to add a secure OTP-based verification process to your WordPress login flow. This plugin enables users to verify their email addresses by entering a One-Time Password (OTP) sent to their email. Upon successful verification, users will be logged in and redirected to the "My Account" page or a custom page of your choice.

This plugin also provides an option for users to trigger the verification process through a shortcode that can be embedded into a page. It integrates seamlessly with WooCommerce, allowing you to handle both the OTP verification and email status checks, ensuring a secure and seamless experience for your users.

### Features:
- OTP-based email verification for user login
- Customizable login form with OTP integration
- Redirect users to a custom page after OTP verification
- Option to display "Email Verified" status in the user list in the admin dashboard
- Shortcode-based implementation for easy integration
- Compatibility with WooCommerce and other popular plugins
- Error handling for invalid or expired verification links
- Works with default WordPress login as well as WooCommerce login

== Installation ==

1. Upload the `custom-email-otp-verification` plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use the provided shortcodes `[custom_otp_login_form]` to integrate OTP login form on your pages.
4. Users will now be prompted for OTP verification during login, and the system will handle successful and failed verification responses.
5. Optional: Add a custom field to the User list in the Admin Dashboard to display the email verification status.

== Frequently Asked Questions ==

= How does OTP verification work? =
After the user enters their email, an OTP will be sent to their email address. The user needs to input this OTP to successfully log in. If the user has not verified their email, they will be prompted to do so before proceeding with the login.

= Can I customize the OTP login form? =
Yes, you can customize the form by using the `custom_otp_login_form` shortcode in any post or page. You can also edit the form HTML and style as needed.

= How can I check if a user has verified their email? =
You can view the "Email Verified" status directly in the Users section of your WordPress dashboard. A "Yes" or "No" will appear for each user, indicating whether their email address has been successfully verified.

== Changelog ==

= 1.0 =
- Initial release with OTP verification for login.
- Option to display "Email Verified" status in the Users list.
- Redirect users to custom page after successful OTP verification.

== Upgrade Notice ==

= 1.0 =
This is the first release of the plugin.
