<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Models\ForeignKey;

/**
 * Class Config
 * @package Angujo\Elocrud
 *
 * @method static string relation_name();
 * @method static string relation_remove_prx();
 * @method static string relation_remove_sfx();
 * @method static string namespace();
 */
class Config
{
    const CLASS_NAME = 'table';
    const COLUMN_NAME = 'column';
    const CONSTRAINT_NAME = 'constraint';

    private static $_defaults = ['relation_name' => self::CLASS_NAME,
        'relation_remove_prx' => 'fk_',
        'relation_remove_sfx' => '_id',
        'namespace' => 'App\Models'];

    public static function relationFunctionName(ForeignKey $foreignKey, $strictly = null)
    {
        $strictly = null === $strictly || !is_string($strictly) ? self::relation_name() : $strictly;
        switch ($strictly) {
            case self::CLASS_NAME:
                $clsName = Helper::className($foreignKey->foreign_table_name);
                break;
            case self::CONSTRAINT_NAME:
                $clsName = Helper::className(trim(preg_replace("/((^fk(_)?)|((_)?fk$))/i", '', $foreignKey->name), "_"));
                break;
            case self::COLUMN_NAME:
                $clsName = Helper::className(trim(preg_replace('/((^' . self::relation_remove_prx() . '(_)?)|((_)?' . self::relation_remove_sfx() . '$))/i', '', $foreignKey->foreign_column_name), "_"));
                break;
            default:
                //TODO make this intelligent option to check on relationship and name this function accordingly
                return self::relationFunctionName($foreignKey, self::CLASS_NAME);
        }
        return lcfirst($clsName);
    }

    public static function __callStatic($method, $args)
    {
        if (!array_key_exists($method, self::$_defaults)) return null;
        if (function_exists('config')) return config('elocrud.' . $method, self::$_defaults[$method]);
        return self::$_defaults[$method];
    }
}