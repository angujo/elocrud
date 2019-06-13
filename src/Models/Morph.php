<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\Schema;
use Angujo\Elocrud\Helper;

class Morph
{
    private $name;
    private $id;
    private $type;
    private $table_name;
    private $schema_name;

    protected function __construct(DBColumn $column)
    {
        $this->name        = Helper::morphName($column->name);
        $this->table_name  = $column->table_name;
        $this->schema_name = $column->schema_name;
        $this->id          = $this->name.'_id';
        $this->type        = $this->name.'_type';
        if (is_string($column->comment)) {
            $entries = array_filter(array_map('trim', explode(',', $column->comment)));
            foreach ($entries as $comment) {
                if (!preg_match("/^(\((.*?)\))/i", $comment)) {
                    $comment = "({$column->schema_name})$comment";
                }
                if (!($item = MorphItem::commentDefinition($this, array_filter(explode(':', $comment), 'trim'), $column->name))) {
                    continue;
                }
                Morpher::setMorphItem($item);
            }
        }
    }

    public static function fromColumn(DBColumn $column)
    {
        return new self($column);
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

    public function tableReference()
    {
        return $this->schema_name.'.'.$this->table_name;
    }

}