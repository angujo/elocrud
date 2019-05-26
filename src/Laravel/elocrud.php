<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:29 PM
 */

return [
    /*
     * Column names that are used for soft delete.
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'soft_delete_columns' => ['deleted_at'],

    /*
     * Tables to be excluded from model generation
     */
    'excluded_tables' => ['migrations'],
    /*
     *Tables to be run ONLY
     * The reset will be excluded
     */
    'only_tables' => [],
    /*
     * Column names to mark as create columns
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'create_columns' => ['created_at'],
    /*
     * Columns to be used as update
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'update_columns' => ['updated_at'],
    /*
     * Prefix used to mark relationship column names
     * Depends on naming conventions
     * E.g. relationship column might be prefixed with "fk" e.g. fk_customer
     * or suffixed with "id" e.g. customer_id
     * or bother fk_customer_id
     * This is essential for naming of relationship properties
     * The naming is based on relation table name and column.
     * E.g. for 1-1 relation the table name is used as in class
     * for 1-N relation, the column name will be used
     * Therefore, for column "fk_customer_id", relation will be "customer"
     * It is recommended to have a prefix/suffix/both to separate the column value from the relation,
     * otherwise, might break the models
     */
    'relation_remove_prx' => 'fk',
    /*
     * @see doc for 'relation_remove_prx' above
     */
    'relation_remove_sfx' => 'id',
    /*
     * Class to be used for each and every generated model
     * Ensure it is or extends \Illuminate\Database\Eloquent\Model::class
     */
    'model_class' => \Illuminate\Database\Eloquent\Model::class,
    /*
     * Directory path to put the models
     */
    'base_dir' => app_path('Models'),
    /*
     * Enable composite keys in laravel
     */
    'composite_keys' => true,
    /*
     * Name of class to be used in customizing Eloquent to accommodate composite keys.
     */
    'eloquent_extension_name' => 'EloquentExtension',
    /*
     * Create abstract classes to act as BASE Class for teh tables
     */
    'base_abstract' => true,
    /*
     * Namespace for the models
     */
    'namespace' => 'App\Models',
    /*
     * Type Casting for properties and database values.
     * You can cast using a column name of data type.
     * To cast data type e.g. tinyint(1) to be boolean,
     * start with "type:" followed by the type i.e. "type:tinyint(1)"=>'boolean'
     */
    'type_casts' => ['type:tinyint(1)' => 'boolean', '%_json' => 'array', '%_array' => 'array', 'is_%' => 'boolean'],
];