<?php

include '../autoload.php';
include '../vendor/autoload.php';

\Angujo\DBReader\Drivers\Config::set('mysql', 'localhost', 3306, 'physiqtone', 'root', 'root');
$crud = new \Angujo\Elocrud\Elocrud('physiqtone');
\Angujo\Elocrud\Config::base_dir(__DIR__.'/output');
echo '<pre>';
$crud->writeModels(__DIR__ . '/output');
