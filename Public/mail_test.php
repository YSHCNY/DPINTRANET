<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/core/bootstrap.php';

use App\Services\MailService;

try {

    $mail = new MailService();

    $success = $mail->send(
        'testmail@philkoei.com.ph',
        'DP Intranet Mail Test',
        '
            <h2>Mail Test Successful</h2>

            <p>If you received this email, PHPMailer is correctly configured.</p>

            <hr>

            <p>Generated at: '.date('Y-m-d H:i:s').'</p>
        '
    );

    if ($success) {
        echo "<h2>✅ Email sent successfully.</h2>";
    } else {
        echo "<h2>❌ MailService returned false.</h2>";
    }

} catch (Throwable $e) {

    echo "<pre>";
    echo $e->getMessage();
    echo "</pre>";

}

