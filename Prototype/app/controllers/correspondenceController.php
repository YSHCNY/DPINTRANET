<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/correspondence.php";
require_once '../app/models/UserModel.php';


class CorrespondenceController extends Controller {

    private $model;
    private const MAX_ATTACHMENT_FILES = 4;
    private const MAX_ATTACHMENT_BYTES = 41943040; // 40 MB

    public function __construct() {
        $this->model = new CorrespondenceModel();
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

        $content = $this->renderView('correspondence/index', [
            'documents' => $documents,
            'users'     => $users,
            'nextTrackingId' => $nextTrackingId,
            'showRemovedItems' => $showRemovedItems,
            'canCreateCorrespondence' => $this->canCreateCorrespondence(),
            'canEditCorrespondence' => $this->canEditCorrespondence(),
            'canDeleteCorrespondence' => $this->canDeleteCorrespondence(),
            'canHardDeleteCorrespondence' => $this->canHardDeleteCorrespondence(),
        ]);

        $this->view('layout/main', ['content' => $content]);
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

        $id = $_GET['id'] ?? 0;
        $doc = $this->model->getDocumentWithDetails($id);
        $circulations = $this->model->getCirculationDetails($id);
        $attachments = $this->model->getAttachments($id);
        $history = $this->model->getDocumentChangeHistory($id);

        if (!$doc) {
            echo "<p class='text-red-600 p-8'>Document not found.</p>";
            exit;
        }

        $receivedCount = (int)($doc['received_count'] ?? 0);
        $totalRecipients = (int)($doc['total_recipients'] ?? 0);
        $status = ($totalRecipients > 0 && $receivedCount >= $totalRecipients) ? 'Completed' : 'Pending';
        $isDeleted = !empty($doc['is_deleted']);
        $isEdited = !empty($doc['is_edited']);
        $canManage = $this->model->canManageDocument($doc);
        $manageUntil = $this->model->getDocumentManageWindow($doc);
        $isSuperAdmin = $this->isSuperAdmin();
        $canEditDocument = $this->canEditCorrespondence() && !$isDeleted;
        $canDeleteDocument = $this->canDeleteCorrespondence() && !$isDeleted;
        $statusClass = $status === 'Completed'
            ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
            : 'bg-amber-50 text-amber-700 border-amber-100';
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
                            <span class="inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-semibold <?= $statusClass ?>">
                                <?= htmlspecialchars($status) ?>
                            </span>
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
        <form id="editDocumentForm" method="POST" action="index.php?controller=correspondence&action=update&id=<?= (int)$doc['id'] ?>" class="space-y-6">
            <input type="hidden" name="id" value="<?= (int)$doc['id'] ?>">

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)]">
                <section class="space-y-4">
                    <?php if ($isDeleted): ?>
                        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            This document has been removed and can no longer be edited.
                        </div>
                    <?php else: ?>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                            Update the sender copy here. Privileged roles can edit the document regardless of the original author.
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Tracking ID</label>
                            <input type="text" value="<?= htmlspecialchars($doc['tracking_id']) ?>" class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-mono text-slate-700" readonly>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Manage Until</label>
                            <input type="text" value="<?= htmlspecialchars($manageUntil ?? '—') ?>" class="w-full h-11 rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-700" readonly>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Document Title</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($doc['title']) ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm text-slate-900 focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Type</label>
                            <select name="type" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm bg-white focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                <?php foreach (['Memo', 'Letter', 'Report', 'Circular'] as $type): ?>
                                    <option value="<?= htmlspecialchars($type) ?>" <?= $doc['type'] === $type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Priority</label>
                            <select name="priority" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm bg-white focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                                <?php foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority): ?>
                                    <option value="<?= htmlspecialchars($priority) ?>" <?= $doc['priority'] === $priority ? 'selected' : '' ?>><?= htmlspecialchars($priority) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Due Date</label>
                            <input type="date" name="due_date" value="<?= htmlspecialchars($doc['due_date'] ?? '') ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Sender Email</label>
                        <input type="email" name="sender_email" value="<?= htmlspecialchars($doc['sender_email']) ?>" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full h-11 rounded-2xl border border-slate-200 px-4 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Description</label>
                        <textarea name="description" rows="5" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50"><?= htmlspecialchars($doc['description'] ?? '') ?></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500 mb-1.5">Notes</label>
                        <textarea name="notes" rows="3" <?= !$canEditDocument ? 'disabled' : '' ?> class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm focus:outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50"><?= htmlspecialchars($doc['notes'] ?? '') ?></textarea>
                    </div>

                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                        <input type="checkbox" name="is_confidential" value="1" <?= !empty($doc['is_confidential']) ? 'checked' : '' ?> <?= !$canEditDocument ? 'disabled' : '' ?> class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                        <span class="text-sm font-medium text-slate-700">Confidential</span>
                    </label>
                </section>

                <aside class="space-y-4">
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-slate-500">Change Snapshot</p>
                        <h4 class="mt-1 text-base font-semibold text-slate-900">Latest edit note</h4>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            <?= htmlspecialchars($doc['edit_summary'] ?? 'No edits have been recorded yet.') ?>
                        </p>
                    </div>

                    <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-600">History</p>
                        <h4 class="mt-1 text-base font-semibold text-slate-900">Recent Activity</h4>
                        <div class="mt-4 space-y-3 max-h-[360px] overflow-auto pr-1">
                            <?php if (!empty($history)): ?>
                                <?php foreach (array_slice($history, 0, 5) as $entry): ?>
                                    <?php
                                        $actor = trim(($entry['firstName'] ?? '') . ' ' . ($entry['lastName'] ?? ''));
                                        $actor = $actor !== '' ? $actor : ($entry['userName'] ?? 'System');
                                    ?>
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                                        <p class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($actor) ?></p>
                                        <p class="mt-1 text-sm leading-6 text-slate-600"><?= htmlspecialchars($entry['logDesc'] ?? '') ?></p>
                                        <p class="mt-2 text-xs text-slate-500">
                                            <?= !empty($entry['logDate']) ? date('M d, Y g:i A', strtotime($entry['logDate'])) : '—' ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">
                                    No change history recorded yet.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeEditModal()" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Cancel
                </button>
                <button type="submit" <?= !$canEditDocument ? 'disabled' : '' ?> class="inline-flex items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    Save Changes
                </button>
            </div>
        </form>
        <?php
        exit;
    }

    public function update($id) {
        if (!isset($_SESSION['user'])) {
            $this->redirect('index.php?controller=Auth&action=login');
        }

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
            if (empty($data['title'])) {
                throw new Exception('Document title is required.');
            }

            if (!$this->model->updateDocument($id, $data)) {
                throw new Exception('Unable to update the document.');
            }

            $_SESSION['message'] = 'Document updated successfully.';
            $_SESSION['msg_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            $_SESSION['msg_type'] = 'error';
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
        return $this->hasAnyRole([0, 1, 2]);
    }

    private function canEditCorrespondence(): bool {
        return $this->hasAnyRole([0, 1, 2]);
    }

    private function canDeleteCorrespondence(): bool {
        return $this->hasAnyRole([0, 1]);
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

        // Convert array to comma-separated string
        if (is_array($recipientsRaw)) {
            $recipientsRaw = implode(',', array_filter($recipientsRaw));
        } else {
            $recipientsRaw = trim($recipientsRaw);
        }

        if (is_array($ccRaw)) {
            $ccRaw = implode(',', array_filter($ccRaw));
        } else {
            $ccRaw = trim($ccRaw);
        }

        $data = [
            'tracking_id'     => $this->generateTrackingId(),
            'title'           => trim($_POST['title'] ?? ''),
            'type'            => $_POST['type'] ?? 'Memo',
            'description'     => trim($_POST['description'] ?? ''),
            'sender_email'    => trim($_POST['sender_email'] ?? 'noreply@dalton.com.ph'),
            'priority'        => $_POST['priority'] ?? 'Medium',
            'due_date'        => trim($_POST['due_date'] ?? '') !== '' ? $_POST['due_date'] : null,
            'is_confidential' => isset($_POST['is_confidential']) ? 1 : 0,
            'notes'           => trim($_POST['notes'] ?? ''),
            'recipients'      => $recipientsRaw,
            'cc'              => $ccRaw
        ];

        if (empty($data['title'])) {
            throw new Exception("Document title is required.");
        }

        $documentId = $this->model->createDocument($data);

        if (!$documentId) {
            throw new Exception("Failed to create document.");
        }

        $this->handleFileUploads($documentId, $attachments);

        // Process into document_circulations
        if (!$this->processRecipients($documentId, $data['recipients'], $data['cc'])) {
            throw new Exception("Document was created, but recipients/CC could not be saved.");
        }


        $_SESSION['message'] = "Document circulated successfully! Tracking ID: " . $data['tracking_id'];
        $_SESSION['msg_type'] = "success";

    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['msg_type'] = "error";
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
        $uploadDir = __DIR__ . "/../uploads/correspondence/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        foreach ($attachments as $attachment) {
            $name = $attachment['name'];
            $fileSize = (int)$attachment['size'];
            $tmpName = $attachment['tmp_name'];

            $fileExt = pathinfo($name, PATHINFO_EXTENSION);
            $newFileName = uniqid('doc_') . '.' . strtolower($fileExt);
            $destination = $uploadDir . $newFileName;

            if (move_uploaded_file($tmpName, $destination)) {
                $this->model->addAttachment($documentId, $name, $destination, $fileSize);
                $uploaded[] = $name;
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
        $fullPath = $attachment['file_path'] ?? '';

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
