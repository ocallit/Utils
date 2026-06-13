<?php

namespace Ocallit\Utils;

class Str {

    public static function superTrim($string): string {
        if($string === null)
            return "";
        return mb_trim(preg_replace('/\s+/u', ' ',(string)$string));
    }
    
    public static function snake2Pascal($string): string {
        if($string === null)
            return "";
        return str_replace(' ', '',
            mb_convert_case(
                str_replace('_', ' ', mb_strtolower($string, 'UTF-8')),
                MB_CASE_TITLE, 'UTF-8'
            )
        );
    }

    public static function snake2Camel($string): string {
        if($string === null)
            return "";
        return mb_lcfirst(
            str_replace(' ', '',
                mb_convert_case(
                    str_replace('_', ' ', mb_strtolower($string, 'UTF-8')),
                    MB_CASE_TITLE, 'UTF-8'
                )
            ), MB_CASE_LOWER, 'UTF-8'
        );
    }

    public static function camel2Snake($string): string {
        if($string === null)
            return "";

        $string = preg_replace('/(\p{Lu}+)(\p{Lu}\p{Ll})/u', '$1_$2', $string);
        //@TODO what to do on null
        if($string === null)
            return "";

        $string = preg_replace('/(?<=[\p{Ll}\p{N}])(\p{Lu})/u', '_$1', $string);
        if($string === null)
            return "";

        return mb_strtolower($string, 'UTF-8');
    }

    public static function pascal2snake($string): string { return camel2Snake($string); }

    public static function snake2Kebab(?string $string): string {
        if($string === null)
            return "";

        return str_replace('_', '-', $string);
    }

    public static function kebab2Snake(?string $string): string {
        if($string === null)
            return "";

        return str_replace('-', '_', $string);
    }

    public static function slugify(?string $string, string $separator = '-'): string {
        if($string === null)
            return "";

        $string = unaccent($string);
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('/[^\p{L}\p{N}]+/u', $separator, $string);
        if($string === null)
            return "";

        return trim($string, $separator);
    }
    
    public static function toLabel(?string $string): string {
        if($string === null)
            return "";
        $string = superTrim(str_replace(['_', '-'], ' ', camel2Snake($string)));
        //@todo suffix fixer like _id to what?
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }

    public static function unaccent(?string $string): string {
        if($string === null)
            return "";

        if(function_exists('transliterator_transliterate')) {
            $result = transliterator_transliterate(
                'NFD; [:Nonspacing Mark:] Remove; NFC',
                $string
            );

            return $result === false ? $string : $result;
        }

        return strtr($string, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
    }

    public static function topWords(string $text, int $amount = 5): array {
        $top = [];
        $words = preg_split('~[^[:alnum:]]+~is', strtolower($text), -1, PREG_SPLIT_NO_EMPTY);
        foreach($words as $word) {
            if(!isset($top[$word]))
                $top[$word] = 1;
            else
                $top[$word]++;
        }
        arsort($top);
        return array_slice($top, 0, $amount, true);
    }
  
  }
    
    
}
