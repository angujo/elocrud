<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Drivers\Connection;
use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\Schema;
use Angujo\Elocrud\Models\ManyToMany;
use Angujo\Elocrud\Models\Model;
use Angujo\Elocrud\Models\Morpher;
use Closure;

class Elocrud
{
    protected $database;

    public function __construct($db_name = null)
    {
        $this->database = !is_string($db_name) ? Connection::currentDatabase() : new Database($db_name);
    }

    public function modelsCount()
    {
        return array_sum(array_map(function(Schema $schema){ return count($schema->tables); }, $this->database->schemas));
    }

    public function modelsOutput(Closure $closure)
    {
        foreach ($this->database->schemas as $schema) {
            foreach ($schema->tables as $table) {
                if (!$this->allowTable($table->name)) {
                    continue;
                }
                Morpher::fromTable($table);
                ManyToMany::checkLoadTable($table);
            }
            foreach ($schema->tables as $table) {
                if (!$this->allowTable($table->name)) {
                    continue;
                }
                $closure(new Model($table));
            }
        }
    }

    /**
     * @param null|Closure $closure
     */
    public function writeModels($closure = null)
    {
        $this->extendLaravelModel();
        Helper::makeDir(Config::base_abstract() ? Config::base_dir() : Config::dir_path());
        $this->modelsOutput(function(Model $model) use ($closure){
            if (Config::base_abstract() || (!Config::base_abstract() && Config::overwrite())) {
                file_put_contents((Config::base_abstract() ? Config::base_dir().'/Base' : Config::dir_path().'/').$model->getFileName(), (string)$model->toString());
            }
            if (Config::base_abstract() && (Config::overwrite() || !file_exists(Config::dir_path().'/'.$model->getFileName()))) {
                file_put_contents(Config::dir_path().'/'.$model->getFileName(), (string)$model->workingClassText());
            }
            if ($closure && is_callable($closure)) {
                $closure($model);
            }
            Morpher::addMap($model->getTable()->name, Config::namespace().'\\'.$model->getClassName());
        });
        Morpher::setMaps();
    }

    protected function extendLaravelModel()
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
        return !in_array($name, Config::excluded_tables()) && (empty(Config::only_tables()) || in_array($name, Config::only_tables()));
    }

    /**
     * @param mixed $db_name
     *
     * @return Elocrud
     */
    public function setDbName($db_name)
    {
        $this->database = new Database($db_name);
        return $this;
    }
}