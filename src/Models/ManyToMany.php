<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-06-16
 * Time: 5:06 AM
 */

namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBTable;
use Angujo\DBReader\Models\ForeignKey;

class ManyToMany
{
    /**
     * @var self[]
     */
    private static $relations = [];

    /**
     * Name of the joining table
     *
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $table_name;
    /**
     * @var string
     */
    private $schema_name;
    /**
     * @var string
     */
    private $column_name;
    /**
     * @var string
     */
    private $ref_column_name;
    /**
     * @var string
     */
    private $ref_table_name;
    /**
     * @var string
     */
    private $ref_schema_name;

    /**
     * @var DBTable
     */
    private $table;

    protected function __construct($name, DBTable $table, $table_name = null, $schema_name = null)
    {
        $this->table       = $table;
        $this->name        = $name;
        $this->table_name  = $table->name;
        $this->schema_name = $table->schema_name;
    }

    public static function checkLoadTable(DBTable $table)
    {
        if (!self::isManyToMany($table)) {
            return;
        }
        $keys = $table->foreign_keys_one_to_one;
        foreach ($keys as $foreignKey) {
            $me = new self($table->name,$table);
            self::setColumnDetails($me, $foreignKey, true);
            $me->column_name    = $foreignKey->column_name;
            $other_foreign_keys = array_filter($table->foreign_keys_one_to_one, function (ForeignKey $key) use ($foreignKey) {
                return 0 !== strcasecmp($foreignKey->column_reference, $key->column_reference);
            });
            foreach ($other_foreign_keys as $other_foreign_key) {
                $nme                  = clone $me;
                $nme->ref_column_name = $other_foreign_key->column_name;
                self::setColumnDetails($nme, $other_foreign_key);
                self::$relations[] = $nme;
            }
        }
    }

    /**
     * Check if table is valid for Many To Many Relation
     *
     * @param DBTable $table
     *
     * @return bool
     */
    public static function isManyToMany(DBTable $table)
    {
        return (count($table->columns) >= 2 && count($table->foreign_keys_one_to_one) === 2);
    }

    /**
     * @param self       $me
     * @param ForeignKey $foreignKey
     * @param bool       $c
     */
    private static function setColumnDetails(ManyToMany $me, ForeignKey $foreignKey, $c = false)
    {
        if ($c) {
            $me->table_name  = $foreignKey->foreign_table_name;
            $me->schema_name = $foreignKey->foreign_schema_name;
        } else {
            $me->ref_table_name  = $foreignKey->foreign_table_name;
            $me->ref_schema_name = $foreignKey->foreign_schema_name;
        }
    }

    /**
     * @param DBTable $table
     *
     * @return ManyToMany[]|array
     */
    public static function getManyRelations(DBTable $table)
    {
        return array_filter(self::$relations, function (self $self) use ($table) { return 0 === strcasecmp($table->reference, $self->getReference()); });
    }

    public function getReference()
    {
        return implode('.', [$this->schema_name, $this->table_name]);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @return null
     */
    public function getSchemaName()
    {
        return $this->schema_name;
    }

    /**
     * @return mixed
     */
    public function getColumnName()
    {
        return $this->column_name;
    }

    /**
     * @return mixed
     */
    public function getRefColumnName()
    {
        return $this->ref_column_name;
    }

    /**
     * @return mixed
     */
    public function getRefTableName()
    {
        return $this->ref_table_name;
    }

    /**
     * @return mixed
     */
    public function getRefSchemaName()
    {
        return $this->ref_schema_name;
    }

    /**
     * @return self[]
     */
    public static function getRelations(): array
    {
        return self::$relations;
    }

    /**
     * @return DBTable
     */
    public function getTable(): DBTable
    {
        return $this->table;
    }
}