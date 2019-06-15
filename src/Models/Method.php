<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Method
{
    private $comment;
    private $returns = true;
    private $name;
    private $run;
    private $output;
    private $output_type;
    private $static = false;
    private $access = 'public';
    private static $me = [];
    private static $def_name = '_default_';
    private static $c_name;
    private $namespace;
    private $imports = [];
    private $properties = [];

    protected function __construct($name, $fly = false)
    {
        self::$c_name = self::$def_name;
        $this->setName($name);
        if (false === $fly) {
            self::$me[self::$c_name][] = $this;
        }
    }

    public static function init($name = null)
    {
        if (null !== $name && !is_string($name) && !is_numeric($name)) {
            return;
        }
        self::$c_name            = null !== $name ? $name : self::$def_name;
        self::$me[self::$c_name] = [];
    }

    public static function fromMorph(Morph $morph)
    {
        $method = new self($morph->getName());
        $method->setReturns(true);
        $method->setOutputType(Helper::baseName(MorphTo::class));
        $method->setOutput('$this->morphTo();');
        $method->imports[] = MorphTo::class;
        $prop              = Property::phpdocProperty($morph->getName())->addType('NULL');
        $cmt               = [];
        foreach ($morph->getItems() as $item) {
            if ($morph->getReferenced() && $item->isBy()) continue;
            $method->imports[] = Config::namespace().'\\'.Helper::className($item->getTableName());
            $prop->addType(Helper::className($item->getTableName()));
            $cmt[] = Inflector::singularize(Helper::className($item->getTableName()));
        }
        $method->setComment('Get  '.implode('/', $cmt).' that is assigned to this '.Helper::className(Inflector::singularize($morph->getTableName())));
        return $method;
    }

    public static function fromMorphItem(MorphItem $morphItem)
    {
        $method            = new self($prop_name = lcfirst(Inflector::classify($morphItem->isOneToOneRelation() ? Inflector::singularize($morphItem->getReturnTableName()) : Inflector::pluralize($morphItem->getReturnTableName()))));
        $method->imports[] = Config::namespace().'\\'.Helper::className($morphItem->getReturnTableName());
        $method->setComment('Get all of '.Inflector::pluralize(Helper::className($morphItem->getReturnTableName())).' that are assigned to this '.Helper::className(Inflector::singularize($morphItem->getTableName())));
        if ($morphItem->isOneToOneRelation()) {
            $method->setComment('Get  '.Inflector::singularize(Helper::className($morphItem->getReturnTableName())).' that is assigned to this '.Helper::className(Inflector::singularize($morphItem->getTableName())));
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()))->addType('NULL');
            $method->imports[] = MorphOne::class;
            $method->setOutputType(Helper::baseName(MorphOne::class));
            $method->setOutput('$this->morphOne('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
        } elseif ($morphItem->isManyToManyRelation()) {
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()).'[]')->addType(Helper::baseName(Collection::class));
            $method->imports[] = Collection::class;
            $method->imports[] = MorphToMany::class;
            $method->setOutputType(Helper::baseName(MorphToMany::class));
            if ($morphItem->isBy()) {
                $method->setOutput('$this->morphedByMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
            } else {
                $method->setOutput('$this->morphToMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
            }
        } else {
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()).'[]')->addType(Helper::baseName(Collection::class));
            $method->imports[] = MorphMany::class;
            $method->imports[] = Collection::class;
            $method->setOutputType(Helper::baseName(MorphMany::class));
            $method->setOutput('$this->morphMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
        }
        return $method;
    }

    public static function fromForeignKey(ForeignKey $foreignKey, $namespace, $return = false)
    {
        $method = new self($name = Config::relationFunctionName($foreignKey), $return);
        $method->setReturns(true);
        $method->namespace = $namespace;
        $method->setComment('Get all of '.Inflector::pluralize(Helper::className($foreignKey->foreign_table_name)).' that are assigned to this '.Helper::className(Inflector::singularize($foreignKey->table_name)));
        if ($foreignKey->isOneToOne()) {
            $method->setComment('Get '.Inflector::singularize(Helper::className($foreignKey->foreign_table_name)).' that is assigned to this '.Helper::className(Inflector::singularize($foreignKey->table_name)));
            $method->setOutput('$this->hasOne('.Helper::className($foreignKey->foreign_table_name).'::class, \''.$foreignKey->foreign_column_name.'\',\''.$foreignKey->column_name.'\');');
            $method->setOutputType(Helper::baseName(HasOne::class));
            $method->imports[] = HasOne::class;
            Property::phpdocProperty($name, Helper::className($foreignKey->foreign_table_name), Helper::toWords($foreignKey->name))->addType('NULL');
            if (Config::base_abstract()) {
                $method->imports[] = Config::namespace().'\\'.Helper::className($foreignKey->foreign_table_name);
            }
        }
        if ($foreignKey->isOneToMany()) {
            $method->setOutput('$this->hasMany('.Helper::className($foreignKey->foreign_table_name).'::class, \''.$foreignKey->foreign_column_name.'\',\''.$foreignKey->column_name.'\');');
            $method->setOutputType(Helper::baseName(HasMany::class));
            $method->imports[] = Collection::class;
            $method->imports[] = HasMany::class;
            Property::phpdocProperty($name, Helper::className($foreignKey->foreign_table_name).'[]', Helper::toWords($foreignKey->name))->addType('Collection');
            if (Config::base_abstract()) {
                $method->imports[] = Config::namespace().'\\'.Helper::className($foreignKey->foreign_table_name);
            }
        }
        return $method;
    }

    public function __toString()
    {
        if (!$this->getName()) {
            return '';
        }
        $content = file_get_contents(Helper::BASE_DIR.'/stubs/function-template.tmpl');
        $content = Helper::replacePlaceholder('description', $this->getComment(true), $content);
        $content = Helper::replacePlaceholder('returns', $this->getOutputType(), $content);
        $content = Helper::replacePlaceholder('access', $this->getAccess(), $content);
        $content = Helper::replacePlaceholder('static', $this->isStatic() ? 'static ' : '', $content);
        $content = Helper::replacePlaceholder('name', $this->getName(), $content);
        $content = Helper::replacePlaceholder('run', $this->getRun(true), $content);
        $content = Helper::replacePlaceholder('return', $this->isReturns() ? 'return ' : '', $content);
        $content = Helper::replacePlaceholder('output', $this->getOutput(), $content);
        return Helper::cleanPlaceholder($content);
    }

    public static function textFormat($name = null)
    {
        $name = null === $name || !isset(self::$me[$name]) ? self::$def_name : $name;
        if (!isset(self::$me[$name]) || !is_array(self::$me[$name])) {
            return '';
        }
        $content = '';
        $entries = (self::$me[$name]);
        /** @var Method $method */
        foreach ($entries as $method) {
            $content .= "\n\n".$method;
        }
        return $content;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return array
     */
    public function getImports(): array
    {
        return array_unique($this->imports);
    }

    /**
     * @return mixed
     */
    public function getOutputType()
    {
        return $this->output_type;
    }

    /**
     * @param mixed $output_type
     *
     * @return Method
     */
    public function setOutputType($output_type)
    {
        $this->output_type = $output_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccess(): string
    {
        return $this->access;
    }

    /**
     * @param string $access
     *
     * @return Method
     */
    public function setAccess(string $access): Method
    {
        if (!in_array($access, ['public', 'protected', 'private'])) {
            $access = 'public';
        }
        $this->access = $access;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param mixed $output
     *
     * @return Method
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReturns(): bool
    {
        return $this->returns;
    }

    /**
     * @param bool $returns
     *
     * @return Method
     */
    public function setReturns(bool $returns): Method
    {
        $this->returns = $returns;
        return $this;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return $this->static;
    }

    /**
     * @param bool $static
     *
     * @return Method
     */
    public function setStatic(bool $static): Method
    {
        $this->static = $static;
        return $this;
    }

    /**
     * @param bool $string
     *
     * @return mixed
     */
    public function getComment($string = false)
    {
        if (is_array($this->comment) && $string) {
            return implode("\n\t* ", $this->comment);
        }
        return $this->comment ? $this->comment : '*';
    }

    /**
     * @param mixed $comment
     *
     * @return Method
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Method
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param bool $string
     *
     * @return mixed
     */
    public function getRun($string = false)
    {
        if (is_array($this->run) && $string) {
            return implode("\t\n", $this->run);
        }
        return $this->run;
    }

    /**
     * @param mixed $run
     *
     * @return Method
     */
    public function setRun($run)
    {
        $this->run = $run;
        return $this;
    }

    public function addRun($run)
    {
        if (!is_array($this->run)) {
            $this->run = $this->run ? [$this->run] : [];
        }
        $this->run[] = $run;
        return $this;
    }

    public function addComment($comment)
    {
        if (!is_array($this->comment)) {
            $this->comment = $this->comment ? [$this->comment] : [];
        }
        $this->comment[] = $comment;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}