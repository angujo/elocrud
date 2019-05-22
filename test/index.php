<?php

include '../autoload.php';
include '../vendor/autoload.php';

\Angujo\DBReader\Drivers\Config::set('mysql', 'localhost', 3306, 'test', 'root', 'root');
$crud = new \Angujo\Elocrud\Elocrud('test');
\Angujo\Elocrud\Config::base_dir('D:\www\elocrud\test\output');
echo ' < pre>';
$crud->writeModels(__DIR__ . ' / output');
