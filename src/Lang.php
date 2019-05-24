<?php

namespace Angujo\Elocrud;

class Lang
{
    private static $to_plural = [
        '/(s|ss|sh|ch|x|z|o|is)$/i' => '$1es',
        '/(fe|f)$/i'                => '$1ves',
        '/(us)$/i'                  => '$1i',
        '/(on)$/i'                  => '$1a',
        '/([^aeiou]y)$/i'           => '$1ies',
        '/$/i'                      => '$1s',
    ];
    private static $to_single = [
        '/(z)zes$/i'                                                       => '$1',
        '/(matr)ices$/i'                                                   => '$1ix',
        '/(vert|ind)ices$/i'                                               => '$1ex',
        '/i$/i'                                                            => 'us',
        '/(cris|ax|test|s)es$/i'                                           => '$1is',
        '/(shoe)s$/i'                                                      => '$1',
        '/(o)es$/i'                                                        => '$1',
        '/(bus)es$/i'                                                      => '$1',
        '/([m|l])ice$/i'                                                   => '$1ouse',
        '/(x|ch|ss|sh|s)es$/i'                                             => '$1',
        '/([^aeiouy]|qu|v)ies$/i'                                          => '$1y',
        '/([lr])ves$/i'                                                    => '$1f',
        '/(li|wi|kni)ves$/i'                                               => '$1fe',
        '/(shea|loa|lea|thie)ves$/i'                                       => '$1f',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
        '/([ti])a$/i'                                                      => '$1um',
        '/(h|bl)ouses$/i'                                                  => '$1ouse',
        '/(corpse)s$/i'                                                    => '$1',
        '/(us)es$/i'                                                       => '$1',
        '/s$/i'                                                            => '', ];
    private static $irregular = ['fungus' => 'fungi', 'nucleus' => 'nuclei', 'cactus' => 'cacti', 'alumnus' => 'alumni', 'octopus' => 'octopi', 'ox' => 'oxen'];
    private static $uncountable = ['accommodation', 'advertising', 'air', 'aid', 'advice', 'anger', 'art', 'assistance', 'bread', 'business', 'butter', 'calm', 'cash', 'chaos', 'cheese ', 'childhood', 'clothing',
        'coffee ', 'content', 'corruption', 'courage', 'currency', 'damage', 'danger ', 'darkness', 'data', 'determination', 'economics', 'education', 'electricity', 'employment', 'energy', 'entertainment', 'enthusiasm',
        'equipment', 'evidence', 'failure ', 'fame', 'fire', 'flour', 'food', 'freedom', 'friendship ', 'fuel', 'furniture', 'fun', 'genetics', 'gold', 'grammar', 'guilt', 'hair', 'happiness', 'harm', 'health', 'heat',
        'help', 'homework', 'honesty', 'hospitality', 'housework', 'humour', 'imagination ', 'importance', 'information', 'innocence', 'intelligence', 'jealousy', 'juice', 'justice', 'kindness', 'knowledge', 'labour',
        'lack', 'laughter', 'leisure', 'literature', 'litter', 'logic', 'love ', 'luck', 'magic', 'management', 'metal ', 'milk', 'money', 'motherhood', 'motivation', 'music', 'nature', 'news', 'nutrition', 'obesity',
        'oil', 'old age', 'oxygen', 'paper', 'patience', 'permission', 'pollution', 'poverty', 'power ', 'pride', 'production', 'progress', 'pronunciation', 'publicity', 'punctuation', 'quality ', 'quantity ', 'racism',
        'rain', 'relaxation ', 'research', 'respect', 'rice', 'room ', 'rubbish', 'safety', 'salt', 'sand', 'seafood', 'shopping', 'silence ', 'smoke', 'snow', 'software', 'soup ', 'speed', 'spelling', 'stress', 'sugar',
        'sunshine', 'tea ', 'tennis', 'time', 'tolerance', 'trade', 'traffic', 'transportation', 'travel', 'trust', 'understanding', 'unemployment', 'usage', 'violence', 'vision ', 'warmth', 'water', 'wealth', 'weather',
        'weight ', 'welfare', 'wheat', 'width', 'wildlife', 'wisdom', 'wood ', 'work', 'yoga', 'youth', 'series', ];

    public static function toPlural($word)
    {
        if (in_array($word, self::$uncountable)) {
            return $word;
        }
        foreach (self::$irregular as $plur => $sing) {
            if (0 === strcasecmp($word, $sing)) {
                return $plur;
            }
        }
        foreach (self::$to_plural as $regex => $pattern) {
            if (preg_match($regex, $word)) {
                return preg_replace($regex, $pattern, $word);
            }
        }

        return $word;
    }

    public static function toSingle($word)
    {
        if (in_array($word, self::$uncountable)) {
            return $word;
        }
        foreach (self::$irregular as $plur => $sing) {
            if (0 === strcasecmp($word, $plur)) {
                return $sing;
            }
        }
        foreach (self::$to_single as $regex => $pattern) {
            if (preg_match($regex, $word)) {
                return preg_replace($regex, $pattern, $word);
            }
        }

        return $word;
    }
}
