<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        $this->mailer->SMTPDebug = 0;
        $this->mailer->Debugoutput = 'error_log';
        $this->mailer->SMTPKeepAlive = true;

        /*
        |--------------------------------------------------------------------------
        | SMTP Configuration
        |--------------------------------------------------------------------------
        */

        $this->mailer->isSMTP();

        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;

        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];

        /*
        |--------------------------------------------------------------------------
        | Encryption
        |--------------------------------------------------------------------------
        */

        if ($_ENV['MAIL_ENCRYPTION'] === 'ssl') {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $this->mailer->Port = (int) $_ENV['MAIL_PORT'];

        /*
        |--------------------------------------------------------------------------
        | Message Defaults
        |--------------------------------------------------------------------------
        */

        $this->mailer->CharSet = 'UTF-8';
        $this->mailer->Encoding = 'base64';

        $this->mailer->isHTML(true);

        $this->mailer->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'],
            $_ENV['MAIL_FROM_NAME']
        );
    }

    public function send(
        string $recipient,
        string $subject,
        string $html,
        array $attachments = []
    ): bool {

        try {

            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            $this->mailer->addAddress($recipient);

            $this->mailer->Subject = $subject;
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags($html);

            foreach ($attachments as $attachment) {
                if (is_string($attachment) && $attachment !== '' && file_exists($attachment)) {
                    $this->mailer->addAttachment($attachment, basename($attachment));
                }
            }

            return $this->mailer->send();

        } catch (Exception $e) {

            error_log('Mail Error: ' . $this->mailer->ErrorInfo);

            return false;

        }

    }
}