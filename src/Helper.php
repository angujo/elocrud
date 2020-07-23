<?php


namespace Angujo\Elocrud;


//use Doctrine\Common\Inflector\Inflector;

class Helper
{
    const BASE_DIR = __DIR__;

    /**
     * Replace a placeholder
     *
     * @param $search
     * @param $replace
     * @param $content
     *
     * @return string|string[]|null
     */
    public static function replacePlaceholder($search, $replace, $content)
    {
        return preg_replace('/\${'.$search.'}/i', $replace, $content);
    }

    /**
     * Method to clean pending placeholders and remove any linefeed
     * NOTE: Should be called after all replacements done.
     *
     * @param $content
     *
     * @return string|string[]|null
     */
    public static function cleanPlaceholder($content)
    {
        return preg_replace('/\n+/', "\n", preg_replace('/\${(\w+)\}/', '', $content));
    }

    /**
     * Convert to carmelCase
     *
     * @param $name
     *
     * @return mixed
     */
    public static function carmelCase($name)
    {
        return str_ireplace('_', '', preg_replace_callback('/(^|_)([a-z])/i', function($m){ return strtoupper($m[0]); }, $name));
    }

    public static function toWords($content)
    {
        return preg_replace('/([^a-z0-9]+)/i', ' ', $content);
    }

    /**
     * Get basename
     *
     * @param $name
     *
     * @return mixed
     */
    public static function baseName($name)
    {
        $name = array_filter(preg_split("/[\/]/i", str_ireplace('\\', '/', $name)), 'trim');
        return array_pop($name);
    }

    /**
     * Get corresponding classname equivalent
     *
     * @param $name
     *
     * @return string
     */
    public static function className($name)
    {
        return DocInflector::classify(DocInflector::singularize($name));// Inflector::classify($name);// Lang::toSingle(ucwords(self::carmelCase($name)));
    }

    /**
     * Remove traces of column identifier
     * @param $name
     *
     * @return string
     */
    public static function cleanClassName($name)
    {
        return Helper::className(trim(preg_replace('/((^'.Config::relation_remove_prx().'(_)?)|((_)?('.Config::relation_remove_prx().'|'.Config::relation_remove_sfx().')$))/i', '', $name), "_"));
    }

    /**
     * Streamline var_export to ensure arrays do not have the numbering indices
     *
     * @param $value
     *
     * @return mixed|string|string[]|null
     */
    public static function valueExport($value)
    {
        if (is_array($value)) {
            return preg_replace('/\s+/', ' ', preg_replace('/(([\d]+(\s+)?\=\>(\s+)?)|\s+)/i', ' ', var_export($value, true)));
        }
        return var_export($value, true);
    }

    public static function makeDir($path)
    {
        $path = trim($path, "\\/");
        if (!file_exists($path) && !is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    public static function isMorphId($name)
    {
        return 0 === strcasecmp('_id', substr($name, -3));
    }

    public static function isMorphType($name)
    {
        return 0 === strcasecmp('_type', substr($name, -5));
    }

    /**
     * @param $name
     *
     * @return string|null
     */
    public static function morphName($name)
    {
        if (self::isMorphId($name)) {
            return preg_replace('/(_id)$/i', '', $name);
        }
        if (self::isMorphType($name)) {
            return preg_replace('/(_type)$/i', '', $name);
        }
        return null;
    }
}