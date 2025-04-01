// custom-otp-login.js

jQuery(document).ready(function($) {
    $('#registerForm').on('submit', function(event) {
        event.preventDefault();
        var email = $('#registerEmail').val();
        $.post(customOtpVars.ajax_url, {
            action: 'custom_email_otp_register',
            email: email
        }, function(response) {
            $('#registerMessage').html(response);
        });
    });

    $('#otpRequestForm').on('submit', function(event) {
        event.preventDefault();
        var email = $('#otpEmail').val();
        $.post(customOtpVars.ajax_url, {
            action: 'custom_email_otp_send',
            email: email
        }, function(response) {
            $('#otpMessage').html(response);
            $('#otpRequestForm').hide();
            $('#otpVerifyForm').show();
        });
    });

$('#otpVerifyForm').on('submit', function(event) {
    event.preventDefault();
    var email = $('#otpEmail').val();
    var otp = $('#otpCode').val();

    $.post(customOtpVars.ajax_url, {
        action: 'custom_email_otp_verify_otp',
        email: email,
        otp: otp
    }, function(response) {
        try {
            var res = JSON.parse(response); // Parse the response to handle it
            if (res.success) {
                // If success, redirect to My Account
                window.location.href = res.redirect_url;
            } else {
                // If failed, show the error message
                alert(res.message); // You can replace this with a custom popup
            }
        } catch (e) {
            console.error('Error parsing the response:', e);
            alert('There was an issue with the request.');
        }
    }).fail(function() {
        alert('An error occurred while processing your request.');
    });
});







});
