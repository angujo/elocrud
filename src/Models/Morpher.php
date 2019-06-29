<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Drivers\Connection;
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
                $morph                                  = Morph::create($name, $table->name, $table->schema_name);
                self::$morphs[$morph->getReferenceId()] = $morph;
                unset($tmp[$table->name][$name]);
            }
        }
    }

    public static function create($table_name, $name, $schema_name = null)
    {
        if (!is_string($schema_name)) {
            $schema_name = Connection::currentDatabase(true);
        }
        if (!($table = Schema::getTable($schema_name, $table_name)) || !($column = $table->getColumn($name.'_type'))) {
            return;
        }
        $morph                                  = Morph::create($name, $table->name, $table->schema_name);
        self::$morphs[$morph->getReferenceId()] = $morph;
    }

    /**
     * @param $reference
     *
     * @return Morph|null
     */
    public static function getMorph($reference)
    {
        return array_key_exists($reference, self::$morphs) ? self::$morphs[$reference] : null;
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
        return array_filter(self::$morphs, function($k) use ($schema_name, $table_name){ return 0 === stripos($k, $schema_name.'.'.$table_name); }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param DBTable $table
     *
     * @return MorphItem[]
     */
    public static function getMorphItems(DBTable $table = null): array
    {
        if ($table) {
            return array_filter(self::$morph_items, function(MorphItem $morphItem) use ($table){ return 0 === strcasecmp($table->reference, $morphItem->tableReference()); });
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