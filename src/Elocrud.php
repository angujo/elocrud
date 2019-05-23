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
        $closure(new Model(Database::getTable($this->database->name,'subscriptions')));
        // $this->database->tables->each(function (DBTable $table) use ($closure) {
        //     $closure(new Model($table));
        // });
    }

    public function writeModels()
    {
        $this->extendLaravelModel();
        Helper::makeDir(Config::base_dir());
        $this->modelsOutput(function (Model $model) {
            file_put_contents(Config::base_dir() . '/' . $model->fileName, (string)$model);
        });
    }

    public function extendLaravelModel()
    {
        if (!Config::composite_keys()) return;
        $path = Config::base_dir() . '\Extensions';
        $namespace = Config::namespace() . '\\Extensions';
        Helper::makeDir($path);
        $content = file_get_contents(Helper::BASE_DIR . '/stubs/laravel-model.tmpl');
        $content = Helper::replacePlaceholder('namespace', $namespace, $content);
        $content = Helper::replacePlaceholder('name', Config::eloquent_extension_name(), $content);
        $content = Helper::replacePlaceholder('extension', Config::model_class(), $content);
        $content = Helper::replacePlaceholder('extend', Helper::baseName(Config::model_class()), $content);
        file_put_contents($path . '/' . Config::eloquent_extension_name() . '.php', $content);
        Config::model_class($namespace . '\\' . Config::eloquent_extension_name());
    }
}