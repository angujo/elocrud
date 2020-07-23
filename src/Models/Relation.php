<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\DocInflector;

/**
 * Class Relation
 *
 * @package Angujo\Elocrud\Models
 */
abstract class Relation
{

    /**
     * @var DBTable
     */
    protected $table;

    /**
     * @var Method[]
     */
    protected $methods = [];

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $workspace;

    /**
     * @var string
     */
    protected $basespace;

    /**
     * @return self
     */
    protected abstract function generate();

    /**
     * @param ForeignKey $foreignKey
     *
     * @return Method
     */
    // protected abstract function getMethod(ForeignKey $foreignKey);

    /**
     * HasOneEntry constructor.
     *
     * @param DBTable $table
     * @param string  $namespace
     */
    protected function __construct(DBTable $table, $namespace)
    {
        $this->table     = $table;
        $this->namespace = $namespace;

    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }


    /**
     * @param DBTable $table
     * @param string  $namespace
     *
     * @return Method[]|array
     */
    public static function methods(DBTable $table, $namespace)
    {
        return (new static($table, $namespace))->generate()->getMethods();
    }

    protected function conformValues(DBTable $fTable, ...$cols)
    {
        $vals = [];
        foreach ($cols as $i => $col) {
            $vals[] = $this->columnConforms($col, $fTable) ? null : $col;
        }
        $c = count($vals);
        while ($c) {
            $c--;
            if (null !== ($p = array_pop($vals))) {
                $vals[] = $p;
                break;
            }
        }
        return $vals ? ', \''.implode('\', \'', $vals).'\'' : '';
    }

    /**
     * Method to check if columns are valid relation column names
     *
     * @param DBTable $fTable
     * @param string  ...$cols
     *
     * @return bool
     */
    protected function columnsConform(DBTable $fTable, ...$cols)
    {
        foreach ($cols as $col) {
            if (!$this->columnConforms($col, $fTable)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if the column is a valid relation column name or is primary key
     *
     * @param string  $column_name
     * @param DBTable $fTable
     *
     * @return bool
     */
    protected function columnConforms($column_name, DBTable $fTable)
    {
        return $this->isPrimaryKey($column_name) || $this->isCombinedColumn($column_name, $fTable);
    }

    /**
     * @param $column_name
     *
     * @return bool
     */
    private function isPrimaryKey($column_name)
    {
        return false !== array_search($column_name, array_map(function (DBColumn $column) { return $column->name; }, $this->table->primary_columns));
    }

    /**
     * @param         $column_name
     * @param DBTable $fTable
     *
     * @return bool
     */
    private function isCombinedColumn($column_name, DBTable $fTable)
    {
        if (1 !== count($cols = $this->table->primary_columns)) {
            return false;
        }
        /** @var DBColumn $p */
        $p = array_pop($cols);
        return 0 === strcasecmp($column_name, DocInflector::singularize($fTable->name).'_'.$p->name);
    }
}