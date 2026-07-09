<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/correspondence.php";
require_once '../app/models/UserModel.php';
require_once '../app/config.php';
require_once '../app/Services/CorrespondenceEmailService.php';
require_once '../app/Services/CorrespondenceService.php';
require_once '../app/Services/EmailProgressService.php';

use App\Services\CorrespondenceEmailService;
use App\Services\CorrespondenceService;
use App\Services\EmailProgressService;

class CorrespondenceController extends Controller {

    private $model;
    private CorrespondenceService $correspondenceService;
    private EmailProgressService $emailProgressService;
    private const MAX_ATTACHMENT_FILES = 4;
    private const MAX_ATTACHMENT_BYTES = 41943040; // 40 MB

    public function __construct() {
        $this->model = new CorrespondenceModel();
        $this->correspondenceService = new CorrespondenceService();
        $this->emailProgressService = new EmailProgressService();
    }

    /**
     * Main page - Form + Document Repository
     */


    public function correspondence() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userModel = new UserModel();
        $nextTrackingId = $this->model->getNextTrackingId();
        $documents = $this->model->getAllDocuments(true);
        $users     = $userModel->getAllUsers();           // For Recipients & CC
        $showRemovedItems = $this->getRemovedItemsPreference();
        $draftsCount = count(array_filter($documents, function ($doc) {
            $status = strtolower(trim((string)($doc['status'] ?? '')));
            return !empty($doc['is_draft']) || $status === 'draft';
        }));
        $removedCount = count(array_filter($documents, function ($doc) {
            return !empty($doc['is_deleted']);
        }));

        $content = $this->renderView('correspondence/index', [
            'documents' => $documents,
            'users'     => $users,
            'nextTrackingId' => $nextTrackingId,
            'showRemovedItems' => $showRemovedItems,
            'draftsCount' => $draftsCount,
            'removedCount' => $removedCount,
            'canCreateCorrespondence' => $this->canCreateCorrespondence(),
            'canEditCorrespondence' => $this->canEditCorrespondence(),
            'canDeleteCorrespondence' => $this->canDeleteCorrespondence(),
            'canHardDeleteCorrespondence' => $this->canHardDeleteCorrespondence(),
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    /**
     * New circulation page (form moved off index)
     */
    public function newCirculation() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userModel = new UserModel();
        $nextTrackingId = $this->model->getNextTrackingId();
        $users     = $userModel->getAllUsers();

        $draftDocument = null;
        $draftRecipients = [];
        $draftCc = [];
        $draftAttachments = [];

        // If a draftId is provided, load the draft and prepare recipient/cc lists
        $draftId = (int)($_GET['draftId'] ?? 0);
        if ($draftId > 0) {
            $doc = $this->model->getById($draftId);
            if ($doc) {
                $draftDocument = $doc;
                try {
                    require_once __DIR__ . '/../models/UserModel.php';
                    $userModel = new UserModel();
                    $draftRecipientsRaw = $this->normalizeRecipientValues($doc['draft_recipients'] ?? '');
                    $draftCcRaw = $this->normalizeRecipientValues($doc['draft_cc'] ?? '');

                    foreach ($draftRecipientsRaw as $entry) {
                        if (preg_match('/^\d+$/', (string)$entry)) {
                            $user = $userModel->getUserById((int)$entry);
                            $draftRecipients[] = [
                                'kind' => 'user',
                                'id' => (int)$entry,
                                'name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? (string)$entry),
                                'email' => $user['email'] ?? null,
                            ];
                        } else {
                            $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $draftRecipients[] = [
                                    'kind' => 'custom',
                                    'id' => 0,
                                    'name' => $email,
                                    'email' => $email,
                                    'value' => 'email:' . strtolower($email),
                                ];
                            }
                        }
                    }

                    foreach ($draftCcRaw as $entry) {
                        if (preg_match('/^\d+$/', (string)$entry)) {
                            $user = $userModel->getUserById((int)$entry);
                            $draftCc[] = [
                                'kind' => 'user',
                                'id' => (int)$entry,
                                'name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? (string)$entry),
                                'email' => $user['email'] ?? null,
                            ];
                        } else {
                            $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $draftCc[] = [
                                    'kind' => 'custom',
                                    'id' => 0,
                                    'name' => $email,
                                    'email' => $email,
                                    'value' => 'email:' . strtolower($email),
                                ];
                            }
                        }
                    }
                } catch (Throwable $e) {
                    // ignore
                }

                // attachments
                $attachments = $this->model->getAttachments($draftId);
                foreach ($attachments as $a) {
                    $draftAttachments[] = [
                        'id' => (int)($a['id'] ?? 0),
                        'file_name' => $a['file_name'] ?? ($a['file'] ?? 'file'),
                        'size' => (int)($a['file_size'] ?? 0),
                        'download_url' => "index.php?controller=correspondence&action=download&attachment_id=" . (int)($a['id'] ?? 0)
                    ];
                }
            }
        }

        $isFinalizeMode = !empty($_GET['fromFinalize']);

        $content = $this->renderView('correspondence/new', [
            'users' => $users,
            'nextTrackingId' => $nextTrackingId,
            'canCreateCorrespondence' => $this->canCreateCorrespondence(),
            'canEditCorrespondence' => $this->canEditCorrespondence(),
            'isFinalizeMode' => $isFinalizeMode,
            'draftDocument' => $draftDocument,
            'draftRecipients' => $draftRecipients,
            'draftCc' => $draftCc,
            'draftAttachments' => $draftAttachments,
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    private function normalizeRecipientValues($rawValues): array {
        if (is_array($rawValues)) {
            $values = array_filter(array_map(function ($value) {
                return trim((string)$value);
            }, $rawValues));
        } else {
            $values = array_filter(array_map('trim', preg_split('/\s*,\s*/', trim((string)$rawValues))));
        }

        $normalized = [];
        foreach ($values as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }

            if (preg_match('/^\d+$/', $value)) {
                $normalized[] = $value;
                continue;
            }

            if (preg_match('/^email:(.+)$/i', $value, $matches)) {
                $email = trim($matches[1]);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $normalized[] = 'email:' . strtolower($email);
                }
                continue;
            }

            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $normalized[] = 'email:' . strtolower($value);
            }
        }

        return $normalized;
    }

    private function extractInternalRecipientIds($rawValues): array {
        $ids = [];
        foreach ($this->normalizeRecipientValues($rawValues) as $value) {
            if (preg_match('/^\d+$/', (string)$value)) {
                $ids[] = (int)$value;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    private function getRemovedItemsPreference(): bool {
        $userId = (int)($_SESSION['id'] ?? 0);
        return (bool)($_SESSION['correspondence_prefs'][$userId]['show_removed_items'] ?? false);
    }

    private function setRemovedItemsPreferenceValue(bool $enabled): void {
        $userId = (int)($_SESSION['id'] ?? 0);

        if (!isset($_SESSION['correspondence_prefs'])) {
            $_SESSION['correspondence_prefs'] = [];
        }

        if (!isset($_SESSION['correspondence_prefs'][$userId])) {
            $_SESSION['correspondence_prefs'][$userId] = [];
        }

        $_SESSION['correspondence_prefs'][$userId]['show_removed_items'] = $enabled;
    }

    public function setRemovedItemsPreference() {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $enabled = filter_var($_POST['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->setRemovedItemsPreferenceValue($enabled);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'show_removed_items' => $enabled,
        ]);
        exit;
    }

    /**
     * AJAX - Get Document Details for Modal
     */
    public function getDocumentDetails() {
        if (!isset($_SESSION['user'])) {
            echo "<p class='text-red-600'>Unauthorized</p>";
            exit;
        }

        if (ob_get_level()) ob_end_clean();

        $id = $_GET['id'] ?? 0;
        $doc = $this->model->getDocumentWithDetails($id);
            $circulations = $this->model->getCirculationDetails($id);

            // If this is a draft with no circulations, expose draft recipients/cc for display
            if (empty($circulations) && !empty($doc['is_draft'])) {
                $circulations = [];
                try {
                    require_once __DIR__ . '/../models/UserModel.php';
                    $userModel = new UserModel();
                    $draftRecipients = $this->normalizeRecipientValues($doc['draft_recipients'] ?? '');
                    $draftCc = $this->normalizeRecipientValues($doc['draft_cc'] ?? '');

                    foreach ($draftRecipients as $entry) {
                        if (preg_match('/^\d+$/', (string)$entry)) {
                            $user = $userModel->getUserById((int)$entry);
                            $circulations[] = [
                                'recipient_id' => (int)$entry,
                                'cc' => 0,
                                'status' => 'Pending',
                                'recipient_name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: null,
                                'position' => $user['position'] ?? null,
                                'department' => $user['department'] ?? null,
                                'email' => $user['email'] ?? null,
                            ];
                        } else {
                            $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                            $circulations[] = [
                                'recipient_id' => null,
                                'cc' => 0,
                                'status' => 'Pending',
                                'recipient_name' => $email,
                                'position' => null,
                                'department' => null,
                                'email' => $email,
                            ];
                        }
                    }

                    foreach ($draftCc as $entry) {
                        if (preg_match('/^\d+$/', (string)$entry)) {
                            $user = $userModel->getUserById((int)$entry);
                            $circulations[] = [
                                'recipient_id' => (int)$entry,
                                'cc' => 1,
                                'status' => 'Pending',
                                'recipient_name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: null,
                                'position' => $user['position'] ?? null,
                                'department' => $user['department'] ?? null,
                                'email' => $user['email'] ?? null,
                            ];
                        } else {
                            $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                            $circulations[] = [
                                'recipient_id' => null,
                                'cc' => 1,
                                'status' => 'Pending',
                                'recipient_name' => $email,
                                'position' => null,
                                'department' => null,
                                'email' => $email,
                            ];
                        }
                    }
                } catch (Throwable $e) {
                    // ignore - gracefully degrade to showing no recipients
                }
            }
        $attachments = $this->model->getAttachments($id);
        // Add download URLs for attachments
        foreach ($attachments as &$att) {
            $att['download_url'] = "index.php?controller=correspondence&action=download&attachment_id=" . (int)($att['id'] ?? 0);
        }
        unset($att);
        $history = $this->model->getDocumentChangeHistory($id);
        $threadEntries = $this->model->getThreadEntries($id);

        // Resolve creator name so views show a human-friendly name instead of numeric id
        $createdByName = null;
        try {
            require_once __DIR__ . '/../models/UserModel.php';
            $userModel = new UserModel();
            $creator = $userModel->getUserById((int)($doc['created_by'] ?? 0));
            if ($creator) {
                $createdByName = trim(($creator['firstName'] ?? '') . ' ' . ($creator['lastName'] ?? '')) ?: ($creator['email'] ?? null);
            }
        } catch (Throwable $e) {
            // ignore
        }
        if ($createdByName) $doc['created_by_name'] = $createdByName;

        if (!$doc) {
            echo "<p class='text-red-600 p-8'>Document not found.</p>";
            exit;
        }

        $receivedCount = (int)($doc['received_count'] ?? 0);
        $totalRecipients = (int)($doc['total_recipients'] ?? 0);
        // $status = ($totalRecipients > 0 && $receivedCount >= $totalRecipients) ? 'Completed' : 'Pending';
        $isDeleted = !empty($doc['is_deleted']);
        $isEdited = !empty($doc['is_edited']);
        $canManage = $this->model->canManageDocument($doc);
        $manageUntil = $this->model->getDocumentManageWindow($doc);
        $isSuperAdmin = $this->isSuperAdmin();
        $canEditDocument = $this->canEditCorrespondence() && !$isDeleted;
        $canDeleteDocument = $this->canDeleteCorrespondence() && !$isDeleted;
        // $statusClass = $status === 'Completed'
        //     ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
        //     : 'bg-amber-50 text-amber-700 border-amber-100';
        $priorityClass = match ($doc['priority'] ?? 'Medium') {
            'Urgent' => 'bg-red-50 text-red-700 border-red-100',
            'High' => 'bg-orange-50 text-orange-700 border-orange-100',
            'Medium' => 'bg-blue-50 text-blue-700 border-blue-100',
            default => 'bg-slate-50 text-slate-700 border-slate-100',
        };
        ?>
        <div class="space-y-6 text-gray-800">
            <?php if ($isDeleted): ?>
                <div class="rounded-2xl border border-red-100 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <p class="font-semibold">This document has been deleted by the sender.</p>
                    <p class="mt-1">It remains visible for audit purposes, but downloads and receiving actions are disabled.</p>
                </div>
            <?php elseif ($isEdited): ?>
                <?php
                    $lastUpdateRaw = $doc['edited_at'] ?? $doc['updated_at'] ?? $doc['created_at'] ?? null;
                    $lastUpdate = !empty($lastUpdateRaw) ? date('M d, Y g:i A', strtotime($lastUpdateRaw)) : null;
                ?>
                <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    <p class="font-semibold">Last update was <?= htmlspecialchars($lastUpdate ?? '—') ?></p>
                </div>
            <?php endif; ?>

            <div class="relative overflow-hidden rounded-3xl bg-slate-950 px-6 py-6 text-white shadow-2xl sm:px-7">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 via-transparent to-cyan-400/10"></div>
                <div class="relative flex flex-col gap-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.28em] text-sky-200/80">Document Repository</p>
                            <h4 class="mt-2 text-2xl font-semibold sm:text-3xl"><?= htmlspecialchars($doc['title']) ?></h4>
                            <p class="mt-2 text-sm text-slate-300">
                                <?= htmlspecialchars($doc['tracking_id']) ?> · <?= htmlspecialchars($doc['type'] ?? 'Document') ?> · <?= htmlspecialchars($doc['sender_email'] ?? 'Unknown sender') ?>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                          
                            <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold <?= $priorityClass ?>">
                                <?= htmlspecialchars($doc['priority'] ?? 'Medium') ?>
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">Circulated</p>
                            <p class="mt-2 text-sm font-semibold text-white"><?= !empty($doc['created_at']) ? date('M d, Y g:i A', strtotime($doc['created_at'])) : '—' ?></p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">Due Date</p>
                            <p class="mt-2 text-sm font-semibold text-white"><?= !empty($doc['due_date']) ? date('M d, Y', strtotime($doc['due_date'])) : '—' ?></p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">Recipients</p>
                            <p class="mt-2 text-sm font-semibold text-white"><?= $receivedCount ?> of <?= $totalRecipients ?> received</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-300">Attachments</p>
                            <p class="mt-2 text-sm font-semibold text-white"><?= count($attachments) ?> file(s)</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200 backdrop-blur">
                        <span class="font-semibold text-white">Manage window:</span>
                        <?php if ($manageUntil): ?>
                            <?= $canManage ? 'Active until ' . htmlspecialchars($manageUntil) : 'Expired on ' . htmlspecialchars($manageUntil) ?>
                        <?php else: ?>
                            Not available
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]">
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Overview</p>
                            <h5 class="mt-1 text-lg font-semibold text-slate-900">Document Details</h5>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Tracking ID</p>
                            <p class="mt-2 font-mono text-sm font-semibold text-slate-900"><?= htmlspecialchars($doc['tracking_id']) ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Type</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900"><?= htmlspecialchars($doc['type'] ?? '—') ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Sender</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900 break-words"><?= htmlspecialchars($doc['sender_email'] ?? '—') ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Confidential</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900"><?= !empty($doc['is_confidential']) ? 'Yes' : 'No' ?></p>
                        </div>
                    </div>

                    <?php if (!empty($doc['description'])): ?>
                        <div class="mt-6">
                            <p class="text-sm font-semibold text-slate-900">Description</p>
                            <div class="mt-2 rounded-2xl border border-slate-200 bg-white px-4 py-4 text-sm leading-7 text-slate-700">
                                <?= nl2br(htmlspecialchars($doc['description'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($doc['notes'])): ?>
                        <div class="mt-5">
                            <p class="text-sm font-semibold text-slate-900">Notes</p>
                            <div class="mt-2 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-700">
                                <?= nl2br(htmlspecialchars($doc['notes'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Quick Status</p>
                    <h5 class="mt-1 text-lg font-semibold text-slate-900">Circulation Summary</h5>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Pending</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900"><?= max(0, $totalRecipients - $receivedCount) ?> recipient(s)</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Completed</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900"><?= $receivedCount ?> recipient(s)</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Files</p>
                            <p class="mt-2 text-sm font-semibold text-slate-900"><?= count($attachments) ?> attachment(s)</p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Files</p>
                                    <h6 class="mt-1 text-sm font-semibold text-slate-900">Downloadable attachments</h6>
                                </div>
                                <span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                    <?= count($attachments) ?> file(s)
                                </span>
                            </div>

                            <div class="mt-4 space-y-2">
                                <?php if (!empty($attachments)): ?>
                                    <?php foreach ($attachments as $attachment): ?>
                                        <button type="button" <?= $isDeleted ? 'disabled' : '' ?> onclick="downloadAttachment(event, <?= (int)$attachment['id'] ?>)"
                                                class="flex w-full items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-left transition hover:border-blue-300 hover:bg-blue-50/70 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-900"><?= htmlspecialchars($attachment['file_name']) ?></p>
                                                <p class="mt-0.5 text-xs text-slate-500"><?= number_format(((int)($attachment['file_size'] ?? 0)) / 1024, 1) ?> KB</p>
                                            </div>
                                            <span class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700">
                                                Download
                                            </span>
                                        </button>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="rounded-2xl border border-dashed border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                                        No attachments uploaded.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <button type="button" onclick="openEditDocument(<?= (int)$doc['id'] ?>)"
                                    <?= !$canEditDocument ? 'disabled' : '' ?>
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                                Edit
                            </button>
                            <button type="button" onclick='openDeleteConfirm(<?= (int)$doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                    <?= !$canDeleteDocument ? 'disabled' : '' ?>
                                    class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100 disabled:cursor-not-allowed disabled:opacity-50">
                                Delete
                            </button>
                            <?php if ($this->canHardDeleteCorrespondence()): ?>
                                <button type="button" onclick='openHardDeleteConfirm(<?= (int)$doc["id"] ?>, <?= json_encode($doc["title"], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        class="inline-flex items-center justify-center rounded-2xl border border-rose-300 bg-rose-100 px-4 py-3 text-sm font-semibold text-rose-800 transition hover:bg-rose-200">
                                    Hard Delete
                                </button>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="closeModal()"
                                class="inline-flex w-full items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Close
                        </button>
                    </div>
                </aside>
            </div>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Receiving People</p>
                        <h5 class="mt-1 text-lg font-semibold text-slate-900">Recipient Breakdown</h5>
                    </div>
                    <p class="text-sm text-slate-500">Name, role, department, status, and circulation dates</p>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full border-separate border-spacing-y-3 text-left text-sm">
                        <thead>
                            <tr class="text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Position</th>
                                <th class="px-4 py-2">Department</th>
                                <th class="px-4 py-2">Type</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Date Circulated</th>
                                <th class="px-4 py-2">Due Date</th>
                                <th class="px-4 py-2">Date Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($circulations as $row): ?>
                                <?php
                                    $rowStatus = $row['status'] ?? 'Pending';
                                    $rowStatusClass = $rowStatus === 'Received'
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                                        : 'bg-amber-50 text-amber-700 border-amber-100';
                                    $rowType = !empty($row['cc']) ? 'CC' : 'Recipient';
                                    $recipientName = trim($row['recipient_name'] ?? '');
                                    if ($recipientName === '') {
                                        $recipientName = 'Recipient #' . (int)($row['recipient_id'] ?? 0);
                                    }
                                ?>
                                <tr class="rounded-2xl bg-slate-50/80 align-top">
                                    <td class="px-4 py-4 font-semibold text-slate-900 rounded-l-2xl">
                                        <?= htmlspecialchars($recipientName) ?>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($row['position'] ?? '—') ?></td>
                                    <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($row['department'] ?? '—') ?></td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700">
                                            <?= htmlspecialchars($rowType) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold <?= $rowStatusClass ?>">
                                            <?= htmlspecialchars($rowStatus) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">
                                        <?= !empty($row['circulated_at']) ? date('M d, Y g:i A', strtotime($row['circulated_at'])) : '—' ?>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap">
                                        <?= !empty($row['due_date']) ? date('M d, Y', strtotime($row['due_date'])) : '—' ?>
                                    </td>
                                    <td class="px-4 py-4 text-slate-600 whitespace-nowrap rounded-r-2xl">
                                        <?= !empty($row['received_at']) ? date('M d, Y g:i A', strtotime($row['received_at'])) : 'Pending' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($circulations)): ?>
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">
                                        No recipients have been assigned to this document yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">Change History</p>
                        <h5 class="mt-1 text-lg font-semibold text-slate-900">Document Activity</h5>
                    </div>
                    <p class="text-sm text-slate-500">Audited edits, deletes, receives, and creation events</p>
                </div>

                <div class="mt-5 space-y-3">
                        <?php if (!empty($history)): ?>
                        <?php foreach ($history as $idx => $entry): ?>
                            <?php
                                $actor = trim(($entry['firstName'] ?? '') . ' ' . ($entry['lastName'] ?? ''));
                                $actor = $actor !== '' ? $actor : ($entry['userName'] ?? 'System');

                                $fullDesc = (string)($entry['logDesc'] ?? '');
                                $isLong = mb_strlen($fullDesc) > 400;
                                $previewDesc = $isLong ? mb_substr($fullDesc, 0, 400) : $fullDesc;

                                // Keep preview readable (avoid cutting in middle of word too aggressively)
                                if ($isLong) {
                                    $previewDesc = preg_replace('/\s+\S*$/u', '', $previewDesc) ?: $previewDesc;
                                    $previewDesc .= '…';
                                }

                                $descId = 'historyDesc_' . (int)$doc['id'] . '_' . (int)$idx;
                                $btnId  = 'historyToggle_' . (int)$doc['id'] . '_' . (int)$idx;
                                $previewText = $isLong ? $previewDesc : $fullDesc;
                            ?>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($actor) ?></p>

                                        <p class="mt-1 text-sm leading-6 text-slate-600">
                                            <span id="<?= htmlspecialchars($descId) ?>"><?= htmlspecialchars($previewText) ?></span>
                                        </p>

                                        <?php if ($isLong): ?>
                                            <button
                                                id="<?= htmlspecialchars($btnId) ?>"
                                                type="button"
                                                data-history-toggle="1"
                                                data-history-target="<?= htmlspecialchars($descId) ?>"
                                                data-history-full="<?= htmlspecialchars($fullDesc, ENT_QUOTES) ?>"
                                                data-history-preview="<?= htmlspecialchars($previewText, ENT_QUOTES) ?>"
                                                data-expanded="0"
                                                class="mt-1 inline-flex items-center rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                                                >
                                                Read more
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <p class="text-xs font-medium text-slate-500 whitespace-nowrap">
                                        <?= !empty($entry['logDate']) ? date('M d, Y g:i A', strtotime($entry['logDate'])) : '—' ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                            No change history recorded yet.
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php
        exit;
    }

    /**
     * Full-page document view for admin and above
     */
    public function show() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userLevel = (int)($_SESSION['user_level'] ?? 3);
        if (!in_array($userLevel, [0, 1, 4, 5], true)) {
            // non-admins (including PM/DPM) should not access the full page
            $_SESSION['message'] = 'You do not have permission to view this page.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $doc = $this->model->getById($id);
        if (!$doc) {
            $_SESSION['message'] = 'Document not found.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $circulations = $this->model->getCirculationDetails($id);
        $attachments = $this->model->getAttachments($id);
        $history = $this->model->getDocumentChangeHistory($id);
        $threadEntries = $this->model->getThreadEntries($id);

        $canEditCorrespondence = $this->canEditCorrespondence();

        // Build grouped recipients (TO / CC) using latest circulation per recipient when available
        $recipientIndex = [];
        foreach ($circulations as $c) {
            $key = isset($c['recipient_id']) && $c['recipient_id'] !== null && $c['recipient_id'] !== '' ? 'id_' . (int)$c['recipient_id'] : 'email_' . ($c['email'] ?? uniqid());
            // keep the latest entry by circulation id (assume higher id is later)
            $existing = $recipientIndex[$key] ?? null;
            if ($existing === null || (isset($c['id']) && $c['id'] > ($existing['id'] ?? 0))) {
                $recipientIndex[$key] = [
                    'id' => $c['id'] ?? 0,
                    'recipient_id' => $c['recipient_id'] ?? null,
                    'name' => $c['recipient_name'] ?? ($c['email'] ?? '—'),
                    'office' => trim((($c['position'] ?? '') . ' ' . ($c['department'] ?? ''))),
                    'status' => strtolower($c['status'] ?? 'pending'),
                    'cc' => (int)($c['cc'] ?? 0),
                ];
            }
        }

        // If no circulations but document has draft recipients/cc, include them as pending
        if (empty($recipientIndex) && !empty($doc)) {
            try {
                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();
                $draftTo = !empty($doc['draft_recipients']) ? array_filter(array_map('trim', explode(',', $doc['draft_recipients']))) : [];
                $draftCc = !empty($doc['draft_cc']) ? array_filter(array_map('trim', explode(',', $doc['draft_cc']))) : [];

                foreach ($draftTo as $rid) {
                    $key = 'id_' . (int)$rid;
                    $user = $userModel->getUserById((int)$rid);
                    $recipientIndex[$key] = [
                        'id' => 0,
                        'recipient_id' => (int)$rid,
                        'name' => $user ? trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) : $rid,
                        'office' => $user ? trim(($user['position'] ?? '') . ' ' . ($user['department'] ?? '')) : '',
                        'status' => 'pending',
                        'cc' => 0,
                    ];
                }

                foreach ($draftCc as $rid) {
                    $key = 'id_' . (int)$rid;
                    $user = $userModel->getUserById((int)$rid);
                    $recipientIndex[$key] = [
                        'id' => 0,
                        'recipient_id' => (int)$rid,
                        'name' => $user ? trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) : $rid,
                        'office' => $user ? trim(($user['position'] ?? '') . ' ' . ($user['department'] ?? '')) : '',
                        'status' => 'pending',
                        'cc' => 1,
                    ];
                }
            } catch (Throwable $e) {
                // ignore
            }
        }

        $recipientsTo = [];
        $recipientsCc = [];
        foreach ($recipientIndex as $r) {
            if (!empty($r['cc'])) $recipientsCc[] = $r; else $recipientsTo[] = $r;
        }

        // For PM/DM (admin and above), only show history starting from finalization.
        // Encoders (user_level > 1) already don't get access to full page.
        if ($userLevel <= 1 && !empty($history)) {
            $chrono = array_reverse($history); // oldest -> newest
            $startIndex = null;
            foreach ($chrono as $idx => $entry) {
                $desc = (string)($entry['logDesc'] ?? '');
                if (stripos($desc, 'finaliz') !== false) { // finaliz(e|ed|ation)
                    $startIndex = $idx;
                    break;
                }
            }

            if ($startIndex !== null) {
                $slice = array_slice($chrono, $startIndex);
                $history = array_reverse($slice); // back to newest->oldest order
            } else {
                // If no explicit finalization entry found, remove draft-related logs
                $filtered = array_filter($history, function($e) {
                    $d = (string)($e['logDesc'] ?? '');
                    return stripos($d, 'draft') === false && stripos($d, 'saved as draft') === false;
                });
                if (!empty($filtered)) {
                    $history = array_values($filtered);
                }
                // else keep original history as fallback
            }
        }

        // Compute recipient counts from circulations
        $totalRecipients = 0;
        $receivedCount = 0;
        foreach ($circulations as $c) {
            $totalRecipients++;
            $st = strtolower((string)($c['status'] ?? ''));
            if ($st === 'received') {
                $receivedCount++;
            }
        }

        // Use the stored document status as the source of truth for the show page,
        // so it matches the same status displayed in the document table.
        $status = trim((string)($doc['status'] ?? 'Pending'));
        if ($status === '') {
            $status = 'Pending';
        }
        $documentDetailsStatus = $status;

        // Backwards compatibility: if the underlying status still uses legacy open/close
        // values, normalize them to the same display values used in the table.
        $lowerStatus = strtolower($status);
        if ($lowerStatus === 'open') {
            $status = 'Done';
            $documentDetailsStatus = 'Done';
        } elseif ($lowerStatus === 'close') {
            $status = 'Suspended';
            $documentDetailsStatus = 'Suspended';
        } elseif ($lowerStatus === 'inprogress') {
            $status = 'Inprogress';
            $documentDetailsStatus = 'Inprogress';
        }

        // Resolve creator name
        $createdByName = null;
        try {
            require_once __DIR__ . '/../models/UserModel.php';
            $userModel = new UserModel();
            $creator = $userModel->getUserById((int)($doc['created_by'] ?? 0));
            if ($creator) {
                $createdByName = trim(($creator['firstName'] ?? '') . ' ' . ($creator['lastName'] ?? '')) ?: ($creator['email'] ?? null);
            }
        } catch (Throwable $e) {
            // ignore
        }
        if ($createdByName) $doc['created_by_name'] = $createdByName;

        // The show page uses the stored document status directly as the source of truth.
        // No further override is needed here.


        $content = $this->renderView('correspondence/show', [
            'document' => $doc,
            'circulations' => $circulations,
            'attachments' => $attachments,
            'history' => $history,
            'threadEntries' => $threadEntries,
            'canEditCorrespondence' => $canEditCorrespondence,
            'recipientsTo' => $recipientsTo,
            'recipientsCc' => $recipientsCc,
            'status' => $status,
            'documentDetailsStatus' => $documentDetailsStatus,
            'receivedCount' => $receivedCount,
            'totalRecipients' => $totalRecipients,
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    /**
     * POST endpoint for admin/PM/DM to add a thread entry
     */
    public function postThreadEntry() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo 'Method not allowed';
            exit;
        }

        if (!isset($_SESSION['user'])) {
            $_SESSION['message'] = 'Unauthorized';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $userLevel = (int)($_SESSION['user_level'] ?? 3);
        if (!in_array($userLevel, [0, 1, 4, 5], true)) {
            $_SESSION['message'] = 'You do not have permission to perform this action.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $documentId = (int)($_POST['document_id'] ?? 0);
        if ($documentId <= 0) {
            $_SESSION['message'] = 'Invalid document.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        $entryKind = trim($_POST['entry_kind'] ?? 'comment');
        $content = trim($_POST['content'] ?? '');
        $cycleRef = trim($_POST['cycle_reference'] ?? '');

        $actorType = 'admin';
        $actorUserId = (int)($_SESSION['id'] ?? 0) ?: null;
        $actorName = trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? '')) ?: ($_SESSION['user'] ?? 'Admin');
        $roleLabel = $_SESSION['position'] ?? 'Admin';

        // Handle uploaded files (max 4, max 40MB total)
        $uploaded = [];
        $maxFiles = 4;
        $maxBytes = 41943040; // 40MB

        if (!empty($_FILES['thread_files'])) {
            $files = $_FILES['thread_files'];
            $count = min((int)count($files['name']), $maxFiles);

            $totalSize = 0;
            for ($i = 0; $i < $count; $i++) {
                $totalSize += (int)($files['size'][$i] ?? 0);
            }
            if ($totalSize > $maxBytes) {
                $_SESSION['message'] = 'Thread upload exceeds the 40MB total limit.';
                $_SESSION['msg_type'] = 'error';
                $this->redirect('index.php?controller=correspondence&action=show&id=' . (int)$documentId);
            }

            $targetDir = dirname(__DIR__, 2) . '/uploads/thread/' . $documentId;
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

            for ($i = 0; $i < $count; $i++) {
                if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

                $name = basename((string)($files['name'][$i] ?? ''));
                if ($name === '') continue;

                $uniq = time() . '_' . bin2hex(random_bytes(6)) . '_' . $name;
                $path = $targetDir . '/' . $uniq;

                if (move_uploaded_file($files['tmp_name'][$i], $path)) {
                    $uploaded[] = [
                        'file_name' => $name,
                        'file_path' => $path,
                        'file_size' => (int)($files['size'][$i] ?? 0)
                    ];
                }
            }
        }


        $entryId = $this->model->addThreadEntry(
            $documentId,
            $actorType,
            $actorUserId,
            $actorName,
            $roleLabel,
            $entryKind,
            $content,
            $cycleRef,
            $uploaded
        );

        if ($entryId) {
            $_SESSION['message'] = 'Posted.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to post entry.';
            $_SESSION['msg_type'] = 'error';
        }

        $this->redirect('index.php?controller=correspondence&action=show&id=' . (int)$documentId);
    }

    /**
     * Toggle open/close status for a document's circulations (admin only)
     * Expects POST: document_id, toggle_action (open|close)
     */
public function toggleOpenClose()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return $this->redirect('index.php?controller=correspondence&action=correspondence');
    }

    if (!isset($_SESSION['user'])) {
        $_SESSION['message'] = 'Unauthorized';
        $_SESSION['msg_type'] = 'error';
        return $this->redirect('index.php?controller=Auth&action=login');
    }

    if (!in_array((int)($_SESSION['user_level'] ?? 3), [0, 1, 4, 5], true)) {
        $_SESSION['message'] = 'Insufficient permissions';
        $_SESSION['msg_type'] = 'error';
        return $this->redirect('index.php?controller=correspondence&action=correspondence');
    }

    $documentId = (int)($_POST['document_id'] ?? 0);
    $action     = strtolower(trim($_POST['toggle_action'] ?? ''));

    $statusMap = [
        'close' => 'Done',
        'open'  => 'Suspended',
    ];

    if ($documentId <= 0 || !isset($statusMap[$action])) {
        $_SESSION['message'] = 'Invalid request.';
        $_SESSION['msg_type'] = 'error';
        return $this->redirect('index.php?controller=correspondence&action=correspondence');
    }

    $result = $this->model->updateAllCirculationsStatus($documentId, $statusMap[$action]);

    $_SESSION['message']  = $result['message'];
    $_SESSION['msg_type'] = $result['success'] ? 'success' : 'error';

    return $this->redirect('index.php?controller=correspondence&action=show&id=' . $documentId);
}

    // AJAX - get raw document data (JSON) for populating the main form
public function getDocumentData() {
    // Force clean JSON output
    header('Content-Type: application/json; charset=utf-8');
    if (ob_get_level()) ob_end_clean();

    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    try {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('Invalid document ID');
        }

        $doc = $this->model->getById($id);
        if (!$doc) {
            echo json_encode(['success' => false, 'message' => 'Document not found']);
            exit;
        }

        $circulations = $this->model->getCirculationDetails($id);

        $recipients = [];
        $cc = [];

        if (empty($circulations) && !empty($doc['is_draft'])) {
            // For drafts, return the saved draft_recipients/draft_cc values so the edit form can show them
            try {
                require_once __DIR__ . '/../models/UserModel.php';
                $userModel = new UserModel();
                $draftRecipients = $this->normalizeRecipientValues($doc['draft_recipients'] ?? '');
                $draftCc = $this->normalizeRecipientValues($doc['draft_cc'] ?? '');

                foreach ($draftRecipients as $entry) {
                    if (preg_match('/^\d+$/', (string)$entry)) {
                        $user = $userModel->getUserById((int)$entry);
                        $recipients[] = [
                            'id' => (int)$entry,
                            'name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? (string)$entry),
                            'email' => $user['email'] ?? null,
                        ];
                    } else {
                        $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                        $recipients[] = [
                            'id' => 0,
                            'name' => $email,
                            'email' => $email,
                        ];
                    }
                }

                foreach ($draftCc as $entry) {
                    if (preg_match('/^\d+$/', (string)$entry)) {
                        $user = $userModel->getUserById((int)$entry);
                        $cc[] = [
                            'id' => (int)$entry,
                            'name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? (string)$entry),
                            'email' => $user['email'] ?? null,
                        ];
                    } else {
                        $email = preg_match('/^email:(.+)$/i', (string)$entry, $matches) ? trim($matches[1]) : trim((string)$entry);
                        $cc[] = [
                            'id' => 0,
                            'name' => $email,
                            'email' => $email,
                        ];
                    }
                }
            } catch (Throwable $e) {
                // ignore and fallthrough to empty lists
            }
        } else {
            foreach ($circulations as $c) {
                $item = [
                    'id' => (int)($c['recipient_id'] ?? 0),
                    'name' => trim(($c['recipient_name'] ?? '') ?: ($c['email'] ?? '')),
                    'email' => $c['email'] ?? null,
                    'cc' => !empty($c['cc']) ? 1 : 0,
                ];
                if (!empty($item['cc'])) {
                    $cc[] = $item;
                } else {
                    $recipients[] = $item;
                }
            }
        }

        // attachments
        $attachments = $this->model->getAttachments($id);
        $attList = [];
        foreach ($attachments as $a) {
            $attList[] = [
                'id' => (int)($a['id'] ?? 0),
                'file_name' => $a['file_name'] ?? ($a['file'] ?? 'file'),
                'download_url' => "index.php?controller=correspondence&action=download&attachment_id=" . (int)($a['id'] ?? 0)
            ];
        }

        $payload = [
            'success' => true,
            'document' => $doc,
            'recipients' => $recipients,
            'cc' => $cc,
            'attachments' => $attList,
        ];

        echo json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        error_log('getDocumentData error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' Line: ' . $e->getLine());
        
        echo json_encode([
            'success' => false,
            'message' => 'Server error: ' . $e->getMessage(),
            'debug' => $e->getMessage()
        ]);
        exit;
    }
}




    public function getEditDocumentForm() {
        if (!isset($_SESSION['user'])) {
            echo "<p class='text-red-600 p-6'>Unauthorized</p>";
            exit;
        }

        if (!$this->canEditCorrespondence()) {
            echo "<p class='text-red-600 p-6'>You do not have permission to edit correspondence.</p>";
            exit;
        }

        $id = $_GET['id'] ?? 0;
        $doc = $this->model->getById($id);
        $history = $this->model->getDocumentChangeHistory($id);

        if (!$doc) {
            echo "<p class='text-red-600 p-6'>Document not found.</p>";
            exit;
        }

        $manageUntil = $this->model->getDocumentManageWindow($doc);
        $isDeleted = !empty($doc['is_deleted']);
        $canEditDocument = $this->canEditCorrespondence() && !$isDeleted;
        ?>
        <form id="editDocumentForm" method="POST" action="index.php?controller=correspondence&action=update&id=<?= (int)$doc['id'] ?>" class="space-y-0">
            <input type="hidden" name="id" value="<?= (int)$doc['id'] ?>">
            <?php
                // Render recipients/CC for edit form: prefer existing circulations, fall back to draft fields
                $circs = $this->model->getCirculationDetails($doc['id']);
                $editRecipients = [];
                $editCc = [];
                if (!empty($circs)) {
                    foreach ($circs as $c) {
                        if (!empty($c['cc'])) $editCc[] = $c;
                        else $editRecipients[] = $c;
                    }
                } elseif (!empty($doc['is_draft'])) {
                    try {
                        require_once __DIR__ . '/../models/UserModel.php';
                        $userModel = new UserModel();
                        $draftRecipients = !empty($doc['draft_recipients']) ? array_filter(array_map('trim', explode(',', $doc['draft_recipients']))) : [];
                        $draftCc = !empty($doc['draft_cc']) ? array_filter(array_map('trim', explode(',', $doc['draft_cc']))) : [];
                        foreach ($draftRecipients as $rid) {
                            $user = $userModel->getUserById((int)$rid);
                            $editRecipients[] = [ 'recipient_id' => (int)$rid, 'recipient_name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? null), 'email' => $user['email'] ?? null ];
                        }
                        foreach ($draftCc as $rid) {
                            $user = $userModel->getUserById((int)$rid);
                            $editCc[] = [ 'recipient_id' => (int)$rid, 'recipient_name' => trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?: ($user['email'] ?? null), 'email' => $user['email'] ?? null ];
                        }
                    } catch (Throwable $e) {
                        // ignore
                    }
                }
            ?>

            <div class="overflow-hidden rounded-xl  bg-white shadow-sm">
            

                <div class="px-4 py-3">
                    <div class="grid gap-3 xl:grid-cols-[1.05fr_0.95fr]">
                        <div class="space-y-3">
                            <section class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="text-sm font-medium text-slate-900">Recipients</h4>
                                    <span class="text-xs text-slate-500">Optional</span>
                                </div>
                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                        <label class="block text-xs font-medium text-slate-500">Recipients</label>
                                        <div id="recipients-chips-edit" class="mt-2 flex min-h-[40px] flex-wrap items-center gap-2">
                                            <?php if (empty($editRecipients)): ?>
                                                <span class="text-sm text-slate-500">No recipients selected</span>
                                            <?php else: ?>
                                                <?php foreach ($editRecipients as $r): ?>
                                                    <input type="hidden" name="recipients[]" value="<?= (int)$r['recipient_id'] ?>">
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-2.5 py-1 text-sm text-slate-700"><?= htmlspecialchars($r['recipient_name'] ?? ($r['email'] ?? '')) ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" onclick="openRecipientDrawer('recipients')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Edit recipients</button>
                                        </div>
                                    </div>

                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                        <label class="block text-xs font-medium text-slate-500">CC</label>
                                        <div id="cc-chips-edit" class="mt-2 flex min-h-[40px] flex-wrap items-center gap-2">
                                            <?php if (empty($editCc)): ?>
                                                <span class="text-sm text-slate-500">No CC selected</span>
                                            <?php else: ?>
                                                <?php foreach ($editCc as $c): ?>
                                                    <input type="hidden" name="cc[]" value="<?= (int)$c['recipient_id'] ?>">
                                                    <span class="inline-flex items-center gap-2 rounded-full bg-white px-2.5 py-1 text-sm text-slate-700"><?= htmlspecialchars($c['recipient_name'] ?? ($c['email'] ?? '')) ?></span>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mt-2">
                                            <button type="button" onclick="openRecipientDrawer('cc')" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">Edit CC</button>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <?php if ($isDeleted): ?>
                                <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
                                    This document has been removed and can no longer be edited.
                                </div>
                            <?php else: ?>
                                <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm text-blue-700">
                                    Update the sender copy here. Privileged roles can edit the document regardless of the original author.
                                </div>
                            <?php endif; ?>

                            <section class="rounded-lg border border-slate-200 p-3">
                                <h4 class="text-sm font-medium text-slate-900">Document details</h4>
                                <p class="mt-1 text-xs text-slate-500">Core fields and notes</p>

                                <div class="mt-3 grid gap-3 md:grid-cols-2">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Tracking ID</label>
                                        <input type="text" value="<?= htmlspecialchars($doc['tracking_id']) ?>" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm font-mono text-slate-700" readonly>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Manage Until</label>
                                        <input type="text" value="<?= htmlspecialchars($manageUntil ?? '—') ?>" class="h-9 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700" readonly>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-500">Document Title</label>
                                    <input type="text" name="title" value="<?= htmlspecialchars($doc['title']) ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="h-9 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-900 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                </div>

                                <div class="mt-3 grid gap-3 md:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Type</label>
                                        <select name="type" <?= !$canEditDocument ? 'disabled' : '' ?> class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                            <?php foreach (['Memo', 'Letter', 'Report', 'Circular'] as $type): ?>
                                                <option value="<?= htmlspecialchars($type) ?>" <?= $doc['type'] === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Priority</label>
                                        <select name="priority" <?= !$canEditDocument ? 'disabled' : '' ?> class="h-9 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                            <?php foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority): ?>
                                                <option value="<?= htmlspecialchars($priority) ?>" <?= $doc['priority'] === $priority ? 'selected' : '' ?>><?= htmlspecialchars($priority) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-slate-500">Due Date</label>
                                        <input type="date" name="due_date" value="<?= htmlspecialchars($doc['due_date'] ?? '') ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="h-9 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-500">Sender Email</label>
                                    <input type="email" name="sender_email" value="<?= htmlspecialchars($doc['sender_email']) ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="h-9 w-full rounded-lg border border-slate-200 px-3 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-500">Description</label>
                                    <textarea name="description" rows="4" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50"><?= htmlspecialchars($doc['description'] ?? '') ?></textarea>
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1 block text-xs font-medium text-slate-500">Notes</label>
                                    <textarea name="notes" rows="3" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-700 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
                                </div>

                                <label class="mt-3 flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                    <input type="checkbox" name="is_confidential" value="1" <?= !empty($doc['is_confidential']) ? 'checked' : '' ?> <?= !$canEditDocument ? 'disabled' : '' ?> class="mt-0.5 h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                    <span class="text-sm font-medium text-slate-700">Confidential</span>
                                </label>
                            </section>
                        </div>

                        <aside class="space-y-3">
                            <div class="rounded-lg border border-slate-200 p-3">
                                <p class="text-xs font-medium text-slate-500">Change summary</p>
                                <p class="mt-2 text-sm text-slate-700">
                                    <?= htmlspecialchars($doc['edit_summary'] ?? 'No edits have been recorded yet.') ?>
                                </p>
                            </div>

                            <div class="rounded-lg border border-slate-200 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <h4 class="text-sm font-medium text-slate-900">Recent activity</h4>
                                    <span class="text-xs text-slate-500">Latest</span>
                                </div>
                                <div class="mt-3 space-y-2">
                                    <?php if (!empty($history)): ?>
                                        <?php foreach (array_slice($history, 0, 4) as $entry): ?>
                                            <?php
                                                $actor = trim(($entry['firstName'] ?? '') . ' ' . ($entry['lastName'] ?? ''));
                                                $actor = $actor !== '' ? $actor : ($entry['userName'] ?? 'System');
                                            ?>
                                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5">
                                                <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($actor) ?></p>
                                                <p class="mt-1 text-sm text-slate-600"><?= htmlspecialchars($entry['logDesc'] ?? '') ?></p>
                                                <p class="mt-1 text-xs text-slate-500">
                                                    <?= !empty($entry['logDate']) ? date('M d, Y g:i A', strtotime($entry['logDate'])) : '—' ?>
                                                </p>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="rounded-lg border border-dashed border-slate-200 bg-slate-50 px-3 py-6 text-center text-sm text-slate-500">
                                            No change history recorded yet.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-50/70 px-4 py-3">
                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <button type="button" onclick="closeEditModal()" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                            Cancel
                        </button>
                        <button type="submit" <?= !$canEditDocument ? 'disabled' : '' ?> class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-slate-300">
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php
        exit;
    }

    public function update($id) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $ajaxResponse = null;

        if (!$this->canEditCorrespondence()) {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        try {
            $doc = $this->model->getById($id);
            if (!$doc) {
                throw new Exception('Document not found.');
            }

            if (!empty($doc['is_deleted'])) {
                throw new Exception('Deleted documents cannot be edited.');
            }

            $data = $this->normalizeDocumentPayload($_POST);
            // Gather recipients/cc from POST (string or array)
            $recipientsRaw = $_POST['recipients'] ?? '';
            $ccRaw = $_POST['cc'] ?? '';
            $recipientsRaw = implode(',', $this->normalizeRecipientValues($recipientsRaw));
            $ccRaw = implode(',', $this->normalizeRecipientValues($ccRaw));
            if (empty($data['title'])) {
                throw new Exception('Document title is required.');
            }

            $updated = $this->model->updateDocument($id, $data);
            if (!$updated) {
                // attempt best-effort draft recipients save even if metadata update failed
                try {
                    $this->model->updateDraftRecipients($id, $recipientsRaw !== '' ? $recipientsRaw : null, $ccRaw !== '' ? $ccRaw : null);
                } catch (Throwable $e) {
                    // ignore
                }

                throw new Exception('Unable to update the document.');
            }

            // Persist recipient/CC edits for drafts or circulated documents
            try {
                if (!empty($doc['is_draft']) || strtolower(trim((string)($doc['status'] ?? ''))) === 'draft') {
                    $this->model->updateDraftRecipients($id, $recipientsRaw !== '' ? $recipientsRaw : null, $ccRaw !== '' ? $ccRaw : null);
                } else {
                    $this->model->replaceDocumentCirculations($id, $recipientsRaw !== '' ? $recipientsRaw : null, $ccRaw !== '' ? $ccRaw : null);
                }
            } catch (Throwable $e) {
                error_log('Recipient/CC update failed during edit: ' . $e->getMessage());
            }

            // Save any newly uploaded attachments as part of this update
            $movedFiles = [];
            try {
                $newAttachments = $this->collectAttachmentUploads();
                if (!empty($newAttachments)) {
                    $movedFiles = $this->handleFileUploads($id, $newAttachments);
                }
            } catch (Throwable $e) {
                error_log('Attachment upload failed during update: ' . $e->getMessage());
                throw new Exception('Unable to process uploaded attachments. Please try again.');
            }

            // Remove any attachments marked for deletion
            $removedAttachments = $_POST['removed_attachments'] ?? [];
            if (is_array($removedAttachments) && !empty($removedAttachments)) {
                foreach ($removedAttachments as $attachmentId) {
                    $attachmentId = (int)$attachmentId;
                    if ($attachmentId <= 0) {
                        continue;
                    }
                    try {
                        $this->model->deleteAttachmentById($attachmentId);
                    } catch (Throwable $e) {
                        error_log('Attachment delete failed: ' . $e->getMessage());
                    }
                }
            }

            // If admin finalized the draft, convert to circulated document
            if (!empty($_POST['finalize'])) {
                // permission: only admin (1) or super-admin (0) can finalize
                $userLevel = (int)($_SESSION['user_level'] ?? 3);
                if (!in_array($userLevel, [0, 1, 4, 5], true)) {
                    $_SESSION['message'] = 'You do not have permission to finalize drafts.';
                    $_SESSION['msg_type'] = 'error';
                    header("Location: index.php?controller=correspondence&action=correspondence");
                    exit;
                }

                // mark draft as finalized and record finalizer
                $finalizerId = (int)($_SESSION['id'] ?? 0);
                $this->model->finalizeDraft($id, $finalizerId);

                // notify creator and recipients
                try {
                    require_once __DIR__ . "/../models/Notification.php";
                    $notif = new NotificationModel();
                    $doc = $this->model->getById($id);
                    $creatorId = (int)($doc['created_by'] ?? $_SESSION['id'] ?? 0);
                    $tracking = $doc['tracking_id'] ?? '';
                    $docUrl = "index.php?controller=correspondence&action=correspondence&doc_id={$id}";

                    if ($creatorId > 0) {
                        $notif->create($creatorId, "Your draft has been finalized and circulated • {$tracking}", $docUrl);
                    }

                    $circs = $this->model->getCirculationDetails($id);
                    $recipientIds = [];
                    foreach ($circs as $c) {
                        if (!empty($c['recipient_id'])) $recipientIds[] = (int)$c['recipient_id'];
                    }
                    $recipientIds = array_values(array_unique(array_filter($recipientIds)));
                    if (!empty($recipientIds)) {
                        $notif->createForMany($recipientIds, "A document has been circulated to you • {$tracking}", $docUrl);
                    }
                } catch (Throwable $e) {
                    error_log('Finalize notifications failed: ' . $e->getMessage());
                }

                try {
                    $doc = $this->model->getById($id);
                    if ($doc) {
                        $attachments = $this->model->getAttachments($id);
                        $circulations = $this->model->getCirculationDetails($id);
                        $creatorName = null;
                        try {
                            $userModel = new UserModel();
                            $creator = $userModel->getUserById((int)($doc['created_by'] ?? 0));
                            $creatorName = trim((string)($creator['firstName'] ?? '') . ' ' . (string)($creator['lastName'] ?? '')) ?: null;
                        } catch (Throwable $e) {
                            error_log('Creator name lookup failed: ' . $e->getMessage());
                        }
                        if ($creatorName !== null) {
                            $doc['created_by_name'] = $creatorName;
                        }
                        $this->correspondenceService->queueCorrespondenceNotifications($id, $doc, $circulations, $attachments, 'finalized');
                    }
                } catch (Throwable $e) {
                    error_log('Finalize email queue failed: ' . $e->getMessage());
                }

                $_SESSION['message'] = 'Draft finalized and circulated.';
                $_SESSION['msg_type'] = 'success';
                $ajaxResponse = ['success' => true, 'message' => 'Draft finalized and circulated.', 'document_id' => $id];
            } elseif (!empty($_POST['save_draft'])) {
                // Ensure draft recipients/cc are stored
                try {
                    $this->model->updateDraftRecipients($id, $recipientsRaw !== '' ? $recipientsRaw : null, $ccRaw !== '' ? $ccRaw : null);
                } catch (Throwable $e) {
                    error_log('Draft recipients save failed: ' . $e->getMessage());
                }

                // Save the existing document state as draft (do not circulate)
                $this->model->markAsDraft($id);

                // Notify admins that a draft was saved (reuse model helper)
                try {
                    $this->model->notifyAdminsOfDraft($id);
                } catch (Throwable $e) {
                    error_log('Draft notify (save_draft) failed: ' . $e->getMessage());
                }

                $_SESSION['message'] = 'Draft saved.';
                $_SESSION['msg_type'] = 'success';
                $ajaxResponse = ['success' => true, 'message' => 'Draft saved.', 'document_id' => $id];
            } else {
                $_SESSION['message'] = 'Document updated successfully.';
                $_SESSION['msg_type'] = 'success';
                $ajaxResponse = ['success' => true, 'message' => 'Document updated successfully.', 'document_id' => $id];
            }
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
            $ajaxResponse = ['success' => false, 'message' => $e->getMessage(), 'document_id' => $id];
        }

        if ($this->isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode($ajaxResponse ?? ['success' => false, 'message' => 'Request could not be completed.']);
            exit;
        }

        header("Location: index.php?controller=correspondence&action=correspondence");
        exit;
    }

    public function delete($id) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        if (!$this->canDeleteCorrespondence()) {
            $this->model->logCorrespondenceAction(
                "Delete blocked (insufficient privilege) • Tracking ID: " . ($this->model->getById($id)['tracking_id'] ?? 'Unknown') . " • Attempted by: " . $this->getActorName(),
                $id
            );
            $_SESSION['message'] = 'You do not have permission to delete correspondence.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        try {
            $doc = $this->model->getById($id);
            if (!$doc) {
                throw new Exception('Document not found.');
            }

            if (!empty($doc['is_deleted'])) {
                throw new Exception('Deleted documents cannot be deleted again.');
            }

            if (!$this->model->softDeleteDocument($id)) {
                throw new Exception('Unable to delete the document.');
            }

            $_SESSION['message'] = 'Document deleted. It remains visible in the repository as an audit record.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }

        header("Location: index.php?controller=correspondence&action=correspondence");
        exit;
    }

    public function hardDelete($id) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        if (!$this->canHardDeleteCorrespondence()) {
            $this->model->logCorrespondenceAction(
                "Hard delete blocked (insufficient privilege) • Tracking ID: " . ($this->model->getById($id)['tracking_id'] ?? 'Unknown') . " • Attempted by: " . $this->getActorName(),
                $id
            );
            $_SESSION['message'] = 'Only Super Admin can permanently delete correspondence.';
            $_SESSION['msg_type'] = 'error';
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('index.php?controller=correspondence&action=correspondence');
        }

        try {
            $doc = $this->model->getById($id);
            if (!$doc) {
                throw new Exception('Document not found.');
            }

            if (!$this->model->hardDeleteDocument($id)) {
                throw new Exception('Unable to permanently delete the document.');
            }

            $_SESSION['message'] = 'Document permanently deleted by Super Admin.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
        }

        header("Location: index.php?controller=correspondence&action=correspondence");
        exit;
    }

    private function normalizeDocumentPayload(array $input) {
        $dueDate = trim($input['due_date'] ?? '');

        return [
            'title' => trim($input['title'] ?? ''),
            'type' => $input['type'] ?? 'Memo',
            'description' => trim($input['description'] ?? ''),
            'sender_email' => trim($input['sender_email'] ?? 'noreply@dalton.com.ph'),
            'priority' => $input['priority'] ?? 'Medium',
            'due_date' => $dueDate !== '' ? $dueDate : null,
            'is_confidential' => isset($input['is_confidential']) ? 1 : 0,
            'notes' => trim($input['notes'] ?? ''),
        ];
    }

    private function getActorName() {
        $first = $_SESSION['firstName'] ?? '';
        $last = $_SESSION['lastName'] ?? '';
        return trim($first . ' ' . $last) ?: 'System';
    }

    private function canCreateCorrespondence(): bool {
        return $this->hasAnyRole([0, 1, 2, 4, 5, 6]);
    }

    private function canEditCorrespondence(): bool {
        return $this->hasAnyRole([0, 1, 2, 4, 5, 6]);
    }

    private function canDeleteCorrespondence(): bool {
        return $this->hasAnyRole([0, 1, 4, 5]);
    }

    private function canHardDeleteCorrespondence(): bool {
        return $this->isSuperAdmin();
    }

    /**
     * Handle form submission (Store new circulation)
     */
    public function store() {
    if (!isset($_SESSION['user'])) {
        $this->redirect('index.php?controller=Auth&action=login');
    }

    $ajaxResponse = null;

    if (!$this->canCreateCorrespondence()) {
        $_SESSION['message'] = 'You do not have permission to create correspondence.';
        $_SESSION['msg_type'] = 'error';
        $this->redirect('index.php?controller=correspondence&action=correspondence');
    }

    try {
        $attachments = $this->collectAttachmentUploads();

        // Handle both string and array input from form
        $recipientsRaw = $_POST['recipients'] ?? '';
        $ccRaw         = $_POST['cc'] ?? '';

        $recipientsRaw = implode(',', $this->normalizeRecipientValues($recipientsRaw));
        $ccRaw = implode(',', $this->normalizeRecipientValues($ccRaw));

        $userLevel = (int)($_SESSION['user_level'] ?? 3);

        $trackingId = trim($_POST['tracking_id'] ?? '');
        if ($trackingId === '') {
            $trackingId = $this->generateTrackingId();
        } else {
            if ($this->model->trackingIdExists($trackingId)) {
                throw new Exception("Tracking ID '{$trackingId}' is already in use.");
            }
        }

        $data = [
            'tracking_id'     => $trackingId,
            'title'           => trim($_POST['title'] ?? ''),
            'type'            => $_POST['type'] ?? 'Memo',
            'description'     => trim($_POST['description'] ?? ''),
            'sender_email'    => trim($_POST['sender_email'] ?? 'noreply@dalton.com.ph'),
            'priority'        => $_POST['priority'] ?? 'Medium',
            'due_date'        => trim($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
            'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
            'notes'           => trim($_POST['notes'] ?? ''),
            'is_draft'        => in_array($userLevel, [2,6], true) ? 1 : 0,
            'recipients'      => $recipientsRaw,
            'cc'              => $ccRaw
        ];

        if (empty($data['title'])) {
            throw new Exception("Document title is required.");
        }

        // Start DB transaction so document, attachments and circulations are atomic
        $this->model->beginTransaction();
        $documentId = $this->model->createDocument($data);

        if (!$documentId) {
            throw new Exception("Failed to create document.");
        }

        $movedFiles = $this->handleFileUploads($documentId, $attachments);

        // If user is encoder (level 2) or GRP Head (6) treat as draft: persist recipients/cc on document record and notify admins
        if (in_array($userLevel, [2,6], true)) {
            // Do NOT insert into document_circulations to prevent circulation to recipients.
            // Recipients/CC are stored in the documents.draft_recipients and draft_cc columns by createDocument().
            $this->model->notifyAdminsOfDraft($documentId);
            $_SESSION['message'] = "Draft saved and admin(s) notified. Tracking ID: " . $data['tracking_id'];
            $_SESSION['msg_type'] = "success";
            $ajaxResponse = ['success' => true, 'message' => 'Draft saved and admin(s) notified.', 'document_id' => $documentId];
        } else {
            // Process into document_circulations for normal users
            if (!$this->processRecipients($documentId, $data['recipients'], $data['cc'])) {
                throw new Exception("Document was created, but recipients/CC could not be saved.");
            }

            if (!$this->model->setDocumentStatus($documentId, 'Inprogress')) {
                throw new Exception('Unable to set document status to Inprogress.');
            }
            if (!$this->model->setCirculationsStatus($documentId, 'Inprogress')) {
                throw new Exception('Unable to set circulation status to Inprogress.');
            }

            // notify creator and recipients via internal notifications
            try {
                require_once __DIR__ . "/../models/Notification.php";
                $notif = new NotificationModel();

                // notify creator
                $doc = $this->model->getById($documentId);
                $creatorId = (int)($doc['created_by'] ?? $_SESSION['id'] ?? 0);
                $tracking = $doc['tracking_id'] ?? $data['tracking_id'];
                $docUrl = "index.php?controller=correspondence&action=correspondence&doc_id={$documentId}";

                if ($creatorId > 0) {
                    $notif->create($creatorId, "Your document has been circulated • {$tracking}", $docUrl);
                }

                // notify recipients (standard users) parsed from recipients / cc
                $recipientIds = [];
                if (!empty($data['recipients'])) {
                    $recipientIds = array_merge($recipientIds, $this->extractInternalRecipientIds($data['recipients']));
                }
                if (!empty($data['cc'])) {
                    $recipientIds = array_merge($recipientIds, $this->extractInternalRecipientIds($data['cc']));
                }
                $recipientIds = array_values(array_unique(array_filter($recipientIds)));
                if (!empty($recipientIds)) {
                    $notif->createForMany($recipientIds, "A document has been circulated to you • {$tracking}", $docUrl);
                }
            } catch (Throwable $e) {
                error_log('Post-circulation notification failed: ' . $e->getMessage());
            }

            try {
                $doc = $this->model->getById($documentId);
                if ($doc) {
                    $attachments = $this->model->getAttachments($documentId);
                    $circulations = $this->model->getCirculationDetails($documentId);
                    $creatorName = null;
                    try {
                        $userModel = new UserModel();
                        $creator = $userModel->getUserById((int)($doc['created_by'] ?? 0));
                        $creatorName = trim((string)($creator['firstName'] ?? '') . ' ' . (string)($creator['lastName'] ?? '')) ?: null;
                    } catch (Throwable $e) {
                        error_log('Creator name lookup failed: ' . $e->getMessage());
                    }
                    if ($creatorName !== null) {
                        $doc['created_by_name'] = $creatorName;
                    }
                    $this->correspondenceService->queueCorrespondenceNotifications($documentId, $doc, $circulations, $attachments, 'circulated');
                }
            } catch (Throwable $e) {
                error_log('Post-circulation email queue failed: ' . $e->getMessage());
            }

            $_SESSION['message'] = "Document circulated successfully! Tracking ID: " . $data['tracking_id'];
            $_SESSION['msg_type'] = "success";
            $ajaxResponse = ['success' => true, 'message' => 'Document circulated successfully.', 'document_id' => $documentId];
        }

        // commit DB transaction after all operations
        try { $this->model->commit(); } catch (Throwable $ex) { error_log('Commit failed: ' . $ex->getMessage()); }

    } catch (Exception $e) {
        // rollback DB and remove any moved files
        try { $this->model->rollBack(); } catch (Throwable $ex) { error_log('Rollback failed: ' . $ex->getMessage()); }
        if (!empty($movedFiles) && is_array($movedFiles)) {
            foreach ($movedFiles as $f) {
                if (is_string($f) && file_exists($f)) {
                    @unlink($f);
                }
            }
        }

        $_SESSION['message'] = $e->getMessage();
        $_SESSION['msg_type'] = "error";
        $ajaxResponse = ['success' => false, 'message' => $e->getMessage()];
    }

    if ($this->isAjaxRequest()) {
        header('Content-Type: application/json');
        echo json_encode($ajaxResponse ?? ['success' => false, 'message' => 'Request could not be completed.']);
        exit;
    }

    header("Location: index.php?controller=correspondence&action=correspondence");
    exit;
}

    /**
     * Generate Tracking ID (DPEARP-00001)
     */
    private function generateTrackingId() {
        $lastId = $this->model->getLastTrackingId();
        
        if ($lastId) {
            $number = (int) substr($lastId, 7) + 1;
        } else {
            $number = 1;
        }

        return 'DPEARP-' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    private function getCorrespondenceUploadDirectory(): string {
        $uploadDir = dirname(__DIR__, 2) . '/uploads/correspondence/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        return $uploadDir;
    }

    private function buildCorrespondenceAttachmentUrl(string $filename): string {
        $baseUrl = rtrim(BASE_URL, '/') . '/';
        return $baseUrl . 'uploads/correspondence/' . ltrim(basename($filename), '/');
    }

    private function resolveStoredAttachmentPath(string $path): string {
        if ($path === '') {
            return '';
        }

        if (file_exists($path)) {
            return $path;
        }

        $rootDir = $this->getCorrespondenceUploadDirectory();
        $candidate = $path;

        if (strpos($candidate, '/') === false) {
            $candidate = $rootDir . $candidate;
        } elseif (strpos($candidate, '/uploads/correspondence/') === false && strpos($candidate, 'correspondence/') !== false) {
            $candidate = $rootDir . basename($candidate);
        }

        if (file_exists($candidate)) {
            return $candidate;
        }


        return $path;
    }

    /**
     * Handle multiple file uploads
     */
    private function collectAttachmentUploads(): array {
        if (empty($_FILES['attachments']) || !is_array($_FILES['attachments']['name'] ?? null)) {
            return [];
        }

        $files = [];
        $totalSize = 0;

        foreach (array_keys($_FILES['attachments']['name']) as $index) {
            $name = trim((string)($_FILES['attachments']['name'][$index] ?? ''));
            $error = (int)($_FILES['attachments']['error'][$index] ?? UPLOAD_ERR_NO_FILE);

            if ($name === '' || $error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($error !== UPLOAD_ERR_OK) {
                throw new Exception('One of the attachments failed to upload. Please try again.');
            }

            $fileSize = (int)($_FILES['attachments']['size'][$index] ?? 0);
            if ($fileSize <= 0) {
                throw new Exception('One of the attachments is empty.');
            }

            if (count($files) >= self::MAX_ATTACHMENT_FILES) {
                throw new Exception('You can attach up to 4 files only.');
            }

            $totalSize += $fileSize;
            if ($totalSize > self::MAX_ATTACHMENT_BYTES) {
                throw new Exception('Total attachment size must not exceed 40MB.');
            }

            $files[] = [
                'name' => $name,
                'tmp_name' => $_FILES['attachments']['tmp_name'][$index] ?? '',
                'size' => $fileSize,
            ];
        }

        return $files;
    }

    private function handleFileUploads($documentId, array $attachments = []) {
        if (empty($attachments)) {
            return [];
        }

        $uploaded = [];
        $uploadDir = $this->getCorrespondenceUploadDirectory();

        foreach ($attachments as $attachment) {
            $name = $attachment['name'];
            $fileSize = (int)$attachment['size'];
            $tmpName = $attachment['tmp_name'];

            $fileExt = pathinfo($name, PATHINFO_EXTENSION);
            $newFileName = uniqid('doc_') . '.' . strtolower($fileExt);
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $this->model->addAttachment($documentId, $name, $destination, $fileSize);
                $uploaded[] = $destination;
            }
        }

        return $uploaded;
    }

    /**
     * Process Recipients and CC
     */
    private function processRecipients($documentId, $recipients, $cc) {
        $saved = true;

        // Both $recipients and $cc are comma-separated strings from hidden fields
        if (!empty($recipients)) {
            $saved = $this->model->addRecipients($documentId, $recipients, false) && $saved;
        }

        if (!empty($cc)) {
            $saved = $this->model->addRecipients($documentId, $cc, true) && $saved;
        }

        return $saved;
    }

    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    public function emailProgress(): void
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $documentId = (int)($_GET['id'] ?? 0);
        if ($documentId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Document id is required']);
            return;
        }

        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $this->emailProgressService->getProgressForCorrespondence($documentId)));
    }

       public function download() {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

        $id = $_GET['id'] ?? 0;   // Document ID or Attachment ID
        $attachmentId = $_GET['attachment_id'] ?? 0;

        if (!empty($attachmentId)) {
            $attachment = $this->model->getAttachmentById($attachmentId);
            if (!$attachment) {
                $_SESSION['message'] = 'Attachment not found.';
                $_SESSION['msg_type'] = 'error';
                header("Location: index.php?controller=Correspondence&action=correspondence");
                exit;
            }

            $doc = $this->model->getById($attachment['document_id']);
            if ($doc && !empty($doc['is_deleted'])) {
                $this->model->logCorrespondenceAction(
                    "Download blocked on deleted document • Tracking ID: " . ($doc['tracking_id'] ?? 'Unknown') . " • Attempted by: " . $this->getActorName(),
                    $attachment['document_id']
                );
                $_SESSION['message'] = "This document was deleted and is no longer downloadable.";
                $_SESSION['msg_type'] = "error";
                header("Location: index.php?controller=Correspondence&action=correspondence");
                exit;
            }

            $this->streamAttachment($attachment);
        }

        if (empty($id)) {
            $_SESSION['message'] = "No file specified.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        $doc = $this->model->getById($id);
        if ($doc && !empty($doc['is_deleted'])) {
            $this->model->logCorrespondenceAction(
                "Download blocked on deleted document • Tracking ID: " . ($doc['tracking_id'] ?? 'Unknown') . " • Attempted by: " . $this->getActorName(),
                $id
            );
            $_SESSION['message'] = "This document was deleted and is no longer downloadable.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        // Get attachments for this document
        $attachments = $this->model->getAttachments($id);

        if (empty($attachments)) {
            $_SESSION['message'] = "No attachments found for this document.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        // For now, download the first attachment (you can extend to choose specific file later)
        $file = $attachments[0];
        $this->streamAttachment($file);
    }

    private function streamAttachment(array $attachment): void {
        $fullPath = $this->resolveStoredAttachmentPath((string)($attachment['file_path'] ?? ''));

        if ($fullPath === '' || !file_exists($fullPath)) {
            $_SESSION['message'] = "File not found on server.";
            $_SESSION['msg_type'] = "error";
            header("Location: index.php?controller=Correspondence&action=correspondence");
            exit;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $fileName = $attachment['file_name'] ?: basename($fullPath);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fullPath);
        finfo_close($finfo);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        readfile($fullPath);
        exit;
    }
    // You can add more methods later: view(), edit(), delete(), download(), etc.
}
