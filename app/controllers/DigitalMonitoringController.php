<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../Services/DigitalMonitoringService.php';

class DigitalMonitoringController extends Controller
{
    private DigitalMonitoringService $monitoringService;

    public function __construct()
    {
        $this->monitoringService = new DigitalMonitoringService();
    }

    public function index(): void
    {
    
        $snapshot = $this->monitoringService->getSnapshot();

        $this->view('digital_monitoring/index', [
            'snapshot' => $snapshot,
            'vehicles' => $snapshot['vehicles'],
            'rooms' => $snapshot['rooms'],
            'workforce' => $snapshot['workforce'],
            'firstName' => $_SESSION['firstName'] ?? $_SESSION['standard_user_name'] ?? 'User',
        ]);
    }

    public function snapshotAjax(): void
    {
        $this->requireLogin();
        $snapshot = $this->monitoringService->getSnapshot();

        $this->jsonResponse([
            'success' => true,
            'message' => 'Digital Monitoring snapshot loaded.',
            'data' => $snapshot,
        ]);
    }
}