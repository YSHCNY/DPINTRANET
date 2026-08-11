<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/models/StaffDirectoryModel.php';

if (file_exists(__DIR__ . '/../config.php')) {
    require_once __DIR__ . '/../config.php';
} elseif (file_exists(__DIR__ . '/../../app/config.php')) {
    require_once __DIR__ . '/../../app/config.php';
}



class StaffDirectoryController extends Controller {
    private StaffDirectoryModel $directoryModel;

    public function __construct() {
        $this->directoryModel = new StaffDirectoryModel();
    }

    private function logStaffDirectoryAction(string $action, string $staffId, array $context = []): void {
        $actorId = (int)($_SESSION['id'] ?? $_SESSION['user_id'] ?? 0);
        $actorName = (string)($_SESSION['user'] ?? ($actorId > 0 ? $actorId : 'system'));

        $details = [];
        $targetRef = $staffId !== '' ? $staffId : 'new staff entry';

        if (!empty($context['first_name'])) {
            $details[] = 'First name: ' . $context['first_name'];
        }
        if (!empty($context['last_name'])) {
            $details[] = 'Last name: ' . $context['last_name'];
        }
        if (!empty($context['position'])) {
            $details[] = 'Position: ' . $context['position'];
        }
        if (!empty($context['department'])) {
            $details[] = 'Department: ' . $context['department'];
        }
        if (!empty($context['firm'])) {
            $details[] = 'Firm: ' . $context['firm'];
        }
        if (!empty($context['message'])) {
            $details[] = $context['message'];
        }

        $description = match ($action) {
            'create' => 'Created staff directory entry ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'update' => 'Updated staff directory entry ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'delete' => 'Deleted staff directory entry ' . $targetRef . ($details ? ' • ' . implode(' • ', $details) : ''),
            'import' => 'Imported staff directory entries' . ($details ? ' • ' . implode(' • ', $details) : ''),
            default => 'Staff directory action ' . $action . ' for ' . $targetRef,
        };

        try {
            $db = Database::connect();
            $stmt = $db->prepare("INSERT INTO systemLogs (userName, logDesc, module, logDate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$actorName, $description, 'Staff Directory', date('Y-m-d H:i:s')]);
        } catch (Throwable $e) {
            // Keep the flow intact even if logging fails.
        }
    }

    public function index() {
        $this->requireLogin();

        $staffEntries = $this->directoryModel->getAllStaff();
        $firms = array_values(array_filter(array_unique(array_map(function ($item) {
            return trim($item['firm'] ?? '');
        }, $staffEntries))));
        sort($firms, SORT_NATURAL | SORT_FLAG_CASE);

        $positions = array_values(array_filter(array_unique(array_map(function ($item) {
            return trim($item['position'] ?? '');
        }, $staffEntries))));
        sort($positions, SORT_NATURAL | SORT_FLAG_CASE);

        $metrics = [
            'totalStaff' => count($staffEntries),
            'activeStaff' => count(array_filter($staffEntries, function ($item) {
                return ($item['status'] ?? '') === 'active';
            })),
            'firmCount' => count($firms),
            'recentDeployment' => $this->getLatestDeploymentDate($staffEntries),
        ];

        $content = $this->renderView('staff_directory/index', [
            'staffEntries' => $staffEntries,
            'firms' => $firms,
            'positions' => $positions,
            'metrics' => $metrics,
        ]);

        $this->view('layout/main', ['content' => $content]);
    }

    public function create() {
        $this->requireLogin();

        $content = $this->renderView('staff_directory/create', []);
        $this->view('layout/main', ['content' => $content]);
    }

    public function store() {
        $this->requireLogin();

        $staffId = trim($_POST['staff_id'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $firm = trim($_POST['firm'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $deploymentDate = trim($_POST['deployment_date'] ?? '');

        if ($staffId === '' || $firstName === '' || $lastName === '' || $position === '' || $email === '') {
            $_SESSION['message'] = 'Please complete all required fields: Staff ID, first name, last name, position and email.';
            $_SESSION['msg_type'] = 'error';
            header('Location: index.php?controller=StaffDirectory&action=create');
            exit;
        }

        $imageName = null;
        if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/staff_directory/';
            if ($uploadDir === false) {
                $uploadDir = dirname(__DIR__, 2) . '/uploads/staff_directory/';
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['profile_photo']['name']);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $validExt = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $validExt, true)) {
                $imageName = preg_replace('/[^a-zA-Z0-9_-]/', '', $staffId) . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath);
            }
        }

        $data = [
            'staff_id' => $staffId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'position' => $position,
            'department' => $department !== '' ? $department : null,
            'firm' => $firm !== '' ? $firm : null,
            'email' => $email,
            'contact_number' => $contactNumber !== '' ? $contactNumber : null,
            'deployment_date' => $deploymentDate !== '' ? $deploymentDate : null,
            'image' => $imageName,
            'status' => 'active',
        ];

        try {
            $success = $this->directoryModel->createStaff($data);
        } catch (Exception $e) {
            $success = false;
        }

        if ($success) {
            $this->logStaffDirectoryAction('create', $staffId, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position' => $position,
                'department' => $department,
                'firm' => $firm,
            ]);
            $_SESSION['message'] = 'Staff profile created successfully.';
            $_SESSION['msg_type'] = 'success';
            header('Location: index.php?controller=StaffDirectory&action=index');
            exit;
        }

        $_SESSION['message'] = 'Unable to save staff profile. Confirm the staff ID is unique and try again.';
        $_SESSION['msg_type'] = 'error';
        header('Location: index.php?controller=StaffDirectory&action=create');
        exit;
    }

    public function edit($staffId) {
        $this->requireLogin();

        $staff = $this->directoryModel->getStaffById($staffId);
        if (!$staff) {
            $_SESSION['message'] = 'Staff profile not found.';
            $_SESSION['msg_type'] = 'error';
            header('Location: index.php?controller=StaffDirectory&action=index');
            exit;
        }

        $content = $this->renderView('staff_directory/edit', ['staff' => $staff]);
        $this->view('layout/main', ['content' => $content]);
    }

    public function update() {
        $this->requireLogin();

        $staffId = trim($_POST['staff_id'] ?? '');
        $firstName = trim($_POST['firstName'] ?? '');
        $lastName = trim($_POST['lastName'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $firm = trim($_POST['firm'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contactNumber = trim($_POST['contact_number'] ?? '');
        $deploymentDate = trim($_POST['deployment_date'] ?? '');
        $status = trim($_POST['status'] ?? 'active');

        if ($staffId === '' || $firstName === '' || $lastName === '' || $position === '' || $email === '') {
            $_SESSION['message'] = 'Please complete all required fields: Staff ID, first name, last name, position and email.';
            $_SESSION['msg_type'] = 'error';
            header('Location: index.php?controller=StaffDirectory&action=edit&id=' . urlencode($staffId));
            exit;
        }

        $staff = $this->directoryModel->getStaffById($staffId);
        if (!$staff) {
            $_SESSION['message'] = 'Staff profile not found.';
            $_SESSION['msg_type'] = 'error';
            header('Location: index.php?controller=StaffDirectory&action=index');
            exit;
        }

        $imageName = $staff['image'] ?? null;
        if (!empty($_FILES['profile_photo']['name']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/uploads/staff_directory';
            if ($uploadDir === false) {
                $uploadDir = dirname(__DIR__, 2) . '/uploads/staff_directory';
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = basename($_FILES['profile_photo']['name']);
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $validExt = ['jpg', 'jpeg', 'png'];
            if (in_array($extension, $validExt, true)) {
                $imageName = preg_replace('/[^a-zA-Z0-9_-]/', '', $staffId) . '_' . time() . '.' . $extension;
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $imageName;
                move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath);
            }
        }

        $data = [
            'staff_id' => $staffId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'position' => $position,
            'department' => $department !== '' ? $department : null,
            'firm' => $firm !== '' ? $firm : null,
            'email' => $email,
            'contact_number' => $contactNumber !== '' ? $contactNumber : null,
            'deployment_date' => $deploymentDate !== '' ? $deploymentDate : null,
            'image' => $imageName,
            'status' => $status !== '' ? $status : 'active',
        ];

        try {
            $success = $this->directoryModel->updateStaff($data);
        } catch (Exception $e) {
            $success = false;
        }

        if ($success) {
            $this->logStaffDirectoryAction('update', $staffId, [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'position' => $position,
                'department' => $department,
                'firm' => $firm,
            ]);
            $_SESSION['message'] = 'Staff profile updated successfully.';
            $_SESSION['msg_type'] = 'success';
            header('Location: index.php?controller=StaffDirectory&action=index');
            exit;
        }

        $_SESSION['message'] = 'Unable to update staff profile. Please try again.';
        $_SESSION['msg_type'] = 'error';
        header('Location: index.php?controller=StaffDirectory&action=edit&id=' . urlencode($staffId));
        exit;
    }

    public function delete($staffId) {
        $this->requireLogin();

        if ($staffId === '') {
            $_SESSION['message'] = 'Invalid staff profile.';
            $_SESSION['msg_type'] = 'error';
            header('Location: index.php?controller=StaffDirectory&action=index');
            exit;
        }

        try {
            $deleted = $this->directoryModel->deleteStaff($staffId);
        } catch (Exception $e) {
            $deleted = false;
        }

        if ($deleted) {
            $this->logStaffDirectoryAction('delete', $staffId, ['message' => 'Staff profile deleted from staff directory']);
            $_SESSION['message'] = 'Staff profile deleted successfully.';
            $_SESSION['msg_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Unable to delete staff profile.';
            $_SESSION['msg_type'] = 'error';
        }

        header('Location: index.php?controller=StaffDirectory&action=index');
        exit;
    }

    public function import() {
        $this->requireLogin();

        $bulkAction = trim($_POST['bulk_action'] ?? 'validate');
        $file = $_FILES['bulk_upload'] ?? null;
        $importErrors = [];
        $importResults = null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $importErrors[] = [
                'row' => '-',
                'field' => 'File',
                'message' => 'Please upload a valid .csv or .xlsx file.',
            ];

            $content = $this->renderView('staff_directory/create', [
                'importErrors' => $importErrors,
                'importResults' => $importResults,
            ]);
            $this->view('layout/main', ['content' => $content]);
            return;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension === 'csv') {
            $rows = $this->parseCsvUpload($file['tmp_name']);
        } elseif ($extension === 'xlsx') {
            $rows = $this->parseXlsxUpload($file['tmp_name']);
        } else {
            $rows = false;
        }

        if ($rows === false) {
            $importErrors[] = [
                'row' => '-',
                'field' => 'File',
                'message' => 'Unable to parse the uploaded file. Use a valid .csv or .xlsx template.',
            ];

            $content = $this->renderView('staff_directory/create', [
                'importErrors' => $importErrors,
                'importResults' => $importResults,
            ]);
            $this->view('layout/main', ['content' => $content]);
            return;
        }

        if (count($rows) < 1) {
            $importErrors[] = [
                'row' => '-',
                'field' => 'File',
                'message' => 'The file does not contain any rows to import.',
            ];
            $content = $this->renderView('staff_directory/create', [
                'importErrors' => $importErrors,
                'importResults' => $importResults,
            ]);
            $this->view('layout/main', ['content' => $content]);
            return;
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $requiredHeaders = ['firstname'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (!array_key_exists($requiredHeader, $headers)) {
                $importErrors[] = [
                    'row' => '-',
                    'field' => 'Header',
                    'message' => sprintf('Missing required column: %s', $requiredHeader),
                ];
            }
        }

        if (!empty($importErrors)) {
            $content = $this->renderView('staff_directory/create', [
                'importErrors' => $importErrors,
                'importResults' => $importResults,
            ]);
            $this->view('layout/main', ['content' => $content]);
            return;
        }

        $existingStaffIds = array_map('strtolower', $this->directoryModel->getStaffIds());
        $seenStaffIds = [];
        $rowNumber = 1;
        $validRows = 0;
        $dataRows = [];
        $invalidRowNames = [];

        foreach ($rows as $row) {
            $rowNumber++;
            $row = array_values(array_map(function ($value) {
                if (!is_string($value)) {
                    return $value;
                }

                return trim(preg_replace('/^\xEF\xBB\xBF/', '', $value));
            }, $row));
            if (count(array_filter($row, fn($value) => $value !== '')) === 0) {
                continue;
            }

            $staffData = $this->buildStaffImportRecord($row, $headers);
            $staffId = $staffData['staff_id'];
            $firstName = $staffData['firstName'];
            $lastName = $staffData['lastName'];
            $position = $staffData['position'];
            $department = $staffData['department'];
            $firm = $staffData['firm'];
            $email = $staffData['email'];
            $contactNumber = $staffData['contact_number'];
            $deploymentDate = $staffData['deployment_date'];

            $rowName = $staffId !== '' ? $staffId : trim(($firstName . ' ' . $lastName));
            if ($rowName === '') {
                $rowName = 'Row ' . $rowNumber;
            }

            $rowErrors = [];
            if ($firstName === '') {
                $rowErrors[] = ['row' => $rowNumber, 'field' => 'First name', 'message' => 'First name is required.', 'name' => $rowName];
            }
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = ['row' => $rowNumber, 'field' => 'Email', 'message' => 'Email address is not valid.', 'name' => $rowName];
            }

            if ($deploymentDate !== '' && strtotime($deploymentDate) === false) {
                $rowErrors[] = ['row' => $rowNumber, 'field' => 'Deployment Date', 'message' => 'Deployment date must be a valid date, like YYYY-MM-DD.', 'name' => $rowName];
            }

            $lowerStaffId = strtolower($staffId);
            if ($staffId !== '') {
                if (in_array($lowerStaffId, $existingStaffIds, true)) {
                    $rowErrors[] = ['row' => $rowNumber, 'field' => 'Staff ID', 'message' => 'Staff ID already exists in the directory.', 'name' => $rowName];
                }
                if (in_array($lowerStaffId, $seenStaffIds, true)) {
                    $rowErrors[] = ['row' => $rowNumber, 'field' => 'Staff ID', 'message' => 'Duplicate Staff ID found in the file.', 'name' => $rowName];
                }
            }

            if ($staffId === '') {
                $staffId = sprintf('imported-%d-%s', $rowNumber, substr(bin2hex(random_bytes(4)), 0, 8));
            }

            if (count($rowErrors) > 0) {
                foreach ($rowErrors as $error) {
                    $importErrors[] = $error;
                }
                if (!in_array($rowName, $invalidRowNames, true)) {
                    $invalidRowNames[] = $rowName;
                }
                continue;
            }

            $validRows++;
            $dataRows[] = [
                'staff_id' => $staffId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'position' => $position,
                'department' => $department !== '' ? $department : '',
                'firm' => $firm !== '' ? $firm : '',
                'email' => $email,
                'contact_number' => $contactNumber !== '' ? $contactNumber : '',
                'deployment_date' => $deploymentDate !== '' ? $deploymentDate : null,
                'image' => null,
                'status' => 'active',
            ];
            $seenStaffIds[] = $lowerStaffId;
        }

        $importResults = [
            'rows' => count($rows),
            'validRows' => $validRows,
            'errors' => count($importErrors),
            'invalidRowNames' => $invalidRowNames,
        ];

        if ($bulkAction === 'import' && count($dataRows) > 0) {
            $created = 0;
            foreach ($dataRows as $dataRow) {
                try {
                    if ($this->directoryModel->createStaff($dataRow)) {
                        $created++;
                    }
                } catch (Exception $e) {
                    $importErrors[] = [
                        'row' => '-',
                        'field' => 'Database',
                        'message' => 'Unable to save one or more rows.',
                        'name' => $dataRow['staff_id'] ?: trim($dataRow['firstName'] . ' ' . $dataRow['lastName']),
                    ];
                }
            }

            $importResults['created'] = $created;
            $importResults['failedRows'] = $invalidRowNames;

            if (empty($importErrors) && $created > 0) {
                $this->logStaffDirectoryAction('import', '', [
                    'message' => sprintf('Imported %d staff profiles successfully.', $created),
                ]);
                $_SESSION['message'] = sprintf('Imported %d staff profiles successfully.', $created);
                $_SESSION['msg_type'] = 'success';
                header('Location: index.php?controller=StaffDirectory&action=index');
                exit;
            }
        }

        $content = $this->renderView('staff_directory/create', [
            'importErrors' => $importErrors,
            'importResults' => $importResults,
        ]);
        $this->view('layout/main', ['content' => $content]);
    }

    private function parseCsvUpload(string $filename): array|false {
        $rows = [];
        if (!is_readable($filename)) {
            return false;
        }

        if (($handle = fopen($filename, 'r')) === false) {
            return false;
        }

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = array_map(function ($value) {
                if (!is_string($value)) {
                    return $value;
                }

                return trim(preg_replace('/^\xEF\xBB\xBF/', '', $value));
            }, $data);
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsxUpload(string $filename): array|false {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($filename) !== true) {
            return false;
        }

        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = $zip->getFromIndex($index);
            if ($xml !== false) {
                $dom = new DOMDocument();
                $dom->loadXML($xml);
                foreach ($dom->getElementsByTagName('si') as $si) {
                    $sharedStrings[] = trim($si->textContent);
                }
            }
        }

        if (($index = $zip->locateName('xl/worksheets/sheet1.xml')) === false) {
            return false;
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();
        if ($xml === false) {
            return false;
        }

        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $rows = [];

        foreach ($dom->getElementsByTagName('row') as $row) {
            $rowValues = [];
            $lastIndex = -1;
            foreach ($row->getElementsByTagName('c') as $cell) {
                $cellRef = $cell->getAttribute('r');
                $cellIndex = $this->columnIndexFromCellReference($cellRef);
                if ($cellIndex === null) {
                    continue;
                }

                while ($lastIndex + 1 < $cellIndex) {
                    $rowValues[] = '';
                    $lastIndex++;
                }

                $cellType = $cell->getAttribute('t');
                $value = '';

                if ($cellType === 's') {
                    $valueNode = $cell->getElementsByTagName('v')->item(0);
                    $numericValue = $valueNode ? $valueNode->nodeValue : '';
                    if (is_numeric($numericValue) && isset($sharedStrings[(int)$numericValue])) {
                        $value = $sharedStrings[(int)$numericValue];
                    }
                } elseif ($cellType === 'inlineStr') {
                    $textNodes = $cell->getElementsByTagName('t');
                    foreach ($textNodes as $textNode) {
                        $value .= $textNode->nodeValue;
                    }
                } else {
                    $valueNode = $cell->getElementsByTagName('v')->item(0);
                    $value = $valueNode ? $valueNode->nodeValue : '';
                }

                $rowValues[$cellIndex] = $value;
                $lastIndex = $cellIndex;
            }
            $rows[] = array_values($rowValues);
        }

        return $rows;
    }

    private function columnIndexFromCellReference(string $cellRef): ?int {
        if ($cellRef === '') {
            return null;
        }

        $columnPart = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        if ($columnPart === '') {
            return null;
        }

        $index = 0;
        $letters = str_split($columnPart);
        foreach ($letters as $letter) {
            $index = $index * 26 + (ord($letter) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function normalizeHeaders(array $headerRow): array {
        $headers = [];
        foreach ($headerRow as $index => $header) {
            $headerKey = trim((string)$header);
            $headerKey = preg_replace('/^\xEF\xBB\xBF/', '', $headerKey) ?? $headerKey;
            if ($headerKey === '') {
                continue;
            }

            $normalizedKey = $this->normalizeHeaderName($headerKey);
            if ($normalizedKey !== '') {
                $headers[$normalizedKey] = $index;
            }

            $headers[strtolower($headerKey)] = $index;
            $headers[strtolower(str_replace([' ', '/'], '', $headerKey))] = $index;
            $headers[strtolower(str_replace([' ', '/', '-', '_', '.'], '', $headerKey))] = $index;
        }
        return $headers;
    }

    private function buildStaffImportRecord(array $row, array $headers): array {
        return [
            'staff_id' => $this->getRowValue($row, $headers, 'staff id', 'staffid'),
            'firstName' => $this->getRowValue($row, $headers, 'first name', 'firstname'),
            'lastName' => $this->getRowValue($row, $headers, 'last name', 'lastname'),
            'position' => $this->getRowValue($row, $headers, 'position'),
            'department' => $this->getRowValue($row, $headers, 'team/discipline', 'teamdiscipline', 'department'),
            'firm' => $this->getRowValue($row, $headers, 'firm'),
            'email' => $this->getRowValue($row, $headers, 'email'),
            'contact_number' => $this->getRowValue($row, $headers, 'contact number', 'contactnumber'),
            'deployment_date' => $this->getRowValue($row, $headers, 'deployment date', 'deploymentdate'),
        ];
    }

    private function getRowValue(array $row, array $headers, string ...$headerNames): string {
        foreach ($headerNames as $headerName) {
            $index = $headers[$headerName] ?? null;
            if ($index === null) {
                $normalizedHeader = $this->normalizeHeaderName($headerName);
                $index = $headers[$normalizedHeader] ?? null;
            }

            if ($index !== null) {
                return trim((string)($row[$index] ?? ''));
            }
        }

        return '';
    }

    private function normalizeHeaderName(string $headerName): string {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($headerName))) ?? '';
    }

    private function getLatestDeploymentDate(array $staffEntries): string
    {
        $dates = array_filter(array_map(function ($item) {
            return $item['deployment_date'] ?? null;
        }, $staffEntries));

        if (empty($dates)) {
            return 'No deployments yet';
        }

        rsort($dates);
        return date('M j, Y', strtotime($dates[0]));
    }
}
