<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model
{
    private $table;
    private $content = '';
    private $extends;

    protected $className;
    protected $abstractName;
    protected $namespace;
    protected $imports = [];
    protected $timestamps;
    protected $attribs;
    protected $fillables;
    protected $casts;
    protected $dates;
    protected $uses = [];

    public $fileName;

    public function __construct(DBTable $table)
    {
        Property::init();
        Method::init();
        $this->setExtension(Config::model_class());
        $this->table        = $table;
        $this->content      = file_get_contents(Helper::BASE_DIR.'/stubs/model-template.tmpl');
        $this->className    = Helper::className($this->table->name);
        $this->abstractName = 'Base'.Helper::className($this->table->name);
        $this->namespace    = Config::namespace().(Config::base_abstract() ? '\BaseTables' : '');
        $this->fileName     = $this->className.'.php';
        $this->timestamps   = Property::attribute('protected', 'timestamps', false, 'Recognize timestamps')->setType('boolean');
        Property::attribute('protected', 'table', $table->has_schema ? $table->query_name : $table->name, 'Model Table')->setType('string');
        $this->fillables = Property::attribute('protected', 'fillable', [], 'Mass assignable columns')->setType('array');
        $this->dates     = Property::attribute('protected', 'dates', [], 'Date and Time Columns')->setType('array');
    }

    private function setExtension($class)
    {
        $this->imports[] = $class;
        $this->extends   = Helper::baseName($class);
    }

    protected function setColumns()
    {
        $timeStamp = 0;
        if ($this->table->primary_columns->count() > 0) {
            $pk = null;
            if ($this->table->primary_columns->count() > 1) {
                Property::attribute('protected', 'primaryKey', array_values($this->table->primary_columns->map(function (DBColumn $column) { return $column->name; })->all()), 'Primary Keys')->setType('array');
            } else {
                $column = $this->table->primary_columns->first();
                if (0 !== strcasecmp('id', $column->name)) {
                    Property::attribute('protected', 'primaryKey', $column->name, 'Primary Key')->setType('string');
                }
                if (!$column->is_auto_increment) {
                    Property::attribute('protected', 'incrementing', false, 'Primary Key is not auto-incrementing')->setType('boolean');
                }
                if (!$column->type->isInt) {
                    Property::attribute('protected', 'keyType', 'string', 'The "type" of the auto-incrementing ID')->setType('string');
                }
            }
        }
        $this->table->columns->each(function (DBColumn $column) use (&$timeStamp) {
            Property::fromColumn($column);
            if ((in_array($column->name, Config::create_columns()) || in_array($column->name, Config::update_columns())) && $column->type->isDateTime) {
                $timeStamp++;
            }
            if (!$column->is_auto_increment) {
                $this->fillables->addValue($column->name);
                $this->defaultColumn($column);
            }
            $this->setCast($column);
            $this->softDeletes($column);
            $this->dates($column);
        });
        $this->timestamps->setValue(2 === $timeStamp);
    }

    protected function setForeignKeys()
    {
        $this->table->foreign_keys_one_to_one->merge($this->table->foreign_keys_one_to_many)->each(function (ForeignKey $foreignKey) {
            $method        = Method::fromForeignKey($foreignKey, $this->namespace);
            $this->imports = array_merge($this->imports, $method->getImports());
        });
    }

    protected function dates(DBColumn $column)
    {
        if (!$column->type->isDateTime) {
            return;
        }
        $this->dates->addValue($column->name);
        $this->imports[] = Carbon::class;
    }

    protected function softDeletes(DBColumn $column)
    {
        if (!in_array($column->name, Config::soft_delete_columns()) || !$column->type->isDateTime) {
            return;
        }
        $this->uses[]    = SoftDeletes::class;
        $this->imports[] = SoftDeletes::class;
    }

    protected function defaultColumn(DBColumn $column)
    {
        if (!strlen($column->default)) {
            return;
        }
        if (!$this->attribs) {
            $this->attribs = Property::attribute('protected', 'attributes', [], 'Default attribute values')->setType('array');
        }
        $this->attribs->addValue($column->default, $column->name);
    }

    protected function setCast(DBColumn $column)
    {
        if (!$this->casts) {
            $this->casts = Property::attribute('protected', 'casts', [], 'Casts')->setType('array');
        }
        if (null !== ($v = $this->typeCast($column))) {
            $this->casts->addValue($v, $column->name);
        }
    }

    protected function autoColumn(DBColumn $column)
    {
        if (!$column->is_auto_increment) {
            return;
        }
    }

    protected function typeCast(DBColumn $column)
    {
        foreach (Config::type_casts() as $type => $cast) {
            if (0 === stripos($type, 'type:')) {
                if (!($ntype = preg_replace('/type:/i', '', $type))) {
                    return null;
                }
                $dtype = preg_replace('/(\((.*?)?\))/', '', $ntype);
                if (!$column->type->{'is'.ucfirst($dtype)}) {
                    return null;
                }
                return 0 === strcasecmp($column->column_type, $ntype) ? $cast : null;
            } elseif (false !== stripos($type, '%')) {
                $regex = $type;
                if (0 === stripos($regex, '%')) {
                    $regex = '^'.$regex;
                }
                if (0 === strcasecmp('%', substr($type, -1, 1))) {
                    $regex = $regex.'$';
                }
                $regex = str_ireplace('%', '(.*?)', preg_replace('/[%]+/', '%', $regex));
                if (preg_match($regex, $column->name)) {
                    return $cast;
                }
            }
        }
        return null;
    }

    public function __toString()
    {
        $this->setColumns();
        $this->setForeignKeys();
        if (Config::base_abstract()) {
            $this->content = Helper::replacePlaceholder('abstract', 'abstract ', $this->content);
        }
        $this->content = Helper::replacePlaceholder('imports', implode("\n", array_filter(array_map(function ($cls) { return $cls ? "use $cls;" : null; }, array_unique($this->imports)))), $this->content);
        $this->content = Helper::replacePlaceholder('uses', !empty($this->uses) ? "\tuse ".implode(',', array_filter(array_map(function ($cls) { return $cls ? Helper::baseName($cls) : null; }, array_unique($this->uses)))).';' : '', $this->content);
        $this->content = Helper::replacePlaceholder('constants', Property::getConstantText(), $this->content);
        $this->content = Helper::replacePlaceholder('properties', Property::getPhpDocText(), $this->content);
        $this->content = Helper::replacePlaceholder('extends', $this->extends, $this->content);
        $this->content = Helper::replacePlaceholder('attributes', Property::getAttributeText(), $this->content);
        $this->content = Helper::replacePlaceholder('class', Config::base_abstract() ? $this->abstractName : $this->className, $this->content);
        $this->content = Helper::replacePlaceholder('namespace', $this->namespace, $this->content);
        $this->content = Helper::replacePlaceholder('functions', Method::textFormat(), $this->content);
        return Helper::cleanPlaceholder($this->content);
    }

    public function workingClassText()
    {
        $content = file_get_contents(Helper::BASE_DIR.'/stubs/model2-template.tmpl');
        $content = Helper::replacePlaceholder('class', $this->className, $content);
        $content = Helper::replacePlaceholder('imports', 'use '.Config::base_namespace().'\\'.$this->abstractName.';', $content);
        $content = Helper::replacePlaceholder('namespace', Config::namespace(), $content);
        $content = Helper::replacePlaceholder('description', '* Working class to be used for customized extension of DB Base Tables', $content);
        $content = Helper::replacePlaceholder('properties', '* Add properties here', $content);
        $content = Helper::replacePlaceholder('extends', $this->abstractName, $content);
        return Helper::cleanPlaceholder($content);
    }
}