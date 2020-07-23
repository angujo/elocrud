<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\DocInflector;
use Angujo\Elocrud\Helper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class HasOneEntry
 *
 * @package Angujo\Elocrud\Models
 */
class HasManyEntry extends Relation
{
    protected function generate()
    {
        foreach ($this->table->foreign_keys_one_to_many as $foreignKey) {
            if ($foreignKey->foreign_column->is_unique) {
                continue;
            }
            $this->methods[] = $this->getMethod($foreignKey);
        }
        return $this;
    }

    protected function getMethod(ForeignKey $foreignKey)
    {
        $name = $foreignKey->foreign_column->comment && 1 === preg_match('/({)([a-z]\w+)(})/i', $foreignKey->foreign_column->comment, $matches) ? $matches[2] : $foreignKey->foreign_table_name;

        $method = new Method(DocInflector::pluralize(lcfirst(Helper::className($name))));
        $method->setReturns(true);
        $method->setNamespace($this->namespace);
        $method->setComment('Get all '.DocInflector::pluralize(Helper::className($foreignKey->foreign_table_name)).' that are assigned to this '.Helper::className(DocInflector::singularize($foreignKey->table_name)));
        $method->setOutput('$this->hasMany('.Helper::className($foreignKey->foreign_table_name).'::class'.$this->conformValues($foreignKey->foreign_table, $foreignKey->foreign_column_name, $foreignKey->column_name).');');
        $method->setOutputType(Helper::baseName(HasMany::class));
        $method->addImport(Collection::class)->addImport(HasMany::class);
        Property::phpdocProperty($method->getName(), Helper::className($foreignKey->foreign_table_name).'[]', Helper::toWords($foreignKey->name))->addType('Collection');
        if (Config::base_abstract()) {
            $method->addImport(Config::workSpace($foreignKey->foreign_schema_name).'\\'.Helper::className($foreignKey->foreign_table_name));
        }
        return $method;
    }
}