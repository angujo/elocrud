<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Drivers\Connection;
use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\DBReader\Models\Schema;
use Angujo\Elocrud\Helper;

class Morpher
{
    /**
     * @var Morph[]
     */
    private static $morphs = [];
    /**
     * @var MorphItem[]
     */
    private static $morph_items = [];

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
                        self::$morphs[$table->schema_name.'.'.$table->name] = Morph::fromColumn($table->getColumn($col_name));
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
        if (!($table = Schema::getTable($schema_name, $table_name)) || !($column = $table->getColumn($name))) {
            return;
        }
        self::$morphs[$schema_name.'.'.$table_name][$name] = Morph::fromColumn($column);
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
     * @param DBTable $table
     *
     * @return MorphItem[]
     */
    public static function getMorphItems(DBTable $table = null): array
    {
        if ($table) {
            return array_filter(self::$morph_items, function(MorphItem $morphItem) use ($table){ return 0 === strcasecmp($table->schema_naming, $morphItem->tableReference()); });
        }
        return self::$morph_items;
    }

    public static function setMorphItem(MorphItem $morphItem)
    {
        self::$morph_items[] = $morphItem;
    }

    /**
     * @param MorphItem[] $morph_items
     */
    public static function setMorphItems(array $morph_items): void
    {
        self::$morph_items = $morph_items;
    }


}