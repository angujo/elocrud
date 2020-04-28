<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\DBReader\Models\DBTable;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model
{
    private $content = '';
    private $extends;

    protected $abstractName;
    protected $imports = [];
    protected $timestamps;
    protected $attribs;
    protected $fillables;
    protected $casts;
    protected $dates;
    protected $uses    = [];
    /** @var Method[] */
    protected $functions = [];

    private $table;
    private $fileName;
    private $className;
    private $basespace;
    private $workspace;

    public function __construct(DBTable $table)
    {
        Property::init();
        // Method::init();
        $this->setExtension(Config::model_class());
        $this->table        = $table;
        $this->content      = file_get_contents(Helper::BASE_DIR.'/stubs/model-template.tmpl');
        $this->className    = Helper::className($this->table->name);
        $this->abstractName = Config::baseName($this->table->name, $this->table->schema_name);
        $this->basespace    = Config::baseSpace($table->schema_name);// Config::namespace().(Config::db_directories() ? '\\'.Helper::className($table->schema_name) : '').(Config::base_abstract() ? '\BaseTables' : '');
        $this->workspace    = Config::workSpace($table->schema_name);// Config::namespace().(Config::db_directories() ? '\\'.Helper::className($table->schema_name) : '');
        $this->fileName     = $this->className.'.php';
        Property::attribute('protected', 'table', $table->has_schema ? $table->reference : $table->name, 'Model Table')->setType('string');
        $this->fillables = Property::attribute('protected', 'fillable', [], 'Mass assignable columns')->setType('array');
    }

    private function setExtension($class)
    {
        $this->imports[] = $class;
        $this->extends   = Helper::baseName($class);
    }

    protected function setColumns()
    {
        if (count($this->table->primary_columns) > 0) {
            $pk = null;
            if (count($this->table->primary_columns) > 1) {
                Property::attribute('protected', 'primaryKey', array_values(array_map(function (DBColumn $column) { return $column->name; }, $this->table->primary_columns)), 'Primary Keys')->setType('array');
            } else {
                /** @var DBColumn $column */
                $column = array_values($this->table->primary_columns)[0];
                if (0 !== strcasecmp('id', $column->name)) {
                    Property::attribute('protected', 'primaryKey', $column->name, 'Primary Key')->setType('string');
                }
                if (!$column->is_auto_increment) {
                    Property::attribute('public', 'incrementing', false, 'Primary Key is not auto-incrementing')->setType('boolean');
                }
                if (!$column->type->isPhpinteger) {
                    Property::attribute('protected', 'keyType', 'string', 'The "type" of the auto-incrementing ID')->setType('string');
                }
            }
        }
        $timeStamp = 0;
        $types     = array_merge(Config::create_columns(), Config::update_columns());
        foreach ($this->table->columns as $column) {
            $ctype = $this->setCast($column);
            $set   = true;
            if (in_array($column->name, $types) &&
                ($column->type->isDateTime || $column->type->isTimestamp || $column->type->isTimestampTz)) {
                $timeStamp++;
                if (in_array($column->name, Config::create_columns()) && 0 !== strcasecmp('created_at', $column->name)) {
                    Property::constant('CREATED_AT', $column->name, '', 'Replacement for date created');
                } elseif (in_array($column->name, Config::update_columns()) && 0 !== strcasecmp('updated_at', $column->name)) {
                    Property::constant('UPDATED_AT', $column->name, '', 'Replacement for date updated');
                } else {
                    $set = false;
                }
            }
            if (true === $set && false === $this->softDeletes($column)) {
                Property::fromColumn($column, $ctype);
            }
            if (!$column->is_auto_increment) {
                $this->fillables->addValue($column->name);
                $this->defaultColumn($column);
            };
            $this->dates($column);
        }
        if (2 !== $timeStamp) {
            Property::attribute('public', 'timestamps', false, 'Recognize timestamps')->setType('boolean');
        }
    }

    protected function setMethods()
    {
        $this->functions = array_merge(
            $this->functions,
            BelongsToEntry::methods($this->table, $this->workspace),
            HasOneEntry::methods($this->table, $this->workspace),
            MorphToEntry::methods($this->table, $this->workspace),
            HasManyEntry::methods($this->table, $this->workspace),
            MorphedEntry::methods($this->table, $this->workspace)
        );

        /** @var Method[] $mts */
        $mts = array_merge(HasManyThroughEntry::methods($this->table, $this->workspace), BelongsToManyEntry::methods($this->table, $this->workspace));
        foreach ($mts as $mt) {
            if (false === current(array_filter($this->functions, function (Method $method) use ($mt) { return 0 === strcasecmp($method->getName(), $mt->getName()); }))) {
                $this->functions[] = $mt;
            }
        }

        foreach ($this->functions as $function) {
            $this->imports = array_merge($this->imports, $function->getImports());
        }
    }

    protected function getFunctions()
    {
        return Method::arrayToString($this->functions);
    }

    protected function dates(DBColumn $column)
    {
        if (!$column->type->isDateTime && !$column->type->isDate && !$column->type->isTimestampTz && !$column->type->isTimestamp) {
            return;
        }
        if (!$this->dates) {
            $this->dates = Property::attribute('protected', 'dates', [], 'Date and Time Columns')->setType('array');
        }
        $this->dates->addValue($column->name);
        $this->imports[] = Carbon::class;
    }

    protected function softDeletes(DBColumn $column)
    {
        if (!in_array($column->name, Config::soft_delete_columns()) ||
            (!$column->type->isDateTime && !$column->type->isDate && !$column->type->isTimestampTz && !$column->type->isTimestamp)) {
            return false;
        }
        $this->uses[]    = SoftDeletes::class;
        $this->imports[] = SoftDeletes::class;
        if (0 !== strcasecmp('deleted_at', $column->name)) {
            Property::constant('DELETED_AT', $column->name, '', 'Replacement for date deleted');
            return true;
        }
        return false;
    }

    protected function defaultColumn(DBColumn $column)
    {
        if (!strlen($column->default)) {
            return;
        }
        if (!$this->attribs) {
            $this->attribs = Property::attribute('protected', 'attributes', [], 'Default attribute values')->setType('array');
        }
        $this->attribs->addValue($this->compileDefaultValue($column), $column->name);
    }

    private function compileDefaultValue(DBColumn $column)
    {
        if ($column->type->isBool) {
            return filter_var($column->default, FILTER_VALIDATE_BOOLEAN);
        }
        if (0 === strcasecmp('null', $column->default)) {
            return null;
        }
        return $column->default;
    }

    protected function setCast(DBColumn $column)
    {
        $type = $this->typeCast($column);
        if (!$this->casts && null !== $type) {
            $this->casts = Property::attribute('protected', 'casts', [], 'Casts')->setType('array');
        }
        if (null !== $type) {
            $this->casts->addValue($type, $column->name);
        }
        return $type;
    }

    protected function autoColumn(DBColumn $column)
    {
        if (!$column->is_auto_increment) {
            return;
        }
    }

    protected function setManyToMany()
    {
        // return;
        /*$rels = ManyToMany::getManyRelations($this->table);
        foreach ($rels as $rel) {
            $method        = Method::fromManyToMany($rel);
            $this->imports = array_merge($this->imports, $method->getImports());
        }*/
    }

    protected function typeCast(DBColumn $column)
    {
        $casts = Config::type_casts();
        foreach ($casts as $type => $cast) {
            if (0 === stripos($type, 'type:')) {
                if (!($ntype = preg_replace('/^(type:)/i', '', $type))) {
                    return null;
                }
                $dtype = preg_replace('/(\((.*?)?\))/', '', $ntype);
                if ($column->type->{'is'.ucfirst($dtype)}) {
                    return $cast;
                }
                // if(0 === strcasecmp($column->column_type, $ntype)) return $cast ;
            } elseif (false !== stripos($type, '%')) {
                $regex = $type;
                if (0 === stripos($regex, '%')) {
                    $regex = '^'.$regex;
                }
                if (0 === strcasecmp('%', substr($type, -1, 1))) {
                    $regex = $regex.'$';
                }
                $regex = str_ireplace('%', '(.*?)', preg_replace('/[%]+/', '%', $regex));
                if (preg_match("/{$regex}/i", $column->name)) {
                    return $cast;
                }
            }
        }
        return null;
    }

    public function toString()
    {
        $this->setColumns();
        $this->setMethods();
        // $this->setMorphedTo();
        //$this->setMorphedReference();
        $this->setManyToMany();
        if (Config::base_abstract()) {
            $this->content = Helper::replacePlaceholder('abstract', 'abstract ', $this->content);
        }
        $this->content = Helper::replacePlaceholder('description', $this->getDescription(), $this->content);
        $this->content = Helper::replacePlaceholder('imports', implode("\n", array_filter(array_map(function ($cls) { return $cls ? "use $cls;" : null; }, array_unique($this->imports)))), $this->content);
        $this->content = Helper::replacePlaceholder('uses', !empty($this->uses) ? "\tuse ".implode(',', array_filter(array_map(function ($cls) { return $cls ? Helper::baseName($cls) : null; }, array_unique($this->uses)))).';' : '', $this->content);
        $this->content = Helper::replacePlaceholder('constants', Property::getConstantText(), $this->content);
        $this->content = Helper::replacePlaceholder('properties', Property::getPhpDocText(), $this->content);
        $this->content = Helper::replacePlaceholder('extends', $this->extends, $this->content);
        $this->content = Helper::replacePlaceholder('attributes', Property::getAttributeText(), $this->content);
        $this->content = Helper::replacePlaceholder('class', Config::base_abstract() ? $this->abstractName : $this->className, $this->content);
        $this->content = Helper::replacePlaceholder('namespace', $this->basespace, $this->content);
        $this->content = Helper::replacePlaceholder('functions', $this->getFunctions(), $this->content);
        return Helper::cleanPlaceholder($this->content);
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function workingClassText()
    {
        $content = file_get_contents(Helper::BASE_DIR.'/stubs/model2-template.tmpl');
        $content = Helper::replacePlaceholder('class', $this->className, $content);
        $content = Helper::replacePlaceholder('imports', "use {$this->basespace}\\{$this->abstractName};", $content);
        $content = Helper::replacePlaceholder('namespace', $this->workspace, $content);
        $content = Helper::replacePlaceholder('description', '* Working class to be used for customized extension of DB Base Tables', $content);
        $content = Helper::replacePlaceholder('properties', '* Add properties here', $content);
        $content = Helper::replacePlaceholder('extends', $this->abstractName, $content);
        return Helper::cleanPlaceholder($content);
    }

    protected function getDescription()
    {
        $intr = " * {$this->className} is an object class for the database table {$this->table->reference}\n * This class should be used AS IS in querying data.\n"
            ." * If 'overwrite' is enabled, it should not be modified, but can be extended by another class.\n".
            " *\n *\n";
        if (Config::base_abstract()) {
            $intr = " * {$this->abstractName} is a BASE class for {$this->className}\n * It is a object model representation of teh table {$this->table->reference}.\n".
                " * Modifications and customizations should be done on the {$this->className} class\n".
                " *\n *\n";
        }
        return $intr.
            "\n * @access public".
            "\n * @author @angujomondi".
            "\n * @version ".PHP_VERSION.' of PhP'.
            (Config::date_base() ? "\n * @date ".date('Y-m-d H:i') : '').
            "\n *\n *\n";
    }

    /**
     * @return string
     */
    public function getAbstractName(): string
    {
        return $this->abstractName;
    }

    /**
     * @return DBTable
     */
    public function getTable(): DBTable
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getBasespace(): string
    {
        return $this->basespace;
    }

    public function filePath()
    {
        return (Config::base_abstract() ? Config::base_dir() : Config::dir_path()).DIRECTORY_SEPARATOR.Config::baseName($this->table->name, $this->table->schema_name).'.php';
    }
}