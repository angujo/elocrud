<?php


namespace Angujo\Elocrud;


use Angujo\DBReader\Models\ForeignKey;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Config
 *
 * @package Angujo\Elocrud
 *
 * @method static string model_class($name = null);
 * @method static string relation_name();
 * @method static string relation_remove_prx();
 * @method static string relation_remove_sfx();
 * @method static string eloquent_extension_name();
 * @method static string namespace();
 * @method static bool overwrite();
 * @method static bool date_base();
 * @method static bool db_directories();
 * @method static bool base_abstract();
 * @method static bool composite_keys();
 * @method static array soft_delete_columns();
 * @method static array excluded_tables();
 * @method static array only_tables();
 * @method static array create_columns();
 * @method static array update_columns();
 * @method static array type_casts();
 * @method static string column_relation_pattern();
 */
class Config
{
    const CLASS_NAME      = 'table';
    const COLUMN_NAME     = 'column';
    const CONSTRAINT_NAME = 'constraint';
    const AUTO            = 'auto';
    const NAME_REL_PERC   = 70;

    protected static $_defaults = [
        'relation_name' => self::AUTO,
        'column_relation_pattern' => '{table_name}_id',
        'soft_delete_columns' => ['deleted_at'],
        'excluded_tables' => ['migrations'],
        'only_tables' => [],
        'create_columns' => ['created_at', 'created'],
        'update_columns' => ['updated_at', 'updated'],
        'relation_remove_prx' => 'fk',
        'db_directories' => false,
        'date_base' => false,
        'overwrite' => false,
        'relation_remove_sfx' => 'id',
        'eloquent_extension_name' => 'EloquentExtension',
        'model_class' => Model::class,
        'base_dir' => Helper::BASE_DIR,
        'composite_keys' => true,
        'base_abstract' => true,
        'namespace' => 'App\Models',
        'type_casts' => ['type:tinyint(1)' => 'boolean', '%_json' => 'array', '%_array' => 'array', 'is_%' => 'boolean', 'type:date' => 'date:Y-m-d', 'type:datetime' => 'datetime:Y-m-d H:i:s', 'type:timestamp' => 'datetime:Y-m-d H:i:s'],
    ];

    public static function relationFunctionName(ForeignKey $foreignKey, $strictly = self::AUTO)
    {
        $strictly = null === $strictly || !is_string($strictly) ? self::relation_name() : $strictly;
        switch ($strictly) {
            case self::CLASS_NAME:
                $clsName = Helper::className($foreignKey->foreign_table_name);
                break;
            case self::CONSTRAINT_NAME:
                $clsName = Helper::cleanClassName($foreignKey->name);
                break;
            case self::COLUMN_NAME:
                $clsName = Helper::cleanClassName($foreignKey->isOneToOne() && !$foreignKey->is_unique ? $foreignKey->column_name : $foreignKey->foreign_column_name);
                break;
            default:
                $clsName = self::autoRelationNaming($foreignKey);
        }
        return lcfirst($clsName);
    }

    protected static function autoRelationNaming(ForeignKey $foreignKey)
    {
        if ($foreignKey->isOneToOne()) {
            if ($foreignKey->is_unique) {
                return 0 === strcasecmp(Helper::cleanClassName($foreignKey->foreign_column_name), Helper::className($foreignKey->table_name)) ?
                    Helper::className($foreignKey->foreign_table_name) :
                    Helper::cleanClassName($foreignKey->foreign_column_name);
            }
            return Helper::cleanClassName($foreignKey->column_name);
        }

        return 0 === strcasecmp(Helper::cleanClassName($foreignKey->foreign_column_name), Helper::className($foreignKey->table_name)) ?
            Helper::className($foreignKey->foreign_table_name) : Helper::cleanClassName($foreignKey->foreign_column_name);
    }

    public static function baseName($table_name, $schema_name)
    {
        return (self::db_directories() ? Helper::className($schema_name) : 'Base').Helper::className($table_name);
    }

    public static function baseSpace($schema_name)
    {
        return self::namespace().(self::base_abstract() ? '\BaseTables' : '');
    }

    public static function workSpace($schema_name)
    {
        return self::namespace().(self::db_directories() ? '\\'.Helper::className($schema_name) : '');
    }

    public static function base_namespace()
    {
        return self::namespace().'\BaseTables';
    }

    public static function __callStatic($method, $args)
    {
        if (!array_key_exists($method, self::$_defaults)) {
            return null;
        }
        if (!empty($args)) {
            self::$_defaults[$method] = array_shift($args);
        } else {
            self::$_defaults[$method] = config('elocrud.'.strtolower($method), self::$_defaults[$method]);
        }
        return self::$_defaults[$method];
    }

    public static function dir_path($path = null, $schema_name = null)
    {
        if (function_exists('config')) {
            self::$_defaults['base_dir'] = config('elocrud.base_dir', self::$_defaults['base_dir']);
        }
        if ($path) {
            self::$_defaults['base_dir'] = trim($path, "\\/");
        }
        return self::$_defaults['base_dir'].(self::db_directories() && $schema_name ? DIRECTORY_SEPARATOR.Helper::className($schema_name) : '');
    }

    public static function base_dir($path = null)
    {
        return self::dir_path($path).'\BaseTables';
    }

    public static function import(array $configs)
    {
        self::$_defaults = array_merge(self::$_defaults, array_intersect_key($configs, self::$_defaults));
    }

    public static function all()
    {
        return self::$_defaults;
    }
}