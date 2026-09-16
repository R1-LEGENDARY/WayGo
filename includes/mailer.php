<?php
require '../config/email.php';
require '../includes/PHPMailer/src/PHPMailer.php';
require '../includes/PHPMailer/src/SMTP.php';
require '../includes/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function email_status_chip($status) {
    $status = strtolower(trim((string)$status));
    $colors = array(
        'pending' => array('bg' => '#fff3cd', 'text' => '#8a4b00'),
        'confirmed' => array('bg' => '#d1fae5', 'text' => '#065f46'),
        'active' => array('bg' => '#d1fae5', 'text' => '#065f46'),
        'completed' => array('bg' => '#dbeafe', 'text' => '#1d4ed8'),
        'cancelled' => array('bg' => '#fee2e2', 'text' => '#991b1b'),
    );
    $color = isset($colors[$status]) ? $colors[$status] : array('bg' => '#f3f4f6', 'text' => '#374151');

    return '<span style="display:inline-block; background-color:' . $color['bg'] . '; color:' . $color['text'] . '; font-size:11px; font-weight:bold; letter-spacing:0.8px; padding:5px 10px; border-radius:999px; border:1px solid rgba(0,0,0,0.05);">' . htmlspecialchars(strtoupper($status)) . '</span>';
}

/**
 * DOCU: Sends a booking confirmation email without blocking the booking flow.
 * @param {string} $to_email - Recipient email address.
 * @param {string} $to_name - Recipient name.
 * @param {string} $vehicle_name - Booked vehicle name.
 * @param {string} $start_date - Booking start date.
 * @param {string} $end_date - Booking end date.
 * @param {float} $total_price - Booking total.
 * @returns {bool} - Whether the message was sent successfully.
 *
 * Author: JC
 */
function send_booking_confirmation_email($to_email, $to_name, $vehicle_name, $start_date, $end_date, $total_price) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $status_badge = email_status_chip('pending');

        $mail->isHTML(true);
        $mail->Subject = 'Your WayGo Booking Is Pending Confirmation';
        $mail->Body = "
            <p>Dear {$to_name},</p>
            <p>We've received your rental booking and it is currently awaiting confirmation from the WayGo team.</p>
            <p style='margin: 12px 0 16px;'><strong>STATUS:</strong> {$status_badge}</p>
            <p><strong>{$vehicle_name}</strong><br>
            Pickup: {$start_date}<br>
            Return: {$end_date}</p>
            <p>We'll notify you as soon as your booking has been reviewed.</p>
            <p>You can log in to your WayGo account anytime to check your booking status and details.</p>
            <p>Thank you for choosing WayGo!</p>
            <p>Regards,<br>WayGo Team</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Email failed to send: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * DOCU: Sends a notification when a booking status changes.
 * @param {string} $to_email - Recipient email address.
 * @param {string} $to_name - Recipient name.
 * @param {string} $vehicle_name - Booked vehicle name.
 * @param {string} $start_date - Booking start date.
 * @param {string} $end_date - Booking end date.
 * @param {string} $new_status - Updated booking status.
 * @returns {bool} - Whether the message was sent successfully.
 *
 * Author: JC
 */
function send_booking_status_email($to_email, $to_name, $vehicle_name, $start_date, $end_date, $new_status) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);

        $status_messages = array(
            'confirmed' => 'Good news! Your rental booking has been confirmed, and your vehicle is now reserved.',
            'active' => 'Your WayGo rental is now active.',
            'completed' => 'Your rental with WayGo has been successfully completed.',
            'cancelled' => 'Your WayGo rental booking has been cancelled.',
        );
        $status_message = isset($status_messages[$new_status]) ? $status_messages[$new_status] : "Your booking status has been updated to '{$new_status}'.";
        $status_badge = email_status_chip($new_status);

        $mail->isHTML(true);
        $mail->Subject = 'Your WayGo Booking Status Update';
        $mail->Body = "
            <p>Dear {$to_name},</p>
            <p>{$status_message}</p>
            <p style='margin: 12px 0 16px;'><strong>STATUS:</strong> {$status_badge}</p>
            ";

        if ($new_status === 'confirmed') {
            $mail->Body .= "
                <p><strong>Booking Details</strong></p>
                <p>- <strong>Vehicle:</strong> {$vehicle_name}<br>
                - <strong>Pickup Date:</strong> {$start_date}<br>
                - <strong>Return Date:</strong> {$end_date}</p>
                <p>Please keep these dates in mind and arrive at the designated pickup location on time.</p>
                <p>You can log in to your WayGo account to view your complete booking information.</p>
                <p>Thank you for choosing WayGo. We look forward to serving you!</p>
            ";
        } elseif ($new_status === 'active') {
            $mail->Body .= "
                <p>Your <strong>{$vehicle_name}</strong> is currently under your rental. Please take care of the vehicle and remember to return it according to your scheduled return time.</p>
                <p><strong>Return Date:</strong> {$end_date}</p>
                <p>You can view your complete rental details through your WayGo account.</p>
                <p>Thank you for choosing WayGo!</p>
            ";
        } elseif ($new_status === 'completed') {
            $mail->Body .= "
                <p>Thank you for renting the <strong>{$vehicle_name}</strong> with us. We hope you had a smooth and enjoyable rental experience.</p>
                <p>Your completed booking has been recorded in your WayGo account, where you can view your rental history and details anytime.</p>
                <p>We appreciate your business and hope to see you again!</p>
            ";
        } elseif ($new_status === 'cancelled') {
            $mail->Body .= "
                <p><strong>{$vehicle_name}</strong><br>
                Scheduled pickup: {$start_date}</p>
                <p>The booking is no longer active. You can log in to your WayGo account to view the cancellation and your booking history.</p>
                <p>If you believe this cancellation was made in error or need further assistance, please contact the WayGo team.</p>
                <p>Thank you for choosing WayGo.</p>
            ";
        } else {
            $mail->Body .= "
                <p><strong>Vehicle:</strong> {$vehicle_name}<br>
                <strong>Pickup Date:</strong> {$start_date}<br>
                <strong>Return Date:</strong> {$end_date}</p>
                <p>You can log in to your WayGo account anytime to view full booking details.</p>
            ";
        }

        $mail->Body .= "
            <p>Regards,<br>WayGo Team</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Status email failed to send: ' . $mail->ErrorInfo);
        return false;
    }
}