<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\Schema;

class MorphItem
{
    /** @var boolean */
    private $by=false;
    /** @var string */
    private $reference_table_name;
    /** @var string|string[]|null */
    private $table_name;
    /** @var string|string[]|null */
    private $schema_name;
    /** @var string */
    private $column_name;
    /** @var bool */
    private $one_to_one_relation = false;
    /** @var string */
    private $morph;
    /** @var bool */
    private $one_to_many_relation = true;
    /** @var bool */
    private $many_to_many_relation = false;

    /**
     * MorphItem constructor.
     *
     * @param      $table_name
     * @param      $column_name
     * @param null $schema_name
     */
    protected function __construct($table_name, $column_name, $schema_name = null)
    {
        $this->table_name  = $table_name;
        $this->column_name = $column_name;
        $this->schema_name = $schema_name;
    }

    /**
     * @param      $table_name
     * @param      $column_name
     * @param null $schema_name
     *
     * @return MorphItem
     */
    public static function create($table_name, $column_name, $schema_name = null)
    {
        return new self($table_name, $column_name, $schema_name);
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
     * @return Morph|null
     */
    public function getMorph(): ?Morph
    {
        return Morpher::getMorph($this->morph);
    }

    public function tableReference()
    {
        return $this->schema_name.'.'.$this->table_name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getMorph()->getName();
    }

    /**
     * @param string $morph_reference
     *
     * @return MorphItem
     */
    public function setMorphReference($morph_reference): MorphItem
    {
        $this->morph = $morph_reference;
        return $this;
    }

    /**
     * @param string $relation
     *
     * @return $this
     */
    public function setRelation($relation)
    {
        if (!in_array($relation, ['1-1', '1-0', '0-0'])) {
            return $this;
        }
        $this->one_to_one_relation   = 0 === strcasecmp('1-1', $relation);
        $this->one_to_many_relation  = 0 === strcasecmp('1-0', $relation);
        $this->many_to_many_relation = 0 === strcasecmp('0-0', $relation);
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceTableName(): string
    {
        return $this->reference_table_name;
    }

    /**
     * @param string $reference_table_name
     *
     * @return MorphItem
     */
    public function setReferenceTableName(string $reference_table_name): MorphItem
    {
        $this->reference_table_name = $reference_table_name;
        return $this;
    }

    public function getReturnTableName()
    {
        return $this->reference_table_name ?: $this->getMorph()->getTableName();
    }

    /**
     * @return bool
     */
    public function isBy(): bool
    {
        return $this->by;
    }

    /**
     * @param bool $by
     *
     * @return MorphItem
     */
    public function setBy(bool $by): MorphItem
    {
        $this->by = $by;
        return $this;
    }

}