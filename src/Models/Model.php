<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Helper;

class Model
{
    private $table;
    private $content = '';

    protected $className;
    protected $namespace;
    protected $imports = [];
    protected $timestamps;
    protected $attribs;
    protected $fillables;
    protected $casts;
    protected $dates;

    public $fileName;

    public function __construct(DBTable $table)
    {
        Property::init();
        $this->table = $table;
        $this->content = file_get_contents(Helper::BASE_DIR . '/stubs/model-template.tmpl');
        $this->className = Helper::carmelCase($this->table->name);
        $this->namespace = $this->className;
        $this->fileName = $this->className . '.php';
        $this->timestamps = Property::attribute('protected', 'timestamps', false, 'Recognize timestamps')->setType('boolean');
        Property::attribute('protected', 'table', $table->name, 'Model Table')->setType('string');
        $this->fillables = Property::attribute('protected', 'fillable', [], 'Mass assignable columns')->setType('array');
        $this->casts = Property::attribute('protected', 'casts', [], 'Casts')->setType('array');
        $this->dates = Property::attribute('protected', 'dates', [], 'Date and Time Columns')->setType('array');
    }

    protected function setColumns()
    {
        $timeStamp = 0;
        $this->table->columns->each(function (DBColumn $column) use (&$timeStamp) {
            Property::fromColumn($column);
            if (in_array($column, ['created_at', 'updated_at'])) $timeStamp++;
            if (!$column->is_primary && !$column->is_auto_increment) {
                $this->fillables->addValue($column->name);
                if (strlen($column->default)) {
                    if (!$this->attribs) $this->attribs = Property::attribute('protected', 'attributes', [], 'Default attribute values')->setType('array');
                    $this->attribs->addValue($column->default, $column->name);
                }
            } else {
                if ($column->is_primary) {
                    if (0 !== strcasecmp('id', $column->name)) Property::attribute('protected', 'primaryKey', $column->name, 'Primary Key')->setType('string');
                    if (!$column->is_auto_increment) Property::attribute('protected', 'incrementing', false, 'Primary Key is not auto-incrementing')->setType('boolean');
                    if (!$column->type->isInt) Property::attribute('protected', 'keyType', 'string', 'The "type" of the auto-incrementing ID')->setType('string');
                }
            }
        });
        $this->timestamps->setValue(2 === $timeStamp);
    }

    public function __toString()
    {
        $this->setColumns();
        $this->content = Helper::replacePlaceholder('constants', Property::getConstantText(), $this->content);
        $this->content = Helper::replacePlaceholder('properties', Property::getPhpDocText(), $this->content);
        $this->content = Helper::replacePlaceholder('attributes', Property::getAttributeText(), $this->content);
        $this->content = Helper::replacePlaceholder('class', $this->className, $this->content);
        $this->content = Helper::replacePlaceholder('namespace', $this->namespace, $this->content);
        return Helper::cleanPlaceholder($this->content);
    }
}