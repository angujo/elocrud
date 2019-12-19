<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\DBTable;
use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class HasOneEntry
 *
 * @package Angujo\Elocrud\Models
 */
class HasOneEntry
{
    /**
     * @var DBTable
     */
    private $table;

    /**
     * @var Method[]
     */
    private $methods = [];

    /**
     * @var string
     */
    private $namespace;

    /**
     * HasOneEntry constructor.
     *
     * @param DBTable $table
     * @param string  $namespace
     */
    protected function __construct(DBTable $table, $namespace)
    {
        $this->table     = $table;
        $this->namespace = $namespace;
    }

    /**
     * @param DBTable $table
     * @param string  $namespace
     *
     * @return Method[]|array
     */
    public static function methods(DBTable $table, $namespace)
    {
        return (new self($table, $namespace))->generate()->getMethods();
    }

    private function generate()
    {
        foreach ($this->table->foreign_keys_one_to_one as $foreignKey) {
            $this->methods[] = $this->getMethod($foreignKey);
        }
        return $this;
    }

    private function getMethod(ForeignKey $foreignKey)
    {
        $method = new Method(Config::relationFunctionName($foreignKey));
        $method->setReturns(true);
        $method->setNamespace($this->namespace);
        $method->setComment('Get '.Inflector::singularize(Helper::className($foreignKey->foreign_table_name)).' that is assigned to this '.Helper::className(Inflector::singularize($foreignKey->table_name)));
        $method->setOutput('$this->hasOne('.Helper::className($foreignKey->foreign_table_name).'::class, \''.$foreignKey->foreign_column_name.'\',\''.$foreignKey->column_name.'\');');
        $method->setOutputType(Helper::baseName(HasOne::class));
        $method->addImport(HasOne::class);
        Property::phpdocProperty($method->getName(), Helper::className($foreignKey->foreign_table_name), Helper::toWords($foreignKey->name))->addType('NULL');
        if (Config::base_abstract()) {
            $method->addImport(Config::namespace().'\\'.Helper::className($foreignKey->foreign_table_name));
        }
        return $method;
    }

    /**
     * @return Method[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}