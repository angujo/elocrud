<?php

include '../autoload.php';
include '../vendor/autoload.php';

\Angujo\DBReader\Drivers\Config::set('pgsql', 'localhost', 5432, 'dvdrental', 'postgres', 'postgres');
//\Angujo\DBReader\Drivers\Config::set('mysql', 'localhost', 3306, 'test', 'root', 'root');
$crud = new \Angujo\Elocrud\Elocrud('test');
\Angujo\Elocrud\Config::dir_path(__DIR__.'/output');
echo '<pre>';
$crud->writeModels(__DIR__ . '/output');
