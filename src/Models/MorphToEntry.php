<?php


namespace Angujo\Elocrud\Models;


use Angujo\DBReader\Models\ForeignKey;
use Angujo\Elocrud\Config;
use Angujo\Elocrud\Helper;
use Doctrine\Common\Inflector\Inflector;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class MorphToEntry
 *
 * @package Angujo\Elocrud\Models
 */
class MorphToEntry extends Relation
{

    /**
     * @inheritDoc
     */
    protected function generate()
    {
        $morphs = Morpher::getTableMorphs($this->table->name, $this->table->schema_name);
        foreach ($morphs as $morph) {
            $this->methods[] = $this->getMethod($morph);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getMethod(Morph $morph)
    {
        $method = new Method($morph->getName());
        $method->setReturns(true);
        $method->setOutputType(Helper::baseName(MorphTo::class));
        $method->setOutput('$this->morphTo();');
        $method->addImport(MorphTo::class);
        $prop = Property::phpdocProperty($morph->getName())->addType('NULL');
        $cmt  = [];
        foreach ($morph->getItems() as $item) {
            if ($morph->getReferenced() && $item->isBy()) {
                continue;
            }
            $method->addImport(Config::workSpace($item->getSchemaName()).'\\'.Helper::className($item->getTableName()));
            $prop->addType(Helper::className($item->getTableName()));
            $cmt[] = Inflector::singularize(Helper::className($item->getTableName()));
        }
        $method->setComment('Get  '.implode('/', $cmt).' that is morphed to this '.Helper::className(Inflector::singularize($morph->getTableName())));
        return $method;
    }
}