<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\Schema;

class Morph
{
    private $name;
    private $referenced;
    private $id;
    private $type;
    private $table_name;
    private $schema_name;

    private $ref_table_name;
    private $ref_schema_name;

    protected function __construct($name, $table_name, $schema_name)
    {
        $this->name        = $name;
        $this->table_name  = $table_name;
        $this->schema_name = $schema_name;
        $this->id          = $this->name.'_id';
        $this->type        = $this->name.'_type';

        $this->prepareItems();
    }

    public static function create($name, $table_name, $schema_name)
    {
        return new self($name, $table_name, $schema_name);
    }

    private function prepareItems()
    {
        if (!($comments = $this->getTypeColumn()->comment)) {
            return;
        }
        if (preg_match('/(\:0-0(\:|,|$))/i', $comments) && ($icomment = $this->getIdColumn()->comment)) {
            $details = array_filter(explode(':', $icomment), 'trim');
            if (count($details) >= 2) {
                $f                     = array_shift($details);
                $this->ref_schema_name = preg_replace("/^(\((.*?)\))?(.*?)$/i", '$2', $f) ?: $this->getTypeColumn()->schema_name;
                $this->ref_table_name  = preg_replace("/^(\((.*?)\))?(.*?)$/i", '$3', $f);
                $this->referenced      = array_shift($details);
            }
        }
        $this->setMorphItems($comments);
    }

    private function setMorphItems($comments)
    {
        $entries = array_filter(array_map('trim', explode(',', $comments)));
        foreach ($entries as $comment) {
            $details     = array_filter(explode(':', $comment), 'trim');
            $f           = array_shift($details);
            $schema_name = preg_replace("/^(\((.*?)\))?(.*?)$/i", '$2', $f) ?: $this->getTypeColumn()->schema_name;
            $table_name  = preg_replace("/^(\((.*?)\))?(.*?)$/i", '$3', $f);
            $relation    = null;
            $column_name = null;

            if (!is_string($table_name) || 1 !== preg_match('/^[a-z]/i', $table_name) || null == Schema::getTable($schema_name, $table_name)) {
                continue;
            }
            foreach ($details as $detail) {
                if (null === $column_name && is_string($detail) && 1 === preg_match('/^[a-z]/i', $detail)) {
                    $column_name = $detail;
                } else {
                    $relation = $detail;
                }
            }
            /*if (!$column_name && count($this->getTable()->primary_columns) !== 1) {
                continue;//TODO If it is a requirement, we'll consider
            }*/
            Morpher::setMorphItem($item = MorphItem::create($table_name, $column_name, $schema_name));
            if ($this->referenced) {
                $item->setReferenceTableName($this->ref_table_name);
                Morpher::setMorphItem(MorphItem::create($this->ref_table_name, $this->referenced, $this->ref_schema_name)->setRelation($relation)->setMorphReference($this->getReferenceId())->setReferenceTableName($table_name)->setBy(true));
            }
            $item->setRelation($relation)->setMorphReference($this->getReferenceId());
        }
    }

    public function setReferenced($column_name, $table_name, $schema_name = null)
    {
        $this->referenced      = $column_name;
        $this->ref_table_name  = $table_name;
        $this->ref_schema_name = $schema_name ?: $this->schema_name;
    }

    /**
     * @return DBColumn|null
     */
    public function getReferencedColumn()
    {
        return Schema::getColumn($this->ref_schema_name, $this->ref_table_name, $this->referenced);
    }

    /**
     * @return DBColumn|null
     */
    public function getTypeColumn()
    {
        return Schema::getColumn($this->schema_name, $this->table_name, $this->type);
    }

    /**
     * @return DBColumn|null
     */
    public function getIdColumn()
    {
        return Schema::getColumn($this->schema_name, $this->table_name, $this->id);
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function getReferenceId()
    {
        return ($this->schema_name.'.'.$this->table_name.'.'.$this->name);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return MorphItem[]
     */
    public function getItems(): array
    {
        return array_filter(Morpher::getMorphItems(), function(MorphItem $morphItem){ return $morphItem->getMorph() == $this; });
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function getTable()
    {
        return Schema::getTable($this->schema_name, $this->table_name);
    }

    public function tableReference()
    {
        return $this->schema_name.'.'.$this->table_name;
    }

    /**
     * @return mixed
     */
    public function getReferenced()
    {
        return $this->referenced;
    }

}