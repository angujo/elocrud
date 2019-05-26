<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:41 PM
 */

namespace Angujo\Elocrud\Laravel;


use Illuminate\Database\DatabaseManager;

class Factory
{
    private $db;
    private $configs = [];
    private $elocrud;
    private $schemas = [];

    public function __construct(DatabaseManager $db, array $configs = [])
    {
        $this->db = $db;
        $this->configs = $configs;
    }

    protected function exclude($table_name)
    {

    }
}