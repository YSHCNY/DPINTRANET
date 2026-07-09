<?php

declare(strict_types=1);

namespace App\Services;

class EmailTemplateService
{
    private const DEFAULT_ORGANIZATION = 'DPEARP';
    private const DEFAULT_BRAND_NAME = 'DPEARP Correspondence Management System';

    public function renderCorrespondenceNotification(array $data): array
    {
        $trackingId = $this->escape($data['tracking_id'] ?? '');
        $title = $this->escape($data['title'] ?? 'Untitled correspondence');
        $description = $this->escape($this->truncateText($data['description'] ?? '', 220));
        $dueDate = $this->escape($data['due_date'] ?? '');
        $senderName = $this->escape($data['sender_name'] ?? $data['sender_email'] ?? '');
        $senderEmail = $this->escape($data['sender_email'] ?? '');
        $circulatedBy = $this->escape($data['circulated_by'] ?? '');
        $recipientType = $this->escape($data['recipient_type'] ?? 'recipient');
        $portalAccessNote = !empty($data['portal_access_note']);
        $portalUrl = isset($data['portal_url']) && is_string($data['portal_url']) && $data['portal_url'] !== ''
            ? $this->escape($data['portal_url'])
            : '';
        $organizationName = $this->escape($data['organization_name'] ?? self::DEFAULT_ORGANIZATION);
        $brandName = $this->escape($data['brand_name'] ?? self::DEFAULT_BRAND_NAME);
        $year = (string)date('Y');
        $subject = $trackingId !== ''
            ? sprintf('[%s] Correspondence Notification', $trackingId)
            : 'Correspondence Notification';
        $heading = $recipientType === 'cc'
            ? 'Correspondence Notification'
            : 'New Correspondence Assigned';
        $intro = $recipientType === 'cc'
            ? 'A correspondence update has been shared with you for review.'
            : 'You have been assigned a correspondence item that requires your attention.';

        $html = $this->renderHtmlTemplate(
            $subject,
            $brandName,
            $heading,
            $intro,
            $trackingId,
            $title,
            $description,
            $dueDate,
            $senderName,
            $senderEmail,
            $circulatedBy,
            $portalAccessNote,
            $portalUrl,
            $organizationName,
            $year
        );

        $text = $this->renderTextTemplate(
            $subject,
            $brandName,
            $heading,
            $intro,
            $trackingId,
            $title,
            $description,
            $dueDate,
            $senderName,
            $senderEmail,
            $circulatedBy,
            $portalAccessNote,
            $portalUrl,
            $organizationName,
            $year
        );

        return [
            'subject' => $subject,
            'html' => $html,
            'text' => $text,
        ];
    }

    private function renderHtmlTemplate(
        string $subject,
        string $brandName,
        string $heading,
        string $intro,
        string $trackingId,
        string $title,
        string $description,
        string $dueDate,
        string $senderName,
        string $senderEmail,
        string $circulatedBy,
        bool $portalAccessNote,
        string $portalUrl,
        string $organizationName,
        string $year
    ): string {
        $detailRows = [];
        $detailRows[] = $this->renderInfoRow('Tracking ID', $trackingId !== '' ? $trackingId : 'Not available');
        $detailRows[] = $this->renderInfoRow('Correspondence Title', $title !== '' ? $title : 'Untitled correspondence');
        $descriptionValue = $description !== '' ? $description : 'No description provided.';
        $descriptionHtml = $this->formatMultilineHtml($descriptionValue);
        $detailRows[] = $this->renderInfoRow('Description', $descriptionHtml);
        $detailRows[] = $this->renderInfoRow('Due Date', $dueDate !== '' ? $dueDate : 'Not specified');
        $senderDisplay = $senderName !== '' ? $senderName : ($senderEmail !== '' ? $senderEmail : 'Not available');
        $detailRows[] = $this->renderInfoRow('Sender', $senderDisplay);
        $detailRows[] = $this->renderInfoRow('Circulated By', $circulatedBy !== '' ? $circulatedBy : 'Not available');

        $portalText = $portalAccessNote
            ? 'If you have an account on the intranet, please log in to view the full correspondence details and related actions.'
            : 'If you have an account on the intranet, please log in to view the full correspondence details and related actions.';

        return <<<HTML
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title>{$subject}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Segoe UI, Arial, sans-serif; color:#111827;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">{$subject}</div>
  <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:24px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:640px; background-color:#ffffff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;">
          <tr>
            <td style="padding:24px 24px 18px 24px; border-bottom:1px solid #e5e7eb; background-color:#f8fafc;">
              <div style="font-size:12px; letter-spacing:1.5px; text-transform:uppercase; color:#64748b; margin-bottom:8px;">{$brandName}</div>
              <div style="font-size:22px; font-weight:700; color:#0f172a;">{$heading}</div>
            </td>
          </tr>
          <tr>
            <td style="padding:24px;">
              <p style="margin:0 0 12px 0; font-size:15px; line-height:24px; color:#334155;">{$intro}</p>
              <div style="background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:16px 18px; margin:0 0 16px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                  <tr>
                    <td colspan="2" style="padding-bottom:10px; font-size:13px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.04em;">Correspondence Details</td>
                  </tr>
                  {$this->implodeRows($detailRows)}
                </table>
              </div>
              <div style="background:linear-gradient(90deg, #eff6ff 0%, #f8fbff 100%); border:1px solid #bfdbfe; border-left:4px solid #2563eb; border-radius:10px; padding:16px 18px; margin:0 0 16px 0;">
                <p style="margin:0 0 6px 0; font-size:15px; font-weight:700; color:#0f172a;">Access Your Correspondence to see file</p>
                <p style="margin:0 0 10px 0; font-size:14px; line-height:22px; color:#334155;">If you have an account on the organization's Intranet or Correspondence Management System, you can log in to view the correspondence, track its status, and access all associated documents and updates.</p>
                <div style="display:inline-block; padding:8px 12px; border:1px solid #93c5fd; border-radius:999px; background-color:#ffffff; font-size:13px; font-weight:600; color:#1d4ed8;">Log In to the Intranet</div>
              </div>
              <p style="margin:0; font-size:13px; line-height:21px; color:#64748b;">Please review the correspondence at your earliest convenience.</p>
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
    }

