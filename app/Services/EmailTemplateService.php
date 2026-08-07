<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/CorrespondenceEmailService.php';

class EmailTemplateService
{
    public function renderCorrespondenceNotification(array $data): array
    {
        $emailService = new CorrespondenceEmailService(null, false);
        $options = [
            'recipient_type' => trim((string)($data['recipient_type'] ?? 'recipient')) !== '' ? trim((string)$data['recipient_type']) : 'recipient',
            'portal_url' => trim((string)($data['portal_url'] ?? '')),
            'organization_name' => trim((string)($data['organization_name'] ?? 'DPEARP')),
            'brand_name' => trim((string)($data['brand_name'] ?? 'DPEARP Correspondence Management System')),
        ];

        return $emailService->renderEmail($data, $options);
    }

        /**
         * Render a Two-Factor Authentication OTP email.
         * Expects: ['otp' => '123456', 'valid_minutes' => 5, 'brand_name' => '', 'organization_name' => '']
         * Returns: ['subject' => string, 'html' => string, 'text' => string]
         */
        public function renderOtpEmail(array $data): array
        {
                $otp = isset($data['otp']) ? (string)$data['otp'] : '******';
                $validMinutes = isset($data['valid_minutes']) ? (int)$data['valid_minutes'] : 5;
                $brandName = htmlspecialchars(trim((string)($data['brand_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Organization';
                $organizationName = htmlspecialchars(trim((string)($data['organization_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: $brandName;
                $subject = 'Two-Factor Authentication';
                $year = (string)date('Y');

                $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
                $browser = htmlspecialchars(trim((string)($data['browser'] ?? '')), ENT_QUOTES, 'UTF-8');
                $operatingSystem = htmlspecialchars(trim((string)($data['operating_system'] ?? '')), ENT_QUOTES, 'UTF-8');
                $deviceName = htmlspecialchars(trim((string)($data['device_name'] ?? '')), ENT_QUOTES, 'UTF-8');
                $ipAddress = htmlspecialchars(trim((string)($data['ip_address'] ?? '')), ENT_QUOTES, 'UTF-8');
                $dateTime = htmlspecialchars(trim((string)($data['date_time'] ?? '')), ENT_QUOTES, 'UTF-8');
                $preheader = 'A sign-in attempt requires additional verification. Enter the verification code below to continue.';

                $loginInfoRows = '';
                if ($browser !== '') {
                        $loginInfoRows .= $this->renderInfoRow('Browser', $browser);
                }
                if ($operatingSystem !== '') {
                        $loginInfoRows .= $this->renderInfoRow('Operating System', $operatingSystem);
                }
                if ($deviceName !== '') {
                        $loginInfoRows .= $this->renderInfoRow('Device Name', $deviceName);
                }
                if ($ipAddress !== '') {
                        $loginInfoRows .= $this->renderInfoRow('IP Address', $ipAddress);
                }
                if ($dateTime !== '') {
                        $loginInfoRows .= $this->renderInfoRow('Date and Time', $dateTime);
                }

                $loginInfoHtml = '';
                if ($loginInfoRows !== '') {
                        $loginInfoHtml = <<<HTML
                <div style="background-color:#f8fafc;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px;margin:0 0 20px 0;">
                  <div style="margin-bottom:12px;font-size:15px;font-weight:700;color:#0f172a;">Login Information</div>
                  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse;">
                    {$loginInfoRows}
                  </table>
                </div>
HTML;
                }

                $html = <<<HTML
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{$subject}</title>
  <style>
    body { margin: 0; padding: 0; background-color: #f3f4f6; font-family: Segoe UI, Arial, sans-serif; color: #111827; }
    .preheader { display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; }
    .otp-code { font-family: Menlo, Monaco, Consolas, monospace; font-size: 40px; line-height: 48px; font-weight: 700; letter-spacing: 0.35em; color: #0f172a; }
    @media (max-width: 480px) { .otp-code { font-size: 34px !important; line-height: 42px !important; } }
  </style>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6;">
  <div class="preheader">{$preheader}</div>
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:640px; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
          <tr>
            <td style="padding:24px 24px 18px 24px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
              <div style="font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#64748b; margin-bottom:8px;">{$brandName}</div>
              <div style="font-size:22px; font-weight:700; color:#0f172a;">{$subject}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:24px;">
              <p style="margin:0 0 20px 0; font-size:15px; line-height:24px; color:#334155;">A sign-in attempt requires additional verification. Enter the verification code below to continue.</p>
              <div style="background-color:#ffffff; border:1px solid #e5e7eb; border-radius:16px; padding:24px 20px; margin:0 auto 16px auto; max-width:360px; text-align:center;">
                <p style="margin:0 0 12px 0; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:0.12em; color:#475569;">Verification Code</p>
                <div class="otp-code">{$safeOtp}</div>
                <p style="margin:16px 0 0 0; font-size:14px; color:#64748b;">Valid for {$validMinutes} minutes</p>
              </div>
              <div style="background-color:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:16px 18px; margin:0 0 20px 0;">
                <p style="margin:0 0 8px 0; font-size:15px; font-weight:700; color:#0f172a;">Security Notice</p>
                <p style="margin:0; font-size:14px; line-height:22px; color:#334155;">If you did not request this verification code, you can safely ignore this email. Your account cannot be accessed without this code.</p>
              </div>
              {$loginInfoHtml}
              <p style="margin:0; font-size:13px; line-height:20px; color:#64748b;">Please keep this code private and do not share it with anyone.</p>
            </td>
          </tr>
          <tr>
            <td style="padding:0 24px 24px 24px;">
              <div style="border-top:1px solid #e5e7eb; padding-top:16px; font-size:12px; line-height:18px; color:#64748b;">
                <p style="margin:0 0 4px 0;">This is an automated notification from {$organizationName}.</p>
                <p style="margin:0 0 4px 0;">Please do not reply to this email.</p>
                <p style="margin:0;">© {$year} {$organizationName}. All rights reserved.</p>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

                $textLines = [];
                $textLines[] = $brandName;
                $textLines[] = $subject;
                $textLines[] = '';
                $textLines[] = 'A sign-in attempt requires additional verification. Enter the verification code below to continue.';
                $textLines[] = '';
                $textLines[] = 'Verification code: ' . $otp;
                $textLines[] = '';
                $textLines[] = "Valid for {$validMinutes} minutes.";
                if ($browser !== '') {
                        $textLines[] = '';
                        $textLines[] = 'Browser: ' . $browser;
                }
                if ($operatingSystem !== '') {
                        $textLines[] = 'Operating System: ' . $operatingSystem;
                }
                if ($deviceName !== '') {
                        $textLines[] = 'Device Name: ' . $deviceName;
                }
                if ($ipAddress !== '') {
                        $textLines[] = 'IP Address: ' . $ipAddress;
                }
                if ($dateTime !== '') {
                        $textLines[] = 'Date and Time: ' . $dateTime;
                }
                $textLines[] = '';
                $textLines[] = 'If you did not request this verification code, you can safely ignore this email. Your account cannot be accessed without this code.';
                $textLines[] = '';
                $textLines[] = "This is an automated notification from {$organizationName}.";
                $textLines[] = 'Please do not reply to this email.';
                $textLines[] = "© {$year} {$organizationName}. All rights reserved.";

                $text = implode(PHP_EOL, $textLines);

                return [
                        'subject' => $subject,
                        'html' => $html,
                        'text' => $text,
                ];
        }

        private function renderInfoRow(string $label, string $value): string
        {
                return '<tr><td style="padding:6px 0 6px 0; font-size:14px; line-height:20px; color:#475569; width:130px; vertical-align:top;">' . $label . '</td><td style="padding:6px 0 6px 0; font-size:14px; line-height:20px; color:#111827;">' . $value . '</td></tr>';
        }
}
