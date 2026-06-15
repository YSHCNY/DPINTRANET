<?php
session_start();
require_once '../app/core/Controller.php';
require_once '../app/models/User.php';
require_once "../app/models/Syslogs.php";



class SyslogsController extends Controller {


            private $model;

            public function __construct() {
                $this->model = new SyslogsModel();
            }

              public function syslogs() {
                $this->requireAnyRole([0], 'Only Super Admin can access System Logs.');


                // render syslogs list view
                $content = $this->renderView('syslogs/index', [
                    'syslogs' => $this->model->getAllData(),
              
                ]);


                
                $this->view('layout/main', [
                    'content' => $content
                ]);

                
              
            }

}
