<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBColumn;
use Angujo\Elocrud\Helper;

class Property
{
    /**
     * For property of phpdoc when method defined
     * @var string|string[]
     */
    private $params;
    /**
     * Internal for prefixing the property
     * E.g @property for property
     * @var string
     */
    private $prefix;
    /**
     * Access type e.g. public, private, protected,...
     * @var string
     */
    private $access;
    /**
     * Return type for the property
     * E.g. integer|string|bool
     * @var string|string[]
     */
    private $type;
    /**
     * Comment for the property
     * @var string
     */
    private $comment;
    /**
     * Name of the property
     * @var string
     */
    private $name;
    /**
     * The default value of the property
     * @var string|int|float|boolean
     */
    private $value;

    /** @var self */
    protected $me;
    /** @var Property[] */
    protected static $instances = [];

    private function __construct($name, $value = null)
    {
        $this->name = $name;
        $this->setValue($value);
    }

    public static function init()
    {
        self::$instances = [];
    }

    public static function fromColumn(DBColumn $column)
    {
        self::constant(strtoupper($column->name), $column->name)->setComment('Column name: ' . $column->name);
        //echo '<pre>';var_dump($column->type->isPhpinteger,$column->type->isInt,$column->data_type);
        self::phpdocProperty($column->name, $column->type->phpName(), Helper::toWords($column->name));
    }

    /**
     * @param string $type
     * @param string|array $name
     * @param null|string $comment
     * @return Property
     */
    public static function phpdocProperty($name, $type = null, $comment = null)
    {
        return self::phpdoc($name, $type, $comment)->setPrefix('@property');
    }

    /**
     * @param $name
     * @param null $type
     * @param null $comment
     * @return Property
     */
    public static function phpdocMethod($name, $type = null, $comment = null)
    {
        return self::phpdoc($name, $type, $comment)->setPrefix('@method');
    }


    /**
     * @param $name
     * @param null $type
     * @param null $comment
     * @return Property
     */
    private static function phpdoc($name, $type = null, $comment = null)
    {
        return self::$instances[__FUNCTION__][] = (new self($name))->setComment($comment)->setType($type);
    }

    /**
     * @param $name
     * @param $value
     * @param string $access
     * @param null $comment
     * @return Property
     */
    public static function constant($name, $value, $access = 'public', $comment = null)
    {
        return self::$instances[__FUNCTION__][] = (new self($name, $value))->setComment($comment)->setAccess($access);
    }

    public static function attribute($access, $name, $value = null, $comment = null)
    {
        return self::$instances[__FUNCTION__][] = (new self($name, $value))->setComment($comment)->setAccess($access);
    }

    public static function getAttributes()
    {
        return isset(self::$instances['attribute']) ? self::$instances['attribute'] : [];
    }

    public static function getConstants()
    {
        return isset(self::$instances['constant']) ? self::$instances['constant'] : [];
    }

    public static function getPhpDocs()
    {
        return isset(self::$instances['phpdoc']) ? self::$instances['phpdoc'] : [];
    }

    public static function getPhpDocText()
    {
        if (empty(self::$instances['phpdoc'])) return null;
        $output = '';
        /** @var Property $property */
        foreach (self::$instances['phpdoc'] as $property) {
            if (!$property->getName()) continue;
            $content = 0 === strcasecmp('@property', $property->getPrefix()) ? ' * ${prefix} ${type} $${name} ${comments};' : ' * ${prefix} ${type} ${name}(${params}) ${comments}';
            $content = str_ireplace('${prefix}', $property->getPrefix(), $content);
            $content = str_ireplace('${comments}', $property->getComment(), $content);
            $content = str_ireplace('${name}', $property->getName(), $content);
            $content = str_ireplace('${type}', (is_array($property->getType()) ? implode('|', $property->getType()) : $property->getType()), $content);
            $content = str_ireplace('${params}', (is_array($property->getParams()) ? implode(', ', array_filter(array_map(function ($p, $k) { return is_string($k) ? $k . '=' . var_export($p, true) : $p; }, $property->getType()))) : $property->getType()), $content);
            $output .= $content . "\n";
        }
        return Helper::cleanPlaceholder($output);
    }

    public static function getConstantText()
    {
        if (empty(self::$instances['constant'])) return null;
        $_content = file_get_contents(Helper::BASE_DIR . '/stubs/property2-template.tmpl');
        $output = '';
        /** @var Property $property */
        foreach (self::$instances['constant'] as $property) {
            if (!$property->getName()) continue;
            $content = $_content;
            $content = str_ireplace('${type}', 'const ', $content);
            $content = str_ireplace('${comments}', $property->getComment(), $content);
            $content = str_ireplace('${access}', ($property->getAccess() ?: 'public') . ' ', $content);
            $content = str_ireplace('${name}', $property->getName(), $content);
            $content = str_ireplace('${value}', var_export($property->getValue(), true), $content);
            $output .= $content . "\n";
        }
        return Helper::cleanPlaceholder($output);
    }

    public static function getAttributeText()
    {
        if (empty(self::$instances['attribute'])) return null;
        $_content = file_get_contents(Helper::BASE_DIR . '/stubs/property-template.tmpl');
        $output = '';
        /** @var Property $property */
        foreach (self::$instances['attribute'] as $property) {
            if (!$property->getName()) continue;
            $content = $_content;
            if (!$property->getComment()) self::replaceEmpty('comments', $content);
            else $content = str_ireplace('${comments}', '* ' . $property->getComment(), $content);
            if (!$property->getType()) self::replaceEmpty('var', $content);
            else $content = str_ireplace('${var}', '* @var ' . (is_array($property->getType()) ? implode('|', $property->getType()) : $property->getType()), $content);
            $content = str_ireplace('${access}', ($property->getAccess() ?: 'public') . ' ', $content);
            $content = str_ireplace('${name}', $property->getName() . ' ', $content);
            $content = str_ireplace('${value}', var_export($property->getValue(), true), $content);
            $output .= $content . "\n";
        }
        return Helper::cleanPlaceholder($output);
    }

    /**
     * @return bool|float|int|string
     */
    public function getValue()
    {
        return $this->value;
    }

    protected static function replaceEmpty($property, &$content)
    {
        return $content = preg_replace("/(\s+)?\$\{" . $property . "\}\n/", '', $content);
    }

    /**
     * @param mixed $comment
     * @return Property
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param mixed $type
     * @return Property
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @param mixed $access
     * @return Property
     */
    public function setAccess($access)
    {
        $this->access = $access;
        return $this;
    }

    /**
     * @param mixed $name
     * @return Property
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return Property
     */
    public function setValue($value)
    {
        $this->value = is_string($value) ? preg_replace('/\s+/i', ' ', $value) : $value;
        return $this;
    }

    public function addType($type)
    {
        if (!is_array($this->type)) $this->type = empty($this->type) ? [] : [$this->type];
        $this->type[] = $type;
        return $this;
    }

    public function addValue($value, $key = null)
    {
        $value = is_string($value) ? preg_replace('/\s+/i', ' ', $value) : $value;
        if (!is_array($this->value)) $this->value = empty($this->value) ? [] : [$this->value];
        if (is_string($key) || (is_numeric($key) && (int)$key)) $this->value[$key] = $value;
        else $this->value[] = $value;
        return $this;
    }

    /**
     * @param string $prefix
     * @return Property
     */
    private function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return string
     */
    private function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @return string|string[]
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string|string[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string|string[] $params
     * @return Property
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }
}