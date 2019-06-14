<?php
/**
 * Created for elocrud.
 * User: Angujo Barrack
 * Date: 2019-05-25
 * Time: 12:29 PM
 */

/*
 * This is the configuration page for custom setup.
 * Worth noting that this library relies on directory levels for namespace
 * Accessible models will be directly placed on "base_dir"
 * Any subsequent extension or supporting directories will be set deeper into "base_dir"
 */

use Illuminate\Database\Eloquent\Model;

return [
    /*
     * Column names that are used for soft delete.
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'soft_delete_columns'     => ['deleted_at'],

    /*
     * Tables to be excluded from model generation
     */
    'excluded_tables'         => ['migrations'],
    /*
     *Tables to be run ONLY
     * The reset will be excluded
     */
    'only_tables'             => [],
    /*
     * Column names to mark as create columns
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'create_columns'          => ['created_at'],
    /*
     * Columns to be used as update
     * If different naming across tables, add them here.
     * NOTE: No two names should be on same table.
     */
    'update_columns'          => ['updated_at'],
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
    'relation_remove_prx'     => 'fk',
    /*
     * @see doc for 'relation_remove_prx' above
     */
    'relation_remove_sfx'     => 'id',
    /*
     * Class to be used for each and every generated model
     * Ensure it is or extends \Illuminate\Database\Eloquent\Model::class
     */
    'model_class'             => Model::class,
    /*
     * Directory path to put the models
     */
    'base_dir'                => app_path('Models'),
    /*
     * Enable composite keys in laravel
     */
    'composite_keys'          => true,
    /*
     * Name of class to be used in customizing Eloquent to accommodate composite keys.
     */
    'eloquent_extension_name' => 'EloquentExtension',
    /*
     * Create abstract classes to act as BASE Class for teh tables
     */
    'base_abstract'           => true,
    /*
     * Namespace for the models
     */
    'namespace'               => 'App\Models',
    /*
     * Pivoting allows Laravel's hasManyThrough to be extended.
     * Currently, we extend upto 3 levels deep
     * NOTE: This relies on foreign keys, so will not work if not set on tables.
     * Any value greater than 3 will be considered as 3 and any less than zero converted to 0
     */
    'pivot_level'             => 0,
    /*
     * This is the nested namespace from the "base_dir" above
     * to be used for pivot tables
     */
    'pivot_extension_name'    => 'Pivots',
    /*
     * @link https://laravel.com/docs/eloquent-relationships#polymorphic-relations
     * Add polymorphic tables as well.
     * To set this up column naming should be as described in laravel
     * On the "_type" list all tables to be referenced on the column's comments (Optional)
     * The primaryKey of the referenced table will be considered by default, otherwise,
     * the column name should be indicated if different from primaryKey
     * If the morph is not one to many, an indicator should be added i.e. 1-1=One to One, 1-0=One to Many, 0-0=Many to Many, with One to Many being the default
     *
     * Syntax 1: table_name1,table_name2,.... table_nameN -- primary column used and one to many assumed
     * Syntax 2: table_name1:column_name1,table_name2:column_name2,... table_nameN:column_nameN -- primary column not used but one to many assumed
     * Syntax 3: table_name1:1-1,table_name2:1,... table_nameN:1-1, -- One to One reference with primaryKey used
     * Syntax 4: table_name1:column_name1:1-1,table_name2:column_name2:1-1,... table_nameN:column_nameN:1-1 -- primary column not used and one to one used
     * Syntax 5: table_name1:column_name1:0-0,table_name2:column_name2:0-0,... table_nameN:column_nameN:0-0 -- primary column not used and many to many used
     *
     * For Many to Many Polymorph,
     * The "_id" column's comment should contain third table's reference.
     * Otherwise, the table should have three columns where the third will be assumed as the end point table.
     *
     * In addition to above, you can add schema/database name, enclosed in a bracket, to the table name i.e. (db_name)table_name1,...
     * This is in case the referenced table belongs to a different schema
     *
     * ONLY POSSIBLE FOR NON-COMPOSITE PRI-KEYs
     * All listing should be separated by a comma
     */
    'polymorph'               => true,
    /*
     * Type Casting for properties and database values.
     * You can cast using a column name or data type.
     * To cast data type e.g. tinyint(1) to be boolean,
     * start with "type:" followed by the type i.e. "type:tinyint(1)"=>'boolean'
     */
    'type_casts'              => ['type:tinyint(1)' => 'boolean', '%_json' => 'array', '%_array' => 'array', 'is_%' => 'boolean', 'type:date' => 'date:Y-m-d', 'type:datetime' => 'datetime:Y-m-d H:i:s'],
];