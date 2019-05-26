<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:41 PM
 */

namespace Angujo\Elocrud\Laravel;


use Angujo\Elocrud\Elocrud;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;

class Factory
{
    private $db;
    private $configs = [];
    /** @var Elocrud */
    private $elocrud;
    private $schemas = [];

    public function __construct(DatabaseManager $db, array $configs = [])
    {
        $this->db      = $db;
        $this->configs = $configs;
    }

    public function exclude(array $table_names)
    {
        $this->elocrud->setExcludeTables($table_names);
    }

    public function only(array $tables)
    {
        $this->elocrud->setOnlyTables($tables);
    }

    public function overwrite($force = false)
    {
        $this->elocrud->setForce($force);
    }

    public function switchDB($db_name)
    {
        $this->elocrud->setDbName($db_name);
    }

    public function setConnection(ConnectionInterface $connection,$driver)
    {
        \Angujo\DBReader\Drivers\Connection::setPDO($connection->getPdo(),$driver);
        $this->elocrud = new Elocrud();
    }

    public function generate()
    {

    }
}