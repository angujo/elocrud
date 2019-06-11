<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\Elocrud\Helper;

class Morph
{
    private $name;
    private $id;
    private $type;
    private $table_name;
    /**
     * @var MorphItem[]
     */
    private $items = [];

    public function __construct(DBColumn $column)
    {
        $this->name       = Helper::morphName($column->name);
        $this->table_name = $column->table_name;
        $this->id         = $this->name.'_id';
        $this->type       = $this->name.'_type';
        if (is_string($column->comment)) {
            $this->items = array_filter(
                array_map(function($entr) use ($column){
                    return MorphItem::commentDefinition($column->schema_name, array_filter(explode(':', $entr), 'trim'));
                }, array_filter(explode(',', $column->comment), 'trim')));
        }
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
        return $this->items;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table_name;
    }

}