<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\Database;
use Angujo\DBReader\Models\Schema;
use Exception;

class MorphItem
{
    private $table_name;
    private $schema_name;
    private $column_name;
    private $one_to_one_relation = false;
    private $morph;
    /**
     * @var bool
     */
    private $one_to_many_relation = true;
    /**
     * @var bool
     */
    private $many_to_many_relation = false;

    /**
     * MorphItem constructor.
     *
     * @param Morph $morph
     * @param array $details
     *
     * @throws Exception
     */
    protected function __construct(Morph $morph, array $details)
    {
        if (empty($details)) {
            throw new Exception('Morph details should at least contain and start with table name!');
        }
        $f                 = array_shift($details);
        $this->schema_name = preg_replace(["/\)(.*?)$/i", "/^\(/i"], '', $f);
        $this->table_name  = preg_replace("/^\((.*?)\)/i", '', $f);
        if (!is_string($this->table_name) || 1 !== preg_match('/^[a-z]/i', $this->table_name) || null == Schema::getTable($this->schema_name, $this->table_name)) {
            throw new Exception('Invalid or missing table name!');
        }
        $this->morph = $morph;
        foreach ($details as $detail) {
            if (in_array($detail, ['1-1', '1-0', '0-0'])) {
                $this->one_to_one_relation   = 0 === strcasecmp('1-1', $detail);
                $this->one_to_many_relation  = 0 === strcasecmp('1-0', $detail);
                $this->many_to_many_relation = 0 === strcasecmp('0-0', $detail);
            } elseif (null === $this->column_name && is_string($detail) && 1 === preg_match('/^[a-z]/i', $detail)) {
                $this->column_name = $detail;
            }
        }
        if (!$this->column_name && count($this->getTable()->primary_columns) !== 1) {
            throw new Exception("The table {$this->table_name} contains composite primary keys!");
        }
    }

    /**
     * @param       $schema_name
     * @param array $details
     *
     * @return MorphItem|null
     */
    public static function commentDefinition(Morph $morph, array $details, $column_name)
    {
        $me = null;
        try {
            $me = new self($morph, $details);
        } catch (\Throwable $exception) {
            $me = null;
        } finally {
            return $me;
        }
    }

    public function getTable()
    {
        return Schema::getTable($this->schema_name, $this->table_name);
    }

    /**
     * @return mixed
     */
    public function getSchemaName()
    {
        return $this->schema_name;
    }


    /**
     * @return mixed
     */
    public function getTableName()
    {
        return $this->table_name;
    }

    /**
     * @return mixed
     */
    public function getColumnName()
    {
        if (null === $this->column_name) {
            $this->column_name = $this->getTable()->primary_columns[0];
        }
        return $this->column_name;
    }

    /**
     * @return bool
     */
    public function isOneToOneRelation(): bool
    {
        return $this->one_to_one_relation;
    }

    /**
     * @return bool
     */
    public function isOneToManyRelation(): bool
    {
        return $this->one_to_many_relation;
    }

    /**
     * @return bool
     */
    public function isManyToManyRelation(): bool
    {
        return $this->many_to_many_relation;
    }

    /**
     * @return Morph
     */
    public function getMorph(): Morph
    {
        return $this->morph;
    }

    public function tableReference()
    {
        return $this->schema_name.'.'.$this->table_name;
    }

}