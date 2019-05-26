<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Drivers\Connection;
use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Models\Model;

class Elocrud
{
    private   $only_tables    = [];
    private   $exclude_tables = [];
    private   $force          = false;
    protected $database;

    public function __construct($db_name = null)
    {
        $this->database = !is_string($db_name) ? Connection::currentDatabase() : new Database($db_name);
    }

    public function modelsOutput(\Closure $closure)
    {
        // $closure(new Model(Database::getTable($this->database->name,'subscriptions')));
        foreach ($this->database->tables as $table) {
            if (!$this->allowTable($table->name)) {
                continue;
            }
            $closure(new Model($table));
        }
    }

    public function writeModels()
    {
        $this->extendLaravelModel();
        Helper::makeDir(Config::base_abstract() ? Config::base_dir() : Config::dir_path());
        $this->modelsOutput(function (Model $model) {
            file_put_contents((Config::base_abstract() ? Config::base_dir().'/Base' : Config::dir_path().'/').$model->fileName, (string)$model->toString());
            if (Config::base_abstract() && (true === $this->force || !file_exists(Config::dir_path().'/'.$model->fileName))) {
                file_put_contents(Config::dir_path().'/'.$model->fileName, (string)$model->workingClassText());
            }
        });
    }

    public function extendLaravelModel()
    {
        if (!Config::composite_keys()) {
            return;
        }
        $path      = Config::dir_path().'\Extensions';
        $namespace = Config::namespace().'\\Extensions';
        Helper::makeDir($path);
        $content = file_get_contents(Helper::BASE_DIR.'/stubs/laravel-model.tmpl');
        $content = Helper::replacePlaceholder('namespace', $namespace, $content);
        $content = Helper::replacePlaceholder('name', Config::eloquent_extension_name(), $content);
        $content = Helper::replacePlaceholder('extension', Config::model_class(), $content);
        $content = Helper::replacePlaceholder('extend', Helper::baseName(Config::model_class()), $content);
        file_put_contents($path.'/'.Config::eloquent_extension_name().'.php', $content);
        Config::model_class($namespace.'\\'.Config::eloquent_extension_name());
    }

    protected function allowTable($name)
    {
        return !in_array($name, $this->exclude_tables) && (empty($this->only_tables) || in_array($name, $this->only_tables));
    }

    /**
     * @param array $only_tables
     * @return Elocrud
     */
    public function setOnlyTables(array $only_tables): Elocrud
    {
        $this->only_tables = array_values(array_merge($this->only_tables, $only_tables, Config::only_tables()));;
        return $this;
    }

    /**
     * @param array $exclude_tables
     * @return Elocrud
     */
    public function setExcludeTables(array $exclude_tables): Elocrud
    {
        $this->exclude_tables = array_values(array_merge($this->exclude_tables, $exclude_tables, Config::excluded_tables()));
        return $this;
    }

    /**
     * @param mixed $db_name
     * @return Elocrud
     */
    public function setDbName($db_name)
    {
        $this->database = new Database($db_name);
        return $this;
    }

    /**
     * @param bool $force
     * @return Elocrud
     */
    public function setForce(bool $force): Elocrud
    {
        $this->force = $force;
        return $this;
    }
}