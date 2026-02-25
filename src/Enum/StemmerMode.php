<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Enum;

use Dompat\Stemmer\Contract\StemmerModeInterface;

enum StemmerMode: string implements StemmerModeInterface
{
    /**
     * Light mode. Removes only basic inflectional suffixes (e.g., plurals, verb tenses).
     * Ideal for autocomplete and text highlighting, as the word usually remains readable.
     *
     * Examples:
     *  - "Apples"  -> "Apple"
     *  - "Calling" -> "Call"
     */
    case LIGHT = 'light';

    /**
     * Aggressive mode. Strips words down to their morphological root (stem).
     * Ideal for full-text search and indexing. Note: The result may not be a valid word.
     *
     * Examples:
     *  - "Happy", "Happily", "Happiness" -> "Happi"
     *  - "Computation", "Computer"       -> "Comput"
     */
    case AGGRESSIVE = 'aggressive';
}
