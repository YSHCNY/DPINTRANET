<?php

namespace App\Services;

require_once __DIR__ . '/../models/UserModel.php';

use \UserModel;

class StandardUserImportService
{
    private const REQUIRED_HEADERS = [
        'username',
        'password',
        'firstname',
        'lastname',
        'email',
        'position',
        'department',
        'pincode',
    ];

    private UserModel $userModel;

    public function __construct(UserModel $userModel = null)
    {
        $this->userModel = $userModel ?? new \UserModel();
    }

    public function validateUploadedFile(array $file): array
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $rows = $this->parseUploadedFile($file['tmp_name'], $extension);

        if ($rows === false) {
            return [
                'total_rows' => 0,
                'valid_rows' => 0,
                'failed_count' => 0,
                'failed_rows' => [],
                'errors' => [
                    ['row_number' => '-', 'username' => null, 'errors' => ['Unable to parse the uploaded file. Use a valid .csv or .xlsx template.']],
                ],
            ];
        }

        if (count($rows) < 1) {
            return [
                'total_rows' => 0,
                'valid_rows' => 0,
                'failed_count' => 0,
                'failed_rows' => [],
                'errors' => [
                    ['row_number' => '-', 'username' => null, 'errors' => ['The file does not contain any rows to import.']],
                ],
            ];
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $missingHeaders = $this->validateHeaders($headers);
        if (!empty($missingHeaders)) {
            return [
                'total_rows' => count($rows),
                'valid_rows' => 0,
                'failed_count' => 0,
                'failed_rows' => [],
                'errors' => [
                    ['row_number' => '-', 'username' => null, 'errors' => $missingHeaders],
                ],
            ];
        }

        $rowRecords = [];
        $totalRows = 0;
        $seenUsernames = [];
        $seenEmails = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $row = array_map('trim', $row);
            if ($this->isRowEmpty($row)) {
                continue;
            }

            $totalRows++;
            $username = $this->getRowValue($row, $headers, 'username');
            $password = $this->getRowValue($row, $headers, 'password');
            $firstName = $this->getRowValue($row, $headers, 'firstName', 'firstname');
            $lastName = $this->getRowValue($row, $headers, 'lastName', 'lastname');
            $email = $this->getRowValue($row, $headers, 'email');
            $position = $this->getRowValue($row, $headers, 'position');
            $department = $this->getRowValue($row, $headers, 'department');
            $pinCode = $this->getRowValue($row, $headers, 'pin_code', 'pincode', 'pin code');

            $rowErrors = [];
            $normalizedUsername = strtolower($username);
            $normalizedEmail = strtolower($email);

            if ($username === '') {
                $rowErrors[] = 'Username is required.';
            }
            if ($password === '') {
                $rowErrors[] = 'Password is required.';
            }
            if ($firstName === '') {
                $rowErrors[] = 'First name is required.';
            }
            if ($lastName === '') {
                $rowErrors[] = 'Last name is required.';
            }
            if ($email === '') {
                $rowErrors[] = 'Email is required.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Email format is invalid.';
            }
            if ($position === '') {
                $rowErrors[] = 'Position is required.';
            }
            if ($department === '') {
                $rowErrors[] = 'Department is required.';
            }
            if ($pinCode === '') {
                $rowErrors[] = 'PIN code is required.';
            }

            if ($normalizedUsername !== '') {
                if (isset($seenUsernames[$normalizedUsername])) {
                    $rowErrors[] = 'Duplicate username found in file.';
                }
                $seenUsernames[$normalizedUsername] = true;
            }

            if ($normalizedEmail !== '') {
                if (isset($seenEmails[$normalizedEmail])) {
                    $rowErrors[] = 'Duplicate email found in file.';
                }
                $seenEmails[$normalizedEmail] = true;
            }

            $rowRecords[] = [
                'row_number' => $rowNumber,
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'position' => $position,
                'department' => $department,
                'pin_code' => $pinCode,
                'errors' => $rowErrors,
            ];
        }

        $existingUsernames = $this->userModel->findExistingUsernames(array_filter(array_map('strtolower', array_column($rowRecords, 'username'))));
        $existingEmails = $this->userModel->findExistingEmails(array_filter(array_map('strtolower', array_column($rowRecords, 'email'))));

        $validRows = [];
        $failedRows = [];

        foreach ($rowRecords as $record) {
            if ($record['username'] !== '' && in_array(strtolower($record['username']), $existingUsernames, true)) {
                $record['errors'][] = 'Username already exists in the system.';
            }
            if ($record['email'] !== '' && in_array(strtolower($record['email']), $existingEmails, true)) {
                $record['errors'][] = 'Email already exists in the system.';
            }

            if (!empty($record['errors'])) {
                $failedRows[] = [
                    'row_number' => $record['row_number'],
                    'username' => $record['username'] ?: null,
                    'errors' => $record['errors'],
                ];
                continue;
            }

            $validRows[] = [
                'username' => $record['username'],
                'password' => password_hash($record['password'], PASSWORD_DEFAULT),
                'firstName' => $record['firstName'],
                'lastName' => $record['lastName'],
                'middleName' => '',
                'email' => $record['email'],
                'phone' => '',
                'position' => $record['position'],
                'department' => $record['department'],
                'role' => 'portal_user',
                'status' => 'active',
                'is_portal_user' => 1,
                'pin_code' => $record['pin_code'],
                'avatar' => null,
            ];
        }

        return [
            'total_rows' => $totalRows,
            'valid_rows' => count($validRows),
            'failed_count' => count($failedRows),
            'failed_rows' => $failedRows,
            'valid_rows_data' => $validRows,
        ];
    }

    public function importUploadedFile(array $file): array
    {
        $validationResult = $this->validateUploadedFile($file);

        if (!empty($validationResult['errors'])) {
            return [
                'success_count' => 0,
                'failed_count' => $validationResult['failed_count'],
                'inserted_rows' => 0,
                'failed_rows' => $validationResult['failed_rows'],
                'total_rows' => $validationResult['total_rows'],
                'errors' => $validationResult['errors'],
            ];
        }

        $insertedCount = 0;
        $connection = $this->userModel->getConnection();

        try {
            $connection->beginTransaction();

            foreach ($validationResult['valid_rows_data'] as $rowData) {
                $this->userModel->insertStandardUser($rowData);
                $insertedCount++;
            }

            $connection->commit();
        } catch (\Exception $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            return [
                'success_count' => 0,
                'failed_count' => $validationResult['failed_count'],
                'inserted_rows' => 0,
                'failed_rows' => $validationResult['failed_rows'],
                'total_rows' => $validationResult['total_rows'],
                'errors' => [
                    ['row_number' => '-', 'username' => null, 'errors' => ['Unexpected database error during import.']],
                ],
            ];
        }

        return [
            'success_count' => $insertedCount,
            'failed_count' => $validationResult['failed_count'],
            'inserted_rows' => $insertedCount,
            'failed_rows' => $validationResult['failed_rows'],
            'total_rows' => $validationResult['total_rows'],
        ];
    }

    private function validateHeaders(array $headers): array
    {
        $errors = [];
        foreach (self::REQUIRED_HEADERS as $header) {
            if (!array_key_exists($header, $headers)) {
                $errors[] = sprintf('Missing required column: %s.', $header);
            }
        }
        return $errors;
    }

    private function parseUploadedFile(string $filename, string $extension): array|false
    {
        if (!is_readable($filename)) {
            return false;
        }

        if ($extension === 'csv') {
            return $this->parseCsvUpload($filename);
        }

        if ($extension === 'xlsx') {
            return $this->parseXlsxUpload($filename);
        }

        return false;
    }

    private function parseCsvUpload(string $filename): array|false
    {
        $rows = [];
        if (($handle = fopen($filename, 'r')) === false) {
            return false;
        }

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $data;
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsxUpload(string $filename): array|false
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filename) !== true) {
            return false;
        }

        $sharedStrings = [];
        $index = $zip->locateName('xl/sharedStrings.xml');
        if ($index !== false) {
            $xml = $zip->getFromIndex($index);
            if ($xml !== false) {
                $dom = new \DOMDocument();
                $dom->loadXML($xml);
                foreach ($dom->getElementsByTagName('si') as $si) {
                    $sharedStrings[] = trim($si->textContent);
                }
            }
        }

        $index = $zip->locateName('xl/worksheets/sheet1.xml');
        if ($index === false) {
            $zip->close();
            return false;
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();
        if ($xml === false) {
            return false;
        }

        $dom = new \DOMDocument();
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
                $valueNode = $cell->getElementsByTagName('v')->item(0);
                $value = $valueNode ? $valueNode->nodeValue : '';

                if ($cellType === 's' && is_numeric($value) && isset($sharedStrings[(int) $value])) {
                    $value = $sharedStrings[(int) $value];
                }

                $rowValues[$cellIndex] = $value;
                $lastIndex = $cellIndex;
            }
            $rows[] = $rowValues;
        }

        return $rows;
    }

    private function columnIndexFromCellReference(string $cellRef): ?int
    {
        $columnPart = preg_replace('/[^A-Z]/', '', strtoupper($cellRef));
        if ($columnPart === '') {
            return null;
        }

        $index = 0;
        foreach (str_split($columnPart) as $letter) {
            $index = $index * 26 + (ord($letter) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function normalizeHeaders(array $headerRow): array
    {
        $headers = [];
        foreach ($headerRow as $index => $header) {
            $normalized = $this->normalizeHeaderName((string) $header);
            if ($normalized !== '') {
                $headers[$normalized] = $index;
            }
        }
        return $headers;
    }

    private function getRowValue(array $row, array $headers, string ...$headerNames): string
    {
        foreach ($headerNames as $headerName) {
            $normalized = $this->normalizeHeaderName($headerName);
            if (isset($headers[$normalized])) {
                return trim((string) ($row[$headers[$normalized]] ?? ''));
            }
        }
        return '';
    }

    private function normalizeHeaderName(string $headerName): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($headerName))) ?? '';
    }

    private function isRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }
}