    private function renderTextTemplate(
        string $subject,
        string $brandName,
        string $heading,
        string $intro,
        string $trackingId,
        string $title,
        string $description,
        string $dueDate,
        string $senderName,
        string $senderEmail,
        string $circulatedBy,
        bool $portalAccessNote,
        string $portalUrl,
        string $organizationName,
        string $year
    ): string {
        $lines = [];
        $lines[] = $brandName;
        $lines[] = $heading;
        $lines[] = '';
        $lines[] = $intro;
        $lines[] = '';
        $lines[] = 'Correspondence Details';
        $lines[] = 'Tracking ID: ' . ($trackingId !== '' ? $trackingId : 'Not available');
        $lines[] = 'Title: ' . ($title !== '' ? $title : 'Untitled correspondence');
        $lines[] = 'Description: ' . ($description !== '' ? $this->normalizePlainText($description) : 'No description provided.');
        $lines[] = 'Due Date: ' . ($dueDate !== '' ? $dueDate : 'Not specified');
        $lines[] = 'Sender: ' . ($senderName !== '' ? $senderName : ($senderEmail !== '' ? $senderEmail : 'Not available'));
        $lines[] = 'Circulated By: ' . ($circulatedBy !== '' ? $circulatedBy : 'Not available');
        $lines[] = '';
        $lines[] = 'Access Your Correspondence';
        $lines[] = 'If you have an account on the organization\'s Intranet or Correspondence Management System, you can log in to view the correspondence, track its status, and access all associated documents and updates.';
        if ($portalAccessNote) {
            $lines[] = 'If you are an internal recipient, please sign in to the intranet to review the record.';
        }
        if ($portalUrl !== '') {
            $lines[] = 'Portal: ' . $portalUrl;
        }
        $lines[] = '';
        $lines[] = 'This is an automated notification from ' . $organizationName . '.';
        $lines[] = 'Please do not reply to this email.';
        $lines[] = '© ' . $year . ' ' . $organizationName . '. All rights reserved.';

        return implode(PHP_EOL, $lines);
    }

    private function renderInfoRow(string $label, string $value): string
    {
        return '<tr><td style="padding:6px 0; font-size:14px; color:#64748b; width:35%; vertical-align:top;">' . $label . '</td><td style="padding:6px 0; font-size:14px; color:#0f172a; font-weight:600; vertical-align:top;">' . $value . '</td></tr>';
    }

    private function formatMultilineHtml(string $value): string
    {
        return nl2br($this->escape($value), false);
    }

    private function normalizePlainText(string $value): string
    {
        return preg_replace('/\r\n|\r|\n/', PHP_EOL, trim($value)) ?? trim($value);
    }

    private function implodeRows(array $rows): string
    {
        return implode('', $rows);
    }

    private function truncateText(string $value, int $limit): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 3)) . '...';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
