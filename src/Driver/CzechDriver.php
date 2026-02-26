<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Driver;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Contract\StemmerModeInterface;
use Dompat\Stemmer\Enum\StemmerMode;
use Dompat\Stemmer\Exception\UnsupportedModeException;

/**
 * Czech Stemmer based on the x3wil implementation (Dolamic/Savoy variant).
 * Handles case suffixes (inflection), possessives, diminutives, and palatalization.
 */
readonly class CzechDriver implements DriverInterface
{
    private const array SUPPORTED_MODES = [
        StemmerMode::LIGHT,
        StemmerMode::AGGRESSIVE,
    ];

    /**
     * Rules for Case Removal
     * Structure: Length => [Suffix => [CutLength, PalataliseBool]]
     */
    private const array CASE_RULES = [
        5 => [
            'atech' => [5, false], 'ětemi' => [3, true], 'átor' => [4, false], 'ator' => [4, false],
        ],
        4 => [
            'ětem' => [3, true], 'atům' => [4, false], 'atým' => [4, false],
        ],
        3 => [
            'ech' => [2, true], 'ich' => [2, true],
            'ého' => [2, true], 'ěmi' => [2, true], 'emi' => [2, true], 'ému' => [2, true],
            'ěte' => [2, true], 'ěti' => [2, true], 'iho' => [2, true], 'ího' => [2, true],
            'ími' => [2, true], 'ímu' => [2, true], 'imu' => [2, true], 'ích' => [2, true],
            'ách' => [3, false], 'ata' => [3, false], 'aty' => [3, false], 'ých' => [3, false],
            'ama' => [3, false], 'ami' => [3, false], 'ové' => [3, false], 'ovi' => [3, false],
            'ými' => [3, false], 'áme' => [3, false], 'áte' => [3, false], 'ají' => [3, false],
            'ali' => [3, false], 'ala' => [3, false],
            'at' => [2, false], 'et' => [2, false], 'it' => [2, false],
        ],
        2 => [
            'em' => [1, true],
            'es' => [2, true], 'ém' => [2, true], 'ím' => [2, true],
            'ům' => [2, false], 'at' => [2, false], 'ám' => [2, false], 'om' => [2, false],
            'os' => [2, false], 'us' => [2, false], 'ým' => [2, false], 'mi' => [2, false],
            'ou' => [2, false], 'áš' => [2, false], 'as' => [2, false], 'is' => [2, false],
            'ál' => [2, false], 'ěl' => [2, false], 'il' => [2, false], 'al' => [2, false],
            'el' => [2, false], 'dl' => [2, false],
        ],
        1 => [
            'e' => [0, true], 'i' => [0, true], 'í' => [0, true], 'é' => [0, true], 'ě' => [0, true],
            'u' => [1, false], 'y' => [1, false], 'ů' => [1, false],
            'a' => [1, false], 'o' => [1, false], 'á' => [1, false], 'ý' => [1, false],
        ],
    ];

    /**
     * Rules for Possessives
     */
    private const array POSSESSIVE_RULES = [
        2 => [
            'ov' => [2, false], 'ův' => [2, false],
            'in' => [1, true],
        ]
    ];

    /**
     * Rules for Comparatives
     */
    private const array COMPARATIVE_RULES = [
        3 => [
            'ejš' => [3, false], 'ějš' => [3, true],
        ]
    ];

    /**
     * Rules for Diminutives
     */
    private const array DIMINUTIVE_RULES = [
        5 => [
            'oušek' => [5, false],
        ],
        4 => [
            'eček' => [3, true], 'éček' => [3, true], 'iček' => [3, true], 'íček' => [3, true],
            'enek' => [3, true], 'ének' => [3, true], 'inek' => [3, true], 'ínek' => [3, true],
            'áček' => [4, false], 'aček' => [4, false], 'oček' => [4, false], 'uček' => [4, false],
            'anek' => [4, false], 'onek' => [4, false], 'unek' => [4, false], 'ánek' => [4, false],
        ],
        3 => [
            'ečk' => [3, true], 'éčk' => [3, true], 'ičk' => [3, true], 'íčk' => [3, true],
            'enk' => [3, true], 'énk' => [3, true], 'ink' => [3, true], 'ínk' => [3, true],
            'áčk' => [3, false], 'ačk' => [3, false], 'očk' => [3, false], 'učk' => [3, false],
            'ank' => [3, false], 'onk' => [3, false], 'unk' => [3, false],
            'átk' => [3, false], 'ánk' => [3, false], 'ušk' => [3, false],
        ],
        2 => [
            'ek' => [1, true], 'ék' => [1, true], 'ík' => [1, true], 'ik' => [1, true],
            'ák' => [1, false], 'ak' => [1, false], 'ok' => [1, false], 'uk' => [1, false],
        ],
        1 => [
            'k' => [1, false],
        ]
    ];

    /**
     * Rules for Augmentatives
     */
    private const array AUGMENTATIVE_RULES = [
        4 => ['ajzn' => [4, false]],
        3 => ['izn' => [2, true], 'isk' => [2, true]],
        2 => ['ák' => [2, false]],
    ];

    /**
     * Rules for Derivational suffixes
     */
    private const array DERIVATIONAL_RULES = [
        6 => ['obinec' => [6, false]],
        5 => [
            'ionář' => [4, true], 'ovisk' => [5, false], 'ovstv' => [5, false], 'ovišt' => [5, false],
            'ovník' => [5, false], 'átor' => [4, false], 'ator' => [4, false], 'tor' => [3, false],
        ],
        4 => [
            'ásek' => [4, false], 'loun' => [4, false], 'nost' => [4, false], 'teln' => [4, false],
            'ovec' => [4, false], 'ovtv' => [4, false], 'ovin' => [4, false], 'štin' => [4, false],
            'ovík' => [4, false], 'enic' => [3, true], 'inec' => [3, true], 'itel' => [3, true],
        ],
        3 => [
            'árn' => [3, false],
            'ěnk' => [2, true],
            'ián' => [2, true], 'ist' => [2, true], 'isk' => [2, true], 'išt' => [2, true], 'itb' => [2, true], 'írn' => [2, true],
            'och' => [3, false], 'ost' => [3, false], 'ovn' => [3, false], 'oun' => [3, false], 'out' => [3, false], 'ouš' => [3, false],
            'ušk' => [3, false],
            'kyn' => [3, false], 'čan' => [3, false], 'kář' => [3, false], 'néř' => [3, false], 'ník' => [3, false], 'ctv' => [3, false], 'stv' => [3, false],
        ],
        2 => [
            'ec' => [1, true], 'en' => [1, true], 'ěn' => [1, true], 'éř' => [1, true],
            'íř' => [1, true], 'ic' => [1, true], 'in' => [1, true], 'ín' => [1, true], 'it' => [1, true], 'iv' => [1, true],
            'ob' => [2, false], 'ot' => [2, false], 'ov' => [2, false], 'oň' => [2, false],
            'ul' => [2, false], 'yn' => [2, false],
            'čk' => [2, false], 'čn' => [2, false], 'dl' => [2, false], 'nk' => [2, false], 'tv' => [2, false], 'tk' => [2, false], 'vk' => [2, false],
        ],
    ];

    public function __construct(
        private string $locale = 'cs'
    ) {
    }

    #[\Override]
    public function getLocale(): string
    {
        return $this->locale;
    }

    #[\Override]
    public function stem(string $word, StemmerModeInterface $mode = StemmerMode::LIGHT): string
    {
        if (!in_array($mode, self::SUPPORTED_MODES, true)) {
            throw new UnsupportedModeException($this, $mode, self::SUPPORTED_MODES);
        }

        $word = mb_strtolower($word);

        // Handle prefixes
        $word = $this->processPrefixes($word);

        // Light steps
        $word = $this->processRules($word, self::CASE_RULES);
        $word = $this->processRules($word, self::POSSESSIVE_RULES);

        if ($mode === StemmerMode::LIGHT) {
            return $word;
        }

        // Aggressive steps
        $word = $this->processRules($word, self::COMPARATIVE_RULES);
        $word = $this->processRules($word, self::DIMINUTIVE_RULES);
        $word = $this->processRules($word, self::AUGMENTATIVE_RULES);
        $word = $this->processRules($word, self::DERIVATIONAL_RULES);

        return $word;
    }

    private function processPrefixes(string $word): string
    {
        if (str_starts_with($word, 'nej') && mb_strlen($word) > 5) {
            $word = mb_substr($word, 3);
        }

        return $word;
    }

    /**
     * @param array<int, array<string, array{0: int, 1: bool}>> $ruleSet
     */
    private function processRules(string $word, array $ruleSet): string
    {
        $len = mb_strlen($word);

        foreach ($ruleSet as $suffixes) {
            foreach ($suffixes as $suffix => $action) {
                if (str_ends_with($word, $suffix)) {
                    // Action: [0 => cut_length, 1 => palatalise_bool]
                    [$cut, $palatalise] = $action;

                    if ($len - $cut < 3 || in_array($word, ['internet', 'magnet'], true)) {
                        continue;
                    }

                    // If cut is 0, we only pass to palatalise (which acts on the whole word)
                    if ($cut > 0) {
                        $word = mb_substr($word, 0, -$cut);
                    }

                    if ($palatalise) {
                        return $this->palatalise($word);
                    }

                    return $word;
                }
            }
        }

        return $word;
    }

    private function palatalise(string $word): string
    {
        if ($this->endsWithAny($word, ['ci', 'ce', 'či', 'če'])) {
            return mb_substr($word, 0, -2) . 'k';
        }
        if ($this->endsWithAny($word, ['zi', 'ze'])) {
            return mb_substr($word, 0, -2) . 'h';
        }
        if ($this->endsWithAny($word, ['ži', 'že'])) {
            return mb_substr($word, 0, -2) . 'h';
        }
        if ($this->endsWithAny($word, ['si', 'se'])) {
            return mb_substr($word, 0, -2) . 'ch';
        }
        if ($this->endsWithAny($word, ['ši', 'še'])) {
            return mb_substr($word, 0, -2) . 'ch';
        }
        if ($this->endsWithAny($word, ['čtě', 'čté', 'čti', 'čtí'])) {
            return mb_substr($word, 0, -3) . 'ck';
        }
        if ($this->endsWithAny($word, ['ště', 'šté', 'šti', 'ští'])) {
            return mb_substr($word, 0, -3) . 'sk';
        }
        if ($this->endsWithAny($word, ['ři', 'ře'])) {
            return mb_substr($word, 0, -2) . 'r';
        }
        if ($this->endsWithAny($word, ['ni', 'ne', 'ně', 'ní'])) {
            return mb_substr($word, 0, -2) . 'n';
        }
        if ($this->endsWithAny($word, ['ti', 'te', 'tě', 'tí'])) {
            return mb_substr($word, 0, -2) . 't';
        }
        if ($this->endsWithAny($word, ['di', 'de', 'dě', 'dí'])) {
            return mb_substr($word, 0, -2) . 'd';
        }

        return mb_substr($word, 0, -1);
    }

    /**
     * @param string[] $suffixes
     */
    private function endsWithAny(string $word, array $suffixes): bool
    {
        foreach ($suffixes as $suffix) {
            if (str_ends_with($word, $suffix)) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function __toString(): string
    {
        return 'CzechDriver';
    }
}
