<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Utility;

final class SnowballUtils
{
    /**
     * Calculates the start index of Region 1 (R1).
     *
     * According to Snowball definition:
     * R1 is the region after the first non-vowel following a vowel.
     * Or simpler: Find the first Vowel-Consonant pair, R1 starts immediately after it.
     *
     * @param string $word The word to analyze.
     * @param string $vowels A string containing all valid vowel characters for the language.
     * @return int The starting index of R1. If R1 does not exist, returns word length.
     */
    public static function findR1(string $word, string $vowels): int
    {
        $len = mb_strlen($word);
        $i = 0;

        // 1. Scan past initial consonants (find the first vowel)
        while ($i < $len && !str_contains($vowels, mb_substr($word, $i, 1))) {
            $i++;
        }

        // 2. Scan past the vowel cluster (find the first non-vowel/consonant)
        while ($i < $len && str_contains($vowels, mb_substr($word, $i, 1))) {
            $i++;
        }

        return $i;
    }

    /**
     * Calculates the start index of Region 2 (R2).
     *
     * R2 is the region calculated the same way as R1, but starting from the R1 region.
     *
     * @param string $word The word to analyze.
     * @param string $vowels A string containing all valid vowel characters.
     * @param int $r1Index The starting index of R1 (calculated previously).
     * @return int The starting index of R2.
     */
    public static function findR2(string $word, string $vowels, int $r1Index): int
    {
        $len = mb_strlen($word);
        $i = $r1Index;

        // 1. Scan past consonants starting from R1
        while ($i < $len && !str_contains($vowels, mb_substr($word, $i, 1))) {
            $i++;
        }

        // 2. Scan past vowels
        while ($i < $len && str_contains($vowels, mb_substr($word, $i, 1))) {
            $i++;
        }

        return $i;
    }
}
