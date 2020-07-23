<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\DocInflector;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Class HasManyThroughEntry
 *
 * @package Angujo\Elocrud\Models
 */
class HasManyThroughEntry extends Relation
{

    /**
     * @inheritDoc
     */
    protected function generate()
    {
        foreach ($this->table->foreign_keys_one_to_many as $foreignKey) {
            foreach ($foreignKey->foreign_table->foreign_keys_one_to_many as $foreignKey_int) {
                $this->methods[] =$this->get_Method($foreignKey, $foreignKey_int);
            }
        }
        return $this;
    }


    /**
     * @param ForeignKey $foreignKey
     * @param ForeignKey $foreignKey_int
     *
     * @return Method
     */
    protected function get_Method(ForeignKey $foreignKey, ForeignKey $foreignKey_int)
    {
        $name=DocInflector::singularize(lcfirst(Helper::cleanClassName($foreignKey->foreign_table_name))).DocInflector::pluralize(ucfirst(Helper::className($foreignKey_int->foreign_table_name)));
        $method = new Method($name);
        $method->setReturns(true);
        $method->setNamespace($this->namespace);
        $method->setComment('Get all ['.DocInflector::pluralize(Helper::className($foreignKey_int->foreign_table_name)).'] accessible via ['.Inflector::pluralize(Helper::className($foreignKey->foreign_table_name)).'] that are assigned to this '.Helper::className(Inflector::singularize($foreignKey->table_name)));
        $method->setOutput('$this->hasManyThrough('.Helper::className($foreignKey_int->foreign_table_name).'::class, '.Helper::className($foreignKey->foreign_table_name).'::class, \''.$foreignKey->foreign_column_name.'\', \''.$foreignKey_int->foreign_column_name.'\', \''.$foreignKey->column_name.'\', \''.$foreignKey_int->column_name.'\');');
        $method->setOutputType(Helper::baseName(HasManyThrough::class));
        $method->addImport(HasManyThrough::class);
        Property::phpdocProperty($method->getName(), Helper::className($foreignKey_int->foreign_table_name).'[]', Helper::toWords($foreignKey_int->name));
        if (Config::base_abstract()) {
            $method->addImport(Config::workSpace($foreignKey->foreign_schema_name).'\\'.Helper::className($foreignKey->foreign_table_name))
                   ->addImport(Config::workSpace($foreignKey_int->foreign_schema_name).'\\'.Helper::className($foreignKey_int->foreign_table_name));
        }
        return $method;
    }

    /**
     * @inheritDoc
     */
    protected function getMethod(ForeignKey $foreignKey)
    {
        // TODO: Implement getMethod() method.
    }
}