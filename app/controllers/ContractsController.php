<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once '../app/models/Contract.php';


class ContractsController extends Controller {

            private $model;

            public function __construct() {
                $this->model = new ContractModel();
            }

              public function contracts() {
                if (!isset($_SESSION['user'])) {
                    $this->redirect('index.php?controller=Auth&action=login');
                }

                // render contracts list view
                $content = $this->renderView('contracts/index', [
                    'contracts' => $this->model->getAll(),
                ]);

                $this->view('layout/main', [
                    'content' => $content
                ]);
            }
}