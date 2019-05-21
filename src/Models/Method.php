<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;

class Method
{
    private $comment;
    private $returns = true;
    private $name;
    private $run;
    private $output;
    private $static = false;
    private $access = 'public';
    private static $me = [];
    private static $c_name = '_default_';

    protected function __construct($name)
    {
        $this->setName($name);
        self::$me[self::$c_name][] = $this;
    }

    public static function init($name)
    {
        if (!is_string($name) || !is_numeric($name)) return;
        self::$me[$name] = isset(self::$me[$name]) ? self::$me[$name] : [];
        self::$c_name = $name;
    }

    public static function fromForeignKey(ForeignKey $foreignKey)
    {

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
     * @return Method
     */
    public function setAccess(string $access): Method
    {
        if (!in_array($access, ['public', 'protected', 'private'])) $access = 'public';
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
     * @return Method
     */
    public function setStatic(bool $static): Method
    {
        $this->static = $static;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
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
     * @return Method
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param bool $string
     * @return mixed
     */
    public function getRun($string = false)
    {
        if (is_array($this->run) && $string) return implode("\t\n", $this->run);
        return $this->run;
    }

    /**
     * @param mixed $run
     * @return Method
     */
    public function setRun($run)
    {
        $this->run = $run;
        return $this;
    }

    public function addRun($run)
    {
        if (!is_array($this->run)) $this->run = $this->run ? [$this->run] : [];
        $this->run[] = $run;
        return $this;
    }
}