<?php

include '../autoload.php';
include '../vendor/autoload.php';

include 'connect.php';

$crud = new \Angujo\Elocrud\Elocrud();
\Angujo\Elocrud\Config::dir_path(__DIR__.'/output');
//\Angujo\Elocrud\Config::only_tables(['admins']);
echo '<pre>';
$crud->writeModels(__DIR__ . '/output');
