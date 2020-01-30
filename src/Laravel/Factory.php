<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:41 PM
 */

namespace Angujo\Elocrud\Laravel;


use Angujo\DBReader\Drivers\Config as DBConfig;
use Angujo\DBReader\Drivers\Connection;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Elocrud;
use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;

class Factory
{
    private $db;
    /** @var Elocrud */
    private $elocrud;

    public function __construct(DatabaseManager $db, array $configs = [])
    {
        $this->db = $db;
        Config::import($configs);
    }

    public function exclude(array $table_names)
    {
        Config::excluded_tables(array_filter($table_names, 'is_string'));
    }

    public function only(array $tables)
    {
        Config::only_tables(array_filter($tables, 'is_string'));
    }

    public function overwrite($force = false)
    {
        Config::overwrite($force);
    }

    public function switchDB($db_name)
    {
        $this->elocrud->setDbName($db_name);
    }

    public function setConnection(ConnectionInterface $connection, $driver)
    {
        $cname = \config('database.default');
        DBConfig::set(\config("database.connections.$cname.driver"), \config("database.connections.$cname.host"),
            \config("database.connections.$cname.port"), \config("database.connections.$cname.database"),
            \config("database.connections.$cname.username"), \config("database.connections.$cname.password"));
        Connection::fromConfig();
        //Connection::setPDO($connection->getPdo(), $driver);
        $this->elocrud = new Elocrud();
    }

    public function processCount()
    {
        return $this->elocrud->modelsCount();
    }

    public function generate(Closure $closure)
    {
        $this->elocrud->writeModels($closure);
    }
}