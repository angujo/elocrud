<?php


namespace Angujo\Elocrud;


class Helper
{
    const BASE_DIR = __DIR__;

    public static function replacePlaceholder($search, $replace, $content)
    {
        return preg_replace('/\${' . $search . '}/i', $replace, $content);
    }

    public static function cleanPlaceholder($content)
    {
        return preg_replace('/\${(\w+)\}/', '', $content);
    }

    public static function carmelCase($name)
    {
        return str_ireplace('_', '', preg_replace_callback('/(^|_)([a-z])/i', function ($m) { return strtoupper($m[0]); }, $name));
    }

    public static function toWords($content)
    {
        return preg_replace('/([^a-z0-9]+)/i', ' ', $content);
    }

    public static function baseName($name)
    {
        $name = array_filter(preg_split("/[\/]/i", str_ireplace('\\', '/', $name)), 'trim');
        return array_pop($name);
    }

    public static function className($name)
    {
        return ucwords(self::carmelCase($name));
    }

    public static function valueExport($value)
    {
        if (is_array($value)) return preg_replace('/\s+/', ' ', preg_replace('/(([\d]+(\s+)?\=\>(\s+)?)|\s+)/i', ' ', var_export($value, true)));
        return var_export($value, true);
    }
}