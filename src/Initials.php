<?php

/**
 * scores each row by matching those initials against the starts of words in the row $campo key,
 * then sorts best matches first and alphabetically as a tie-breaker
 * Modifies $data:
 *  - _initialsNormalized: normalized uppercase searchable text
 *  - _initialsMatchPriority: integer match score used for sorting
 *
 *
 * @example
 *    $testData = [ ['label' => 'Anable abarca'], ['label' => 'Anico Armenta Andul'], ['label' => 'Blater Anico Andul'],
 *      ['label' => 'Willian Anico Andul ancronico'], ];
 *    Initials::sort($testData, ["an","an"]);
 *    print_r($testData);
 *
 * @phpstan-type InitialsMatchedRow array<int|string, mixed>&array{
 *       _initialsNormalized: string,
 *       _initialsMatchPriority: int
 *   }
 */
final /*static*/ class Initials {
    private const string KEY_NORMALIZED = '_initialsNormalized';
    private const string KEY_PRIORITY = '_initialsMatchPriority';

    /**
     * sort: best match to the initial's array, then alphabetically
     * Modifies $data:
     *   - _initialsNormalized: normalized uppercase searchable text
     *   - _initialsMatchPriority: integer match score used for sorting
     *
     * @param array<int|string, array<int|string, mixed>> $data
     * @param array<int|string,string> $initialsArray
     * @param string|int $campo
     * @throws Exception
     */
    public static function sort(array &$data, array $initialsArray, string|int $campo = 'label'):void {
        $initials = [];
        foreach($initialsArray as $d) {
            $initial = trim($d);
            if($initial !== "")
                $initials[] = strtoupper(self::unAccent(trim($initial)));
        }
        if(empty($initials) || empty($data))
            return;

        $starts =  reset($initials);

        self::matchPriority($data, $initials, $campo);
        /** @var array<int|string, InitialsMatchedRow> $data */
        uasort($data, static function($rowA, $rowB) use ($starts): int {
            $priority = $rowB[self::KEY_PRIORITY] <=> $rowA[self::KEY_PRIORITY];
            if($priority !== 0)
                return $priority;
            $a = $rowA[self::KEY_NORMALIZED];
            $b = $rowB[self::KEY_NORMALIZED];
            $aStarts = str_starts_with($a, $starts);
            if($aStarts !== str_starts_with($b, $starts))
                return $aStarts ? -1 : 1;
            return $a <=> $b;
        });
    }

    /**
     * @param array<int|string, array<int|string, mixed>> $data
     * @param array<int|string,string> $initials
     * @param string|int $campo
     * @param-out array<int|string, InitialsMatchedRow> $data
     * @throws Exception
     */
    protected static function matchPriority(array &$data, array $initials, string|int $campo):void {
        /** @var array<int|string, array<int|string, mixed>> $data */
        foreach($data as &$d) {
            if(!array_key_exists(self::KEY_NORMALIZED, $d))
                /** @phpstan-ignore-next-line */
                $d[self::KEY_NORMALIZED] = strtoupper(self::unAccent($d[$campo] ?? ""));
            $d[self::KEY_PRIORITY] = 0;
            /** @var InitialsMatchedRow $d */
            $words = preg_split("/\s+/",  $d[self::KEY_NORMALIZED], -1, PREG_SPLIT_NO_EMPTY);
            if($words === false)
                throw new Exception("preg_split: Error splitting words: " . preg_last_error_msg());
            foreach($initials as $initial) {
                foreach($words as $i => &$word) {
                    if($word !== "" && str_starts_with($word, $initial)) {
                        $word = "";
                        $d[self::KEY_PRIORITY] += (1000 - $i);
                        break;
                    }
                }
                unset($word);
            }
        }
    }

    /**
     * Removes diacritics leaving the plain letter
     *
     * @param string $text The input text which needs to have diacritics removed.
     * @return string The processed text with diacritics removed.
     */
    protected static function unAccent(string $text):string {
        $transliterationMap = [
            // Common Latin characters
          'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ă' => 'A', 'Ą' => 'A',
          'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'ā' => 'a', 'ă' => 'a', 'ą' => 'a',
          'Ç' => 'C', 'Ć' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Č' => 'C', 'ç' => 'c', 'ć' => 'c', 'ĉ' => 'c', 'ċ' => 'c', 'č' => 'c',
          'Ð' => 'D', 'Ď' => 'D', 'Đ' => 'D', 'ð' => 'd', 'ď' => 'd', 'đ' => 'd',
          'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ę' => 'E', 'Ě' => 'E',
          'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ĕ' => 'e', 'ė' => 'e', 'ę' => 'e', 'ě' => 'e',
          'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g',
          'Ĥ' => 'H', 'Ħ' => 'H', 'ĥ' => 'h', 'ħ' => 'h',
          'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ĩ' => 'I', 'Ī' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I',
          'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ĩ' => 'i', 'ī' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i',
          'Ĵ' => 'J', 'ĵ' => 'j',
          'Ķ' => 'K', 'ķ' => 'k',
          'Ĺ' => 'L', 'Ļ' => 'L', 'Ľ' => 'L', 'Ŀ' => 'L', 'Ł' => 'L', 'ĺ' => 'l', 'ļ' => 'l', 'ľ' => 'l', 'ŀ' => 'l', 'ł' => 'l',
          'Ñ' => 'N', 'Ń' => 'N', 'Ņ' => 'N', 'Ň' => 'N', 'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n',
          'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ō' => 'O', 'Ŏ' => 'O', 'Ő' => 'O',
          'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o', 'ŏ' => 'o', 'ő' => 'o',
          'Ŕ' => 'R', 'Ŗ' => 'R', 'Ř' => 'R', 'ŕ' => 'r', 'ŗ' => 'r', 'ř' => 'r',
          'Ş' => 'S', 'Ș' => 'S', 'Ś' => 'S', 'Š' => 'S', 'ş' => 's', 'ș' => 's', 'ś' => 's', 'š' => 's',
          'Þ' => 'TH', 'þ' => 'th', // Icelandic
          'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ũ' => 'U', 'Ū' => 'U', 'Ŭ' => 'U', 'Ů' => 'U', 'Ű' => 'U', 'Ų' => 'U',
          'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ũ' => 'u', 'ū' => 'u', 'ŭ' => 'u', 'ů' => 'u', 'ű' => 'u', 'ų' => 'u',
          'Ý' => 'Y', 'Ÿ' => 'Y', 'Ŷ' => 'Y', 'ý' => 'y', 'ÿ' => 'y', 'ŷ' => 'y',
          'Ź' => 'Z', 'Ż' => 'Z', 'Ž' => 'Z', 'ź' => 'z', 'ż' => 'z', 'ž' => 'z',

          'ß' => 'ss', // German sharp S
          'Œ' => 'OE', 'œ' => 'oe', // French ligature
          'Æ' => 'AE', 'æ' => 'ae', // Danish/Norwegian ligature


            // Additional special cases for B and b
          'ƀ' => 'b', 'Ɓ' => 'B', 'Ƃ' => 'B', 'ƃ' => 'b',
          'ʙ' => 'B', // IPA symbol
        ];

        return strtr($text, $transliterationMap);
    }

}
