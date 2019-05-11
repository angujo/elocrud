<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Drivers\Connection;
use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Models\Model;

class Elocrud
{
    protected $database;

    public function __construct($db_name = null)
    {
        $this->database = !is_string($db_name) ? Connection::currentDatabase() : new Database($db_name);
    }

    public function modelsOutput(\Closure $closure)
    {
        $this->database->tables->each(function (DBTable $table) use ($closure) {
            $closure(new Model($table));
        });
    }

    public function writeModels($path)
    {
        $path = trim($path, "\\/");
        if (!file_exists($path) && !is_dir($path)) mkdir($path);
        $this->modelsOutput(function (Model $model) use ($path) {
            file_put_contents($path . '/' . $model->fileName, (string)$model);
        });
    }
}