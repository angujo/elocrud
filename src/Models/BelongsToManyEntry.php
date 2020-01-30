<?php


namespace Angujo\Elocrud\Models;


use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class HasOneEntry
 *
 * @package Angujo\Elocrud\Models
 */
class BelongsToManyEntry extends Relation
{
    protected function generate()
    {
        $rels = ManyToMany::getManyRelations($this->table);
        foreach ($rels as $rel) {
            $this->methods[] = $this->getMethod($rel);
        }
        return $this;
    }

    protected function getMethod(ManyToMany $toMany)
    {
        $method = new Method($name = lcfirst(Inflector::pluralize(Inflector::classify($toMany->getRefTableName()))));
        Property::phpdocProperty($name, Helper::className($toMany->getRefTableName()).'[]')->addType(Helper::baseName(Collection::class));
        $method->addImport(Collection::class);
        $method->setComment('Get '.Inflector::pluralize(Helper::className($toMany->getRefTableName())).' that belong to this '.Helper::className(Inflector::singularize($toMany->getTableName())));
        $method->setReturns(true);
        $method->setOutputType(Helper::baseName(BelongsToMany::class));
        $method->setOutput('$this->belongsToMany('.Helper::className($toMany->getRefTableName()).'::class, \''.$toMany->getSchemaName().'.'.$toMany->getName().'\', \''.$toMany->getColumnName().'\', \''.$toMany->getRefColumnName().'\');');
        $method->addImport(BelongsToMany::class);
        $method->addImport(Config::namespace().'\\'.Helper::className($toMany->getRefTableName()));
        return $method;
    }
}