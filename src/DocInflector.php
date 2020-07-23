<?php


namespace Angujo\Elocrud;


use Doctrine\Inflector\InflectorFactory;

/**
 * Class DocInflector
 *
 * @package Angujo\Elocrud
 *
 * @method static string unaccent($string)
 * @method static string urlize($string)
 * @method static string singularize($string)
 * @method static string pluralize($string)
 * @method static string capitalize($string)
 * @method static string camelize($string)
 * @method static string classify($string)
 * @method static string tableize($string)
 */
class DocInflector
{
    private static $inflector;

    public static function __callStatic($method, $args)
    {
        if (empty(self::$inflector)) {
            self::$inflector = InflectorFactory::create()->build();
        }
        return call_user_func_array([self::$inflector, $method], $args);
    }
}