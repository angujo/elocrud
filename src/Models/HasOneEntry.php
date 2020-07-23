<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\DocInflector;
use Angujo\Elocrud\Helper;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class HasOneEntry
 *
 * @package Angujo\Elocrud\Models
 */
class HasOneEntry extends Relation
{
    protected function generate()
    {
        foreach ($this->table->foreign_keys_one_to_many as $foreignKey) {
            if (!$foreignKey->foreign_column->is_unique) {
                continue;
            }
            $this->methods[] = $this->getMethod($foreignKey);
        }
        return $this;
    }

    protected function getMethod(ForeignKey $foreignKey)
    {
        $method = new Method(Config::relationFunctionName($foreignKey, Config::CLASS_NAME));
        $method->setReturns(true);
        $method->setNamespace($this->namespace);
        $method->setComment('Get '.DocInflector::singularize(Helper::className($foreignKey->foreign_table_name)).' that is assigned to this '.Helper::className(DocInflector::singularize($foreignKey->table_name)));
        $method->setOutput('$this->hasOne('.Helper::className($foreignKey->foreign_table_name).'::class'.$this->conformValues($foreignKey->foreign_table, $foreignKey->foreign_column_name, $foreignKey->column_name).');');
        $method->setOutputType(Helper::baseName(HasOne::class));
        $method->addImport(HasOne::class);
        Property::phpdocProperty($method->getName(), Helper::className($foreignKey->foreign_table_name), Helper::toWords($foreignKey->name))->addType('NULL');
        if (Config::base_abstract()) {
            $method->addImport(Config::workSpace($foreignKey->foreign_schema_name).'\\'.Helper::className($foreignKey->foreign_table_name));
        }
        return $method;
    }
}