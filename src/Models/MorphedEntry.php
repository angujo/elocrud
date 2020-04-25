<?php


namespace Angujo\Elocrud\Models;


use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Class MorphedEntry
 *
 * @package Angujo\Elocrud\Models
 */
class MorphedEntry extends Relation
{

    /**
     * @inheritDoc
     */
    protected function generate()
    {
        $morphItems = Morpher::getMorphItems($this->table);
        foreach ($morphItems as $morphItem) {
            $this->methods[] = $this->getMethod($morphItem);
        }
        return $this;
    }

    protected function getMethod(MorphItem $morphItem)
    {
        $method = new Method($prop_name = lcfirst(Inflector::classify($morphItem->isOneToOneRelation() ? Inflector::singularize($morphItem->getReturnTableName()) : Inflector::pluralize($morphItem->getReturnTableName()))));
        $method->addImport(Config::workSpace($morphItem->getReturnSchemaName()).'\\'.Helper::className($morphItem->getReturnTableName()));
        $method->setComment('Get all of '.Inflector::pluralize(Helper::className($morphItem->getReturnTableName())).' that are assigned to this '.Helper::className(Inflector::singularize($morphItem->getTableName())));
        if ($morphItem->isOneToOneRelation()) {
            $method->setComment('Get  '.Inflector::singularize(Helper::className($morphItem->getReturnTableName())).' that is assigned to this '.Helper::className(Inflector::singularize($morphItem->getTableName())));
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()))->addType('NULL');
            $method->addImport(MorphOne::class);
            $method->setOutputType(Helper::baseName(MorphOne::class));
            $method->setOutput('$this->morphOne('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
        } elseif ($morphItem->isManyToManyRelation()) {
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()).'[]')->addType(Helper::baseName(Collection::class));
            $method->addImport(Collection::class)->addImport(MorphToMany::class);
            $method->setOutputType(Helper::baseName(MorphToMany::class));
            if ($morphItem->isBy()) {
                $method->setOutput('$this->morphedByMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
            } else {
                $method->setOutput('$this->morphToMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
            }
        } else {
            Property::phpdocProperty($prop_name, Helper::className($morphItem->getReturnTableName()).'[]')->addType(Helper::baseName(Collection::class));
            $method->addImport(MorphMany::class)->addImport(Collection::class);
            $method->setOutputType(Helper::baseName(MorphMany::class));
            $method->setOutput('$this->morphMany('.Helper::className($morphItem->getReturnTableName()).'::class, \''.$morphItem->getName().'\');');
        }
        return $method;
    }
}