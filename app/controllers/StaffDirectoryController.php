<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/StaffDirectoryModel.php';

class StaffDirectoryController extends Controller {
    private StaffDirectoryModel $directoryModel;

    public function __construct() {
        $this->directoryModel = new StaffDirectoryModel();
    }

    public function index() {
        $this->requireLogin();

        $staffEntries = $this->directoryModel->getAllStaff();
        $departments = array_values(array_filter(array_unique(array_map(function ($item) {
            return trim($item['department'] ?? '');
        }, $staffEntries))));
        sort($departments, SORT_NATURAL | SORT_FLAG_CASE);

        $positions = array_values(array_filter(array_unique(array_map(function ($item) {
            return trim($item['position'] ?? '');
        }, $staffEntries))));
        sort($positions, SORT_NATURAL | SORT_FLAG_CASE);

        $metrics = [
            'totalStaff' => count($staffEntries),
            'activeStaff' => count(array_filter($staffEntries, function ($item) {
                return ($item['status'] ?? '') === 'active';
            })),
            'departmentCount' => count($departments),
            'recentDeployment' => $this->getLatestDeploymentDate($staffEntries),
        ];

        $content = $this->renderView('staff_directory/index', [
            'staffEntries' => $staffEntries,
            'departments' => $departments,
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
            $uploadDir = realpath(__DIR__ . '/../../uploads/staff_directory');
            if ($uploadDir === false) {
                $uploadDir = __DIR__ . '/../../uploads/staff_directory';
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
            $uploadDir = realpath(__DIR__ . '/../../uploads/staff_directory');
            if ($uploadDir === false) {
                $uploadDir = __DIR__ . '/../../uploads/staff_directory';
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
        $requiredHeaders = ['staff id', 'firstname', 'lastname', 'position', 'email'];
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

        foreach ($rows as $row) {
            $rowNumber++;
            $row = array_map('trim', $row);
            if (count(array_filter($row, fn($value) => $value !== '')) === 0) {
                continue;
            }

            $staffId = $this->getRowValue($row, $headers, 'staff id');
            $firstName = $this->getRowValue($row, $headers, 'firstname');
            $lastName = $this->getRowValue($row, $headers, 'lastname');
            $position = $this->getRowValue($row, $headers, 'position');
            $department = $this->getRowValue($row, $headers, 'department');
            $email = $this->getRowValue($row, $headers, 'email');
            $contactNumber = $this->getRowValue($row, $headers, 'contact number');
            $deploymentDate = $this->getRowValue($row, $headers, 'deployment date');

            if ($staffId === '') {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Staff ID', 'message' => 'Staff ID is required.'];
            }
            if ($firstName === '') {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'First name', 'message' => 'First name is required.'];
            }
            if ($lastName === '') {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Last name', 'message' => 'Last name is required.'];
            }
            if ($position === '') {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Position', 'message' => 'Position is required.'];
            }
            if ($email === '') {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Email', 'message' => 'Email is required.'];
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Email', 'message' => 'Email address is not valid.'];
            }

            if ($deploymentDate !== '' && strtotime($deploymentDate) === false) {
                $importErrors[] = ['row' => $rowNumber, 'field' => 'Deployment Date', 'message' => 'Deployment date must be a valid date, like YYYY-MM-DD.'];
            }

            $lowerStaffId = strtolower($staffId);
            if ($staffId !== '') {
                if (in_array($lowerStaffId, $existingStaffIds, true)) {
                    $importErrors[] = ['row' => $rowNumber, 'field' => 'Staff ID', 'message' => 'Staff ID already exists in the directory.'];
                }
                if (in_array($lowerStaffId, $seenStaffIds, true)) {
                    $importErrors[] = ['row' => $rowNumber, 'field' => 'Staff ID', 'message' => 'Duplicate Staff ID found in the file.'];
                }
            }

            if (count(array_filter($importErrors, fn($error) => $error['row'] === $rowNumber)) === 0) {
                $validRows++;
                $dataRows[] = [
                    'staff_id' => $staffId,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'position' => $position,
                    'department' => $department !== '' ? $department : null,
                    'email' => $email,
                    'contact_number' => $contactNumber !== '' ? $contactNumber : null,
                    'deployment_date' => $deploymentDate !== '' ? $deploymentDate : null,
                    'image' => null,
                    'status' => 'active',
                ];
                $seenStaffIds[] = $lowerStaffId;
            }
        }

        $importResults = [
            'rows' => count($rows),
            'validRows' => $validRows,
            'errors' => count($importErrors),
        ];

        if ($bulkAction === 'import' && empty($importErrors)) {
            $created = 0;
            foreach ($dataRows as $dataRow) {
                try {
                    if ($this->directoryModel->createStaff($dataRow)) {
                        $created++;
                    }
                } catch (Exception $e) {
                    $importErrors[] = ['row' => '-', 'field' => 'Database', 'message' => 'Unable to save one or more rows.'];
                    break;
                }
            }

            if (empty($importErrors) && $created > 0) {
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
            $rows[] = $data;
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
            foreach ($row->getElementsByTagName('c') as $cell) {
                $cellType = $cell->getAttribute('t');
                $valueNode = $cell->getElementsByTagName('v')->item(0);
                $value = $valueNode ? $valueNode->nodeValue : '';

                if ($cellType === 's' && is_numeric($value) && isset($sharedStrings[(int)$value])) {
                    $value = $sharedStrings[(int)$value];
                }

                $rowValues[] = $value;
            }
            $rows[] = $rowValues;
        }

        return $rows;
    }

    private function normalizeHeaders(array $headerRow): array {
        $headers = [];
        foreach ($headerRow as $index => $header) {
            $headerKey = trim((string)$header);
            $headers[strtolower($headerKey)] = $index;
        }
        return $headers;
    }

    private function getRowValue(array $row, array $headers, string $headerName): string {
        $index = $headers[$headerName] ?? null;
        if ($index === null) {
            return '';
        }
        return trim($row[$index] ?? '');
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
