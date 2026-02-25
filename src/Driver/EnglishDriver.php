<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Driver;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Contract\StemmerModeInterface;
use Dompat\Stemmer\Enum\StemmerMode;
use Dompat\Stemmer\Exception\UnsupportedModeException;
use Dompat\Stemmer\Utility\SnowballUtils;

/**
 * English Stemmer based on the Porter2 (Snowball) algorithm.
 * Handles exceptions (skis -> ski), Y-consonant logic, and precise suffix removal.
 */
readonly class EnglishDriver implements DriverInterface
{
    private const array SUPPORTED_MODES = [
        StemmerMode::LIGHT,
        StemmerMode::AGGRESSIVE,
    ];

    /**
     * Invariant words (exceptions) that should not be stemmed or have special forms.
     */
    private const array INVARIANTS = [
        'skis' => 'ski', 'skies' => 'sky',
        'dying' => 'die', 'lying' => 'lie', 'tying' => 'tie',
        'id' => 'id', 'gently' => 'gentl', 'ugly' => 'ugli', 'early' => 'earli',
        'only' => 'onli', 'singly' => 'singl',
        'sky' => 'sky', 'news' => 'news', 'howe' => 'howe',
        'atlas' => 'atlas', 'cosmos' => 'cosmos', 'bias' => 'bias', 'andes' => 'andes'
    ];

    /**
     * Step 2 suffixes.
     * Note: Ordering matters for overlapping suffixes (longest match first strategy).
     */
    private const array STEP2 = [
        'ational' => 'ate', 'tional' => 'tion', 'ization' => 'ize', 'ation' => 'ate',
        'ator' => 'ate', 'alism' => 'al', 'iveness' => 'ive', 'fulness' => 'ful',
        'ousness' => 'ous', 'aliti' => 'al', 'iviti' => 'ive', 'biliti' => 'ble',
        'enci' => 'ence', 'anci' => 'ance', 'izer' => 'ize', 'bli' => 'ble',
        'alli' => 'al', 'entli' => 'ent', 'eli' => 'e', 'ousli' => 'ous',
        'logi' => 'log',
    ];

    /** Step 3 suffixes */
    private const array STEP3 = [
        'icate' => 'ic', 'ative' => '', 'alize' => 'al', 'iciti' => 'ic',
        'ical' => 'ic', 'ful' => '', 'ness' => ''
    ];

    /** Step 4 suffixes */
    private const array STEP4 = [
        'al', 'ance', 'ence', 'er', 'ic', 'able', 'ible', 'ant', 'ement',
        'ment', 'ent', 'ism', 'ate', 'iti', 'ous', 'ive', 'ize'
    ];

    public function __construct(
        private string $locale = 'en'
    ) {
    }

    #[\Override]
    public function getLocale(): string
    {
        return $this->locale;
    }

    #[\Override]
    public function stem(string $word, StemmerModeInterface $mode): string
    {
        if (!in_array($mode, self::SUPPORTED_MODES, true)) {
            throw new UnsupportedModeException($this, $mode, self::SUPPORTED_MODES);
        }

        if (strlen($word) <= 2) {
            return strtolower($word);
        }

        $word = strtolower($word);

        // Remove initial apostrophe
        if (str_starts_with($word, "'")) {
            $word = substr($word, 1);
        }

        // Check invariants (exceptions)
        if (isset(self::INVARIANTS[$word])) {
            return self::INVARIANTS[$word];
        }

        // Calculate Regions R1 and R2
        [$r1, $r2] = $this->calculateRegions($word);

        // Mark 'y' as consonant 'Y' if preceded by a vowel
        $word = $this->markYs($word);

        // --- Step 0: Remove 's', 's'', ''' ---
        $word = $this->step0($word);

        // --- Step 1a: Plurals (ss, ies, s) ---
        $word = $this->step1a($word);

        // Check invariants again after Step 1a (per Snowball spec, e.g. for "inning")
        // But usually invariants are checked once. We'll stick to standard flow.

        // --- Step 1b: ed, ing, eed ---
        $word = $this->step1b($word, $r1);

        // --- Step 1c: y -> i ---
        $word = $this->step1c($word);

        // LIGHT MODE END
        if ($mode === StemmerMode::LIGHT) {
            return strtolower($word); // Convert Y back to y
        }

        // --- Step 2: Derivational ---
        $word = $this->step2($word, $r1);

        // --- Step 3: Derivational ---
        $word = $this->step3($word, $r1, $r2);

        // --- Step 4: Deletion ---
        $word = $this->step4($word, $r2);

        // --- Step 5: Final e / l ---
        $word = $this->step5($word, $r1, $r2);

        return strtolower($word);
    }

    private function markYs(string $word): string
    {
        // Capitalize Y if it serves as a consonant
        // 1. At start of word: y -> Y
        if ($word[0] === 'y') {
            $word[0] = 'Y';
        }

        // 2. Following a vowel: y -> Y
        $len = strlen($word);
        for ($i = 1; $i < $len; $i++) {
            if ($word[$i] === 'y' && $this->isVowel($word[$i - 1])) {
                $word[$i] = 'Y';
            }
        }
        return $word;
    }

    /**
     * @return int[] [R1 index, R2 index]
     */
    private function calculateRegions(string $word): array
    {
        // R1 is the region after the first non-vowel following a vowel
        $r1 = SnowballUtils::findR1($word, 'aeiouy');

        // Adjust R1 for specific prefixes ONLY if the standard R1 would be further
        if (str_starts_with($word, 'gener') || str_starts_with($word, 'arsen')) {
            $r1 = min($r1, 5);
        } elseif (str_starts_with($word, 'commun')) {
            $r1 = min($r1, 6);
        }

        // R2 is the region after the first non-vowel following a vowel in R1
        $r2 = SnowballUtils::findR2($word, 'aeiouy', $r1);

        return [$r1, $r2];
    }

    private function step0(string $word): string
    {
        if (str_ends_with($word, "'s'")) return substr($word, 0, -3);
        if (str_ends_with($word, "'s")) return substr($word, 0, -2);
        if (str_ends_with($word, "'")) return substr($word, 0, -1);
        return $word;
    }

    private function step1a(string $word): string
    {
        if (str_ends_with($word, 'sses')) return substr($word, 0, -2);
        if (str_ends_with($word, 'ied') || str_ends_with($word, 'ies')) {
            $stem = substr($word, 0, -3);
            return (strlen($stem) > 1) ? $stem . 'i' : $stem . 'ie';
        }
        if (str_ends_with($word, 'ss') || str_ends_with($word, 'us')) return $word;

        if (str_ends_with($word, 's')) {
            $stem = substr($word, 0, -1);
            // Search for a vowel in the stem before the last character
            $stemLen = strlen($stem);
            for ($i = 0; $i < $stemLen - 1; $i++) {
                if ($this->isVowel($stem[$i])) {
                    return $stem;
                }
            }
        }
        return $word;
    }

    private function step1b(string $word, int $r1): string
    {
        if (str_ends_with($word, 'eedly')) {
            $stem = substr($word, 0, -5);
            return (strlen($stem) >= $r1) ? $stem . 'ee' : $word;
        }
        if (str_ends_with($word, 'eed')) {
            $stem = substr($word, 0, -3);
            return (strlen($stem) >= $r1) ? $stem . 'ee' : $word;
        }

        $suffix = '';
        if (str_ends_with($word, 'edly')) $suffix = 'edly';
        elseif (str_ends_with($word, 'ed')) $suffix = 'ed';
        elseif (str_ends_with($word, 'ingly')) $suffix = 'ingly';
        elseif (str_ends_with($word, 'ing')) $suffix = 'ing';

        if ($suffix !== '') {
            $stem = substr($word, 0, -strlen($suffix));
            if ($this->containsVowel($stem)) {
                $word = $stem;
                if (str_ends_with($word, 'at') || str_ends_with($word, 'bl') || str_ends_with($word, 'iz')) {
                    return $word . 'e';
                }
                if ($this->isDoubleConsonant($word)) {
                    return substr($word, 0, -1);
                }
                if ($this->isShortWord($word, $r1)) {
                    return $word . 'e';
                }
                return $word;
            }
        }
        return $word;
    }

    private function step1c(string $word): string
    {
        // replace suffix y or Y by i if preceded by a non-vowel which is not the first letter of the word
        $len = strlen($word);
        if ($len > 1 && ($word[$len - 1] === 'y' || $word[$len - 1] === 'Y')) {
            if (!$this->isVowel($word[$len - 2])) {
                $word[$len - 1] = 'i';
            }
        }
        return $word;
    }

    private function step2(string $word, int $r1): string
    {
        foreach (self::STEP2 as $suffix => $replacement) {
            if (str_ends_with($word, $suffix)) {
                $stem = substr($word, 0, -strlen($suffix));
                if (strlen($stem) >= $r1) {
                    return $stem . $replacement;
                }
                return $word;
            }
        }
        return $word;
    }

    private function step3(string $word, int $r1, int $r2): string
    {
        foreach (self::STEP3 as $suffix => $replacement) {
            if (str_ends_with($word, $suffix)) {
                $stem = substr($word, 0, -strlen($suffix));
                if (strlen($stem) >= $r1) {
                    return $stem . $replacement;
                }
                return $word;
            }
        }
        // Special case: "ative" -> "" if in R2
        if (str_ends_with($word, 'ative')) {
            $stem = substr($word, 0, -5);
            if (strlen($stem) >= $r2) return $stem;
        }
        return $word;
    }

    private function step4(string $word, int $r2): string
    {
        foreach (self::STEP4 as $suffix) {
            if (str_ends_with($word, $suffix)) {
                $stem = substr($word, 0, -strlen($suffix));
                if (strlen($stem) >= $r2) return $stem;
            }
        }
        if (str_ends_with($word, 'ion')) {
            $stem = substr($word, 0, -3);
            if (strlen($stem) >= $r2 && (str_ends_with($stem, 's') || str_ends_with($stem, 't'))) {
                return $stem;
            }
        }
        return $word;
    }

    private function step5(string $word, int $r1, int $r2): string
    {
        // 5a: remove e
        if (str_ends_with($word, 'e')) {
            $stem = substr($word, 0, -1);
            if (strlen($stem) >= $r2 || (strlen($stem) >= $r1 && !$this->isShortSyllable($stem))) {
                $word = $stem;
            }
        }
        // 5b: reduce ll
        if (str_ends_with($word, 'll') && strlen($word) - 1 >= $r2) {
            $word = substr($word, 0, -1);
        }
        return $word;
    }

    // --- Helpers ---

    private function isVowel(string $char): bool
    {
        return str_contains('aeiouy', $char);
    }

    private function containsVowel(string $word): bool
    {
        foreach (str_split($word) as $char) {
            if ($this->isVowel($char)) return true;
        }
        return false;
    }

    private function isDoubleConsonant(string $word): bool
    {
        $len = strlen($word);
        if ($len < 2) return false;
        $c1 = $word[$len - 1];
        $c2 = $word[$len - 2];
        return $c1 === $c2 && !$this->isVowel($c1) && !in_array($c1, ['l', 's', 'z'], true);
    }

    private function isShortSyllable(string $word): bool
    {
        // ends with non-vowel, vowel, non-vowel (except w, x, Y)
        // OR starts with vowel, non-vowel.
        $len = strlen($word);
        if ($len < 2) return false;

        $last = $word[$len - 1];
        if ($this->isVowel($last)) return false;

        $penultimate = $word[$len - 2];
        if (!$this->isVowel($penultimate)) return false; // must be vowel

        if ($len === 2) return true; // Vowel-Consonant at start

        // C-V-C
        $ante = $word[$len - 3];
        if ($this->isVowel($ante)) return false; // must be Consonant

        return !in_array($last, ['w', 'x', 'Y'], true);
    }

    private function isShortWord(string $word, int $r1): bool
    {
        return strlen($word) < $r1 && $this->isShortSyllable($word);
    }

    #[\Override]
    public function __toString(): string
    {
        return 'EnglishDriver';
    }
}
