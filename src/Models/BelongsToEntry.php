<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class HasOneEntry
 *
 * @package Angujo\Elocrud\Models
 */
class BelongsToEntry extends Relation
{
    protected function generate()
    {
        foreach ($this->table->foreign_keys_one_to_one as $foreignKey) {
            $this->methods[] = $this->getMethod($foreignKey);
        }
        return $this;
    }

    protected function getMethod(ForeignKey $foreignKey)
    {
        $method = new Method(Config::relationFunctionName($foreignKey, Config::CLASS_NAME));
        $method->setReturns(true);
        $method->setNamespace($this->namespace);
        $method->setComment('Get '.Inflector::singularize(Helper::className($foreignKey->foreign_table_name)).' that is assigned to this '.Helper::className(Inflector::singularize($foreignKey->table_name)));
        $method->setOutput('$this->belongsTo('.Helper::className($foreignKey->foreign_table_name).'::class'.$this->conformValues($foreignKey->foreign_table, $foreignKey->column_name, $foreignKey->foreign_column_name).');');
        $method->setOutputType(Helper::baseName(BelongsTo::class));
        $method->addImport(BelongsTo::class);
        $prop = Property::phpdocProperty($method->getName(), Helper::className($foreignKey->foreign_table_name), Helper::toWords($foreignKey->name));
        if ($foreignKey->column->is_nullable) {
            $prop->addType('NULL');
        }
        if (Config::base_abstract()) {
            $method->addImport(Config::namespace().'\\'.Helper::className($foreignKey->foreign_table_name));
        }
        return $method;
    }
}