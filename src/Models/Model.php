<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model
{
    private $table;
    private $content = '';
    private $extends;

    protected $className;
    protected $namespace;
    protected $imports = [];
    protected $timestamps;
    protected $attribs;
    protected $fillables;
    protected $casts;
    protected $dates;
    protected $uses = [];

    public $fileName;

    public function __construct(DBTable $table, $extends = null)
    {
        Property::init();
        $this->setExtension(!is_string($extends) ? \Illuminate\Database\Eloquent\Model::class : $extends);
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

    private function setExtension($class)
    {
        $this->imports[] = $class;
        $this->extends = Helper::baseName($class);
    }

    protected function setColumns()
    {
        $timeStamp = 0;
        $this->table->columns->each(function (DBColumn $column) use (&$timeStamp) {
            Property::fromColumn($column);
            if (in_array($column->name, ['created_at', 'updated_at']) && $column->type->isDateTime) $timeStamp++;
            if (!$column->is_primary && !$column->is_auto_increment) {
                $this->fillables->addValue($column->name);
                $this->defaultColumn($column);
            } else {
                $this->primaryColumn($column);
            }
            $this->softDeletes($column);
            $this->dates($column);
        });
        $this->timestamps->setValue(2 === $timeStamp);
    }

    protected function dates(DBColumn $column)
    {
        if (!$column->type->isDateTime) return;
        $this->dates->addValue($column->name);
        $this->imports[] = Carbon::class;
    }

    protected function softDeletes(DBColumn $column)
    {
        if (0 !== strcasecmp($column->name, 'deleted_at') || !$column->type->isDateTime) return;
        $this->uses[] = SoftDeletes::class;
        $this->imports[] = SoftDeletes::class;
    }

    protected function defaultColumn(DBColumn $column)
    {
        if (!strlen($column->default)) return;
        if (!$this->attribs) $this->attribs = Property::attribute('protected', 'attributes', [], 'Default attribute values')->setType('array');
        $this->attribs->addValue($column->default, $column->name);
    }

    protected function autoColumn(DBColumn $column)
    {
        if (!$column->is_auto_increment) return;
    }

    protected function primaryColumn(DBColumn $column)
    {
        if (!$column->is_primary) return;
        if (0 !== strcasecmp('id', $column->name)) Property::attribute('protected', 'primaryKey', $column->name, 'Primary Key')->setType('string');
        if (!$column->is_auto_increment) Property::attribute('protected', 'incrementing', false, 'Primary Key is not auto-incrementing')->setType('boolean');
        if (!$column->type->isInt) Property::attribute('protected', 'keyType', 'string', 'The "type" of the auto-incrementing ID')->setType('string');
    }

    public function __toString()
    {
        $this->setColumns();
        $this->content = Helper::replacePlaceholder('imports', implode("\n", array_filter(array_map(function ($cls) { return $cls ? "use $cls;" : null; }, array_unique($this->imports)))), $this->content);
        $this->content = Helper::replacePlaceholder('uses', !empty($this->uses) ? "\tuse " . implode(',', array_filter(array_map(function ($cls) { return $cls ? Helper::baseName($cls) : null; }, array_unique($this->uses)))) . ';' : '', $this->content);
        $this->content = Helper::replacePlaceholder('constants', Property::getConstantText(), $this->content);
        $this->content = Helper::replacePlaceholder('properties', Property::getPhpDocText(), $this->content);
        $this->content = Helper::replacePlaceholder('extends', $this->extends, $this->content);
        $this->content = Helper::replacePlaceholder('attributes', Property::getAttributeText(), $this->content);
        $this->content = Helper::replacePlaceholder('class', $this->className, $this->content);
        $this->content = Helper::replacePlaceholder('namespace', $this->namespace, $this->content);
        return Helper::cleanPlaceholder($this->content);
    }
}