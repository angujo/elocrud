<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Models\ForeignKey;

/**
 * Class Config
 * @package Angujo\Elocrud
 *
 * @method static string model_class($name=null);
 * @method static string relation_name();
 * @method static string relation_remove_prx();
 * @method static string relation_remove_sfx();
 * @method static string eloquent_extension_name();
 * @method static string namespace();
 * @method static bool composite_keys();
 */
class Config
{
    const CLASS_NAME = 'table';
    const COLUMN_NAME = 'column';
    const CONSTRAINT_NAME = 'constraint';

    private static $_defaults = [
        'relation_name' => self::CONSTRAINT_NAME,
        'relation_remove_prx' => 'fk',
        'relation_remove_sfx' => 'id',
        'eloquent_extension_name' => 'EloquentExtension',
        'model_class' => \Illuminate\Database\Eloquent\Model::class,
        'base_dir' => Helper::BASE_DIR,
        'composite_keys' => true,
        'namespace' => 'App\Models',
    ];

    public static function relationFunctionName(ForeignKey $foreignKey, $strictly = null)
    {
        $strictly = null === $strictly || !is_string($strictly) ? self::relation_name() : $strictly;
        switch ($strictly) {
            case self::CLASS_NAME:
                $clsName = Helper::className($foreignKey->foreign_table_name);
                break;
            case self::CONSTRAINT_NAME:
                $clsName = Helper::className(trim(preg_replace('/((^' . self::relation_remove_prx() . '(_)?)|((_)?(' . self::relation_remove_prx() . '|' . self::relation_remove_sfx() . ')$))/i', '', $foreignKey->name), "_"));
                break;
            case self::COLUMN_NAME:
                $clsName = Helper::className(trim(preg_replace('/((^' . self::relation_remove_prx() . '(_)?)|((_)?' . self::relation_remove_sfx() . '$))/i', '', $foreignKey->column_name), "_"));
                break;
            default:
                return self::relationFunctionName($foreignKey, self::COLUMN_NAME);
        }
        return lcfirst($clsName);
    }

    public static function __callStatic($method, $args)
    {
        if (!array_key_exists($method, self::$_defaults)) return null;
        if (function_exists('config')) return config('elocrud.' . $method, self::$_defaults[$method]);
        if (!empty($args)) self::$_defaults[$method] = array_shift($args);
        return self::$_defaults[$method];
    }

    public static function base_dir($path = null)
    {
        if (null === $path) return self::$_defaults['base_dir'];
        if (function_exists('config')) self::$_defaults['base_dir'] = config('elocrud.base_dir', self::$_defaults['base_dir']);
        if ($path) self::$_defaults['base_dir'] = trim($path, "\\/");
        return self::$_defaults['base_dir'];
    }
}