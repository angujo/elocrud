<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Models\ForeignKey;

/**
 * Class Config
 * @package Angujo\Elocrud
 *
 * @method static string model_class($name = null);
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
    const AUTO = 'auto';

    private static $_defaults = [
        'relation_name' => self::AUTO,
        'relation_remove_prx' => 'fk',
        'relation_remove_sfx' => 'id',
        'eloquent_extension_name' => 'EloquentExtension',
        'model_class' => \Illuminate\Database\Eloquent\Model::class,
        'base_dir' => Helper::BASE_DIR,
        'composite_keys' => true,
        'namespace' => 'App\Models',
    ];

    public static function relationFunctionName(ForeignKey $foreignKey, $strictly = self::AUTO)
    {
        $strictly = null === $strictly || !is_string($strictly) ? self::relation_name() : $strictly;
        switch ($strictly) {
            case self::CLASS_NAME:
                $clsName = Helper::className($foreignKey->foreign_table_name);
                break;
            case self::CONSTRAINT_NAME:
                $clsName = self::cleanClassName($foreignKey->name);
                break;
            case self::COLUMN_NAME:
                $clsName = self::cleanClassName($foreignKey->isOneToOne() && !$foreignKey->unique_colmn ? $foreignKey->column_name : $foreignKey->foreign_column_name);
                break;
            default:
                $clsName = self::autoRelationNaming($foreignKey);
        }
        return lcfirst($clsName);
    }

    protected static function autoRelationNaming(ForeignKey $foreignKey)
    {
        if ($foreignKey->isOneToOne()) {
            if ($foreignKey->unique_colmn) {
                return 0 === strcasecmp(self::cleanClassName($foreignKey->foreign_column_name), Helper::className($foreignKey->table_name)) ?
                    Helper::className($foreignKey->foreign_table_name) :
                    self::cleanClassName($foreignKey->foreign_column_name);
            }
            return self::cleanClassName($foreignKey->column_name);
        }

        return 0 === strcasecmp(self::cleanClassName($foreignKey->foreign_column_name), Helper::className($foreignKey->table_name)) ?
            Helper::className($foreignKey->foreign_table_name) :self::cleanClassName($foreignKey->foreign_column_name);
    }

    protected static function cleanClassName($name)
    {
        return Helper::className(trim(preg_replace('/((^' . self::relation_remove_prx() . '(_)?)|((_)?(' . self::relation_remove_prx() . '|' . self::relation_remove_sfx() . ')$))/i', '', $name), "_"));
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