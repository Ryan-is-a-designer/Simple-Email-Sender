<?php
/*
Plugin Name: Simple Email Sender
Description: A simple plugin to send emails from the WordPress admin.
Version: 0.2
Author: Ryan
*/

function ses_enqueue_admin_styles() {
    wp_enqueue_style('ses-admin-styles', plugin_dir_url(__FILE__) . 'css/admin-styles.css');
}
add_action('admin_enqueue_scripts', 'ses_enqueue_admin_styles');

function ses_create_menu() {
    add_menu_page('Simple Email Sender', 'Email Sender', 'manage_options', 'simple-email-sender', 'ses_email_sender_page', 'dashicons-email-alt');
}
add_action('admin_menu', 'ses_create_menu');

function ses_email_sender_page() {
    ob_start(); // Start output buffering

    // Handle form submission and deletion before any output
    ses_handle_form_submission();
    ses_handle_delete_email();

    // Output the page content
    ?>
    <div class="ses-container">
        <div class="ses-email-form">
            <h1>Simple Email Sender</h1>
            <p>The plugin uses your website's SMTP settings to send out emails.</p>
            <form method="post" action="">
                <label for="ses_email">Email</label>
                <input type="email" id="ses_email" name="ses_email" required>
                <label for="ses_subject">Subject</label>
                <input type="text" id="ses_subject" name="ses_subject" required>
                <label for="ses_message">Message</label>
                <textarea id="ses_message" name="ses_message" required></textarea>
                <button type="submit" name="ses_send_email">Send email</button>
            </form>
            <p class="ses-footer">Hope you found this open-source plugin useful.<br>Want to help with plugin development?</p>
        </div>
        <div class="ses-recent-emails">
            <h1>Recent emails</h1>
            <ul>
                <?php
                $recent_emails = get_option('ses_recent_emails', []);
                foreach ($recent_emails as $index => $email) {
                    echo '<li>' . esc_html($email) . ' <a href="?page=simple-email-sender&ses_delete_email=' . $index . '" class="ses-delete-email">X</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
    <?php

    ob_end_flush(); // End output buffering and send output
}

function ses_handle_form_submission() {
    if (isset($_POST['ses_send_email'])) {
        $to = sanitize_email($_POST['ses_email']);
        $subject = sanitize_text_field($_POST['ses_subject']);
        $message = sanitize_textarea_field($_POST['ses_message']);
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($to, $subject, $message, $headers)) {
            echo '<div class="notice notice-success is-dismissible"><p>Email sent successfully!</p></div>';
            ses_save_recent_email($to);
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Failed to send email.</p></div>';
        }
    }
}

function ses_save_recent_email($email) {
    $recent_emails = get_option('ses_recent_emails', []);
    if (count($recent_emails) >= 12) {
        array_shift($recent_emails);
    }
    $recent_emails[] = $email;
    update_option('ses_recent_emails', $recent_emails);
}

function ses_handle_delete_email() {
    if (isset($_GET['ses_delete_email'])) {
        $index = intval($_GET['ses_delete_email']);
        $recent_emails = get_option('ses_recent_emails', []);
        if (isset($recent_emails[$index])) {
            unset($recent_emails[$index]);
            $recent_emails = array_values($recent_emails);
            update_option('ses_recent_emails', $recent_emails);
        }
        // Redirect to the plugin dashboard
        wp_safe_redirect(admin_url('admin.php?page=simple-email-sender'));
        exit;
    }
}
?>
