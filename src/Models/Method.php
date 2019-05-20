<?php


namespace Angujo\Elocrud\Models;


class Method
{
    private $comment;
    private $returns = true;
    private $name;
    private $run;
    private $static = false;

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