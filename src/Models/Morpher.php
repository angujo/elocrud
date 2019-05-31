<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Helper;

class Morpher
{
    private static $morphs = [];

    protected function __construct()
    {
    }

    public static function fromTable(DBTable $table)
    {
        foreach ($table->columns as $column) {
            if ($column->is_primary || $column->is_auto_increment) {
                continue;
            }
            if (null === ($name = Helper::morphName($column->name))) {
                continue;
            }
            self::$morphs[$table->name][$name][$column->name];
        }
        self::$morphs[$table->name] = array_filter(array_map(function($en){ return count($en) !== 2 ? null : $en; }, self::$morphs[$table->name]));
    }

    public static function create($table_name, $name)
    {
        self::$morphs[$table_name][$name] = [$name.'_id', $name.'_type'];
    }
}