<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Drivers\Connection;
use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Helper;

class Morpher
{
    /**
     * @var Morph[]
     */
    private static $morphs = [];

    protected function __construct()
    {
    }

    public static function fromTable(DBTable $table)
    {
        $tmp = [];
        foreach ($table->columns as $column) {
            if ($column->is_primary || $column->is_auto_increment || null === ($name = Helper::morphName($column->name))) {
                continue;
            }
            $tmp[$table->name][$name][] = $column->name;
            if (count($tmp[$table->name][$name]) === 2) {
                foreach ($tmp[$table->name][$name] as $col_name) {
                    if (Helper::isMorphType($col_name)) {
                        self::$morphs[$table->schema_name.'.'.$table->name] = new Morph($table->getColumn($col_name));
                        break;
                    }
                }
                unset($tmp[$table->name][$name]);
            }
        }
    }

    public static function create($table_name, $name, $schema_name = null)
    {
        if (!is_string($schema_name)) {
            $schema_name = Connection::currentDatabase(true);
        }
        if (!($table = Database::getTable($schema_name, $table_name)) || !($column = $table->getColumn($name))) {
            return;
        }
        self::$morphs[$schema_name.'.'.$table_name][$name] = new Morph($column);
    }

    /**
     * For tables that are morphed to
     * Having the morphing item columns
     *
     * @param      $table_name
     * @param null $schema_name
     *
     * @return Morph[]
     */
    public static function getTableMorphs($table_name, $schema_name = null)
    {
        if (!$schema_name) {
            $schema_name = Connection::currentDatabase(true);
        }
        return array_filter(self::$morphs, function($k) use ($schema_name, $table_name){ return 0 === strcasecmp($k, $schema_name.'.'.$table_name); }, ARRAY_FILTER_USE_KEY);
    }

    /**
     *
     * @param string      $table_name
     * @param string|null $schema_name
     *
     * @return Morph[]
     */
    public static function getTableMorphsReference($table_name, $schema_name = null)
    {
        if (!$schema_name) {
            $schema_name = Connection::currentDatabase(true);
        }
        return array_filter(self::$morphs, function(Morph $morph) use ($schema_name, $table_name){
            return count(array_filter($morph->getItems(), function(MorphItem $morphItem) use ($schema_name, $table_name){
                return 0 === strcasecmp($morphItem->getSchemaName().'.'.$morphItem->getTableName(), $schema_name.'.'.$table_name);
            })) ? $morph : null;
        });
    }
}