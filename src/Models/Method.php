<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\DocInflector;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function __construct($name, $fly = false)
    {
        self::$c_name = self::$def_name;
        $this->setName($name);
        if (false === $fly && !isset(self::$me[self::$c_name][$name])) {
            self::$me[self::$c_name][$name] = $this;
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

    public static function fromManyToMany(ManyToMany $toMany)
    {
        $method = new self($name = lcfirst(DocInflector::pluralize(DocInflector::classify($toMany->getRefTableName()))));
        // Property::phpdocProperty($name, Helper::className($toMany->getRefTableName()).'[]')->addType(Helper::baseName(Collection::class));
        $method->imports[] = Collection::class;
        $method->setComment('Get '.DocInflector::pluralize(Helper::className($toMany->getRefTableName())).' that belong to this '.Helper::className(Inflector::singularize($toMany->getTableName())));
        $method->setReturns(true);
        $method->setOutputType(Helper::baseName(BelongsToMany::class));
        $method->setOutput('$this->belongsToMany('.Helper::className($toMany->getRefTableName()).'::class, \''.$toMany->getSchemaName().'.'.$toMany->getName().'\', \''.$toMany->getColumnName().'\', \''.$toMany->getRefColumnName().'\');');
        $method->imports[] = BelongsToMany::class;
        $method->imports[] = Config::workSpace($toMany->getRefSchemaName()).'\\'.Helper::className($toMany->getRefTableName());
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
     * @param self[] $methods
     *
     * @return string
     */
    public static function arrayToString(array $methods)
    {
        $content = '';
        $methods = !is_array($methods) ? [] : $methods;
        foreach ($methods as $method) {
            if (!is_a($method, self::class)) {
                continue;
            }
            $content .= "\n\n".$method."\n";
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

    public function addImport($item)
    {
        $this->imports[] = $item;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param mixed $namespace
     *
     * @return Method
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }
}