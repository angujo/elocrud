<?php


namespace Angujo\Elocrud;


class Helper
{
    const BASE_DIR = __DIR__;

    /**
     * Replace a placeholder
     * @param $search
     * @param $replace
     * @param $content
     * @return string|string[]|null
     */
    public static function replacePlaceholder($search, $replace, $content)
    {
        return preg_replace('/\${' . $search . '}/i', $replace, $content);
    }

    /**
     * Method to clean pending placeholders and remove any linefeed
     * NOTE: Should be called after all replacements done.
     *
     * @param $content
     * @return string|string[]|null
     */
    public static function cleanPlaceholder($content)
    {
        return preg_replace('/\n+/', "\n", preg_replace('/\${(\w+)\}/', '', $content));
    }

    /**
     * Convert to carmelCase
     * @param $name
     * @return mixed
     */
    public static function carmelCase($name)
    {
        return str_ireplace('_', '', preg_replace_callback('/(^|_)([a-z])/i', function ($m) { return strtoupper($m[0]); }, $name));
    }

    public static function toWords($content)
    {
        return preg_replace('/([^a-z0-9]+)/i', ' ', $content);
    }

    /**
     * Get basename
     * @param $name
     * @return mixed
     */
    public static function baseName($name)
    {
        $name = array_filter(preg_split("/[\/]/i", str_ireplace('\\', '/', $name)), 'trim');
        return array_pop($name);
    }

    /**
     * Get corresponding classname equivalent
     * @param $name
     * @return string
     */
    public static function className($name)
    {
        return ucwords(self::carmelCase($name));
    }

    /**
     * Streamline var_export to ensure arrays do not have the numbering indices
     * @param $value
     * @return mixed|string|string[]|null
     */
    public static function valueExport($value)
    {
        if (is_array($value)) return preg_replace('/\s+/', ' ', preg_replace('/(([\d]+(\s+)?\=\>(\s+)?)|\s+)/i', ' ', var_export($value, true)));
        return var_export($value, true);
    }

    public static function makeDir($path)
    {
        $path = trim($path, "\\/");
        if (!file_exists($path) && !is_dir($path)) mkdir($path);
    }
}