<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests\Driver;

use Dompat\Stemmer\Driver\CzechDriver;
use Dompat\Stemmer\Enum\StemmerMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CzechDriverTest extends TestCase
{
    private CzechDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new CzechDriver('cs');
    }

    #[DataProvider('provideStemmingData')]
    public function testStem(string $word, StemmerMode $mode, string $expected): void
    {
        $this->assertSame($expected, $this->driver->stem($word, $mode));
    }

    /**
     * @return array<int, array{0: string, 1: StemmerMode, 2: string}>
     */
    public static function provideStemmingData(): array
    {
        return [
            // --- PREFIXES ---
            ['nejlepší', StemmerMode::LIGHT, 'lepš'],
            ['nejkrásnější', StemmerMode::LIGHT, 'krásnějš'],
            ['nejkrásnější', StemmerMode::AGGRESSIVE, 'krás'],

            // --- CASE RULES (Light) ---
            // 5 chars
            ['programátor', StemmerMode::LIGHT, 'program'], // átor -> 4, false

            // 4 chars
            ['poupětem', StemmerMode::LIGHT, 'poup'], // ětem -> 3, true. 'poupětem' - 3 = 'poupě'. Palatalise 'poupě' -> 'poup'
            ['kuřatům', StemmerMode::LIGHT, 'kuř'], // atům -> 4, false

            // 3 chars
            ['střech', StemmerMode::LIGHT, 'str'], // ech -> 2, true. 'střech' - 2 = 'stře'. Palatalise 'stře' -> 'str'
            ['jarních', StemmerMode::LIGHT, 'jarn'], // ích -> 2, true. 'jarních' - 2 = 'jarní'. Palatalise 'jarní' -> 'jarn'
            ['zeleného', StemmerMode::LIGHT, 'zelen'], // ého -> 2, true. 'zeleného' - 2 = 'zeleně'. Palatalise 'zeleně' -> 'zelen'
            ['kočkami', StemmerMode::LIGHT, 'kočk'], // ami -> 3, false
            ['ženami', StemmerMode::LIGHT, 'žen'], // ami -> 3, false
            ['hrady', StemmerMode::LIGHT, 'hrad'], // y -> 1, false

            // 2 chars
            ['domem', StemmerMode::LIGHT, 'dom'], // em -> 1, true. 'domem' - 1 = 'dome'. Palatalise 'dome' -> 'dom'
            ['stolem', StemmerMode::LIGHT, 'stol'], // em -> 1, true. 'stolem' - 1 = 'stole'. Palatalise 'stole' -> 'stol'
            ['hradům', StemmerMode::LIGHT, 'hrad'], // ům -> 2, false
            ['děláš', StemmerMode::LIGHT, 'děl'], // áš -> 2, false
            ['snědl', StemmerMode::LIGHT, 'sně'], // dl -> 2, false

            // 1 char
            ['hradě', StemmerMode::LIGHT, 'hrad'], // ě -> 0, true. 'hradě' - 0 = 'hradě'. Palatalise 'hradě' -> 'hrad'
            ['město', StemmerMode::LIGHT, 'měst'], // o -> 1, false
            ['moře', StemmerMode::LIGHT, 'mor'], // e -> 0, true. 'moře' - 0 = 'moře'. Palatalise 'moře' -> 'mor'

            // --- POSSESSIVE RULES (Light) ---
            ['otcův', StemmerMode::LIGHT, 'otc'], // ův -> 2, false
            ['matčin', StemmerMode::LIGHT, 'matk'], // in -> 1, true. 'matčin' - 1 = 'matči'. Palatalise 'matči' -> 'matk'

            // --- COMPARATIVE RULES (Aggressive) ---
            ['rychlejší', StemmerMode::AGGRESSIVE, 'rychl'], // ejš -> 3, false
            ['krásnější', StemmerMode::AGGRESSIVE, 'krás'], // ějš -> 3, true. 'krásnější' -> 'krásně'. Palatalise 'krásně' -> 'krásn'.

            // --- DIMINUTIVE RULES (Aggressive) ---
            ['domeček', StemmerMode::AGGRESSIVE, 'dom'], // eček -> 3, true
            ['stoleček', StemmerMode::AGGRESSIVE, 'stol'], // eček -> 3, true
            ['kočička', StemmerMode::AGGRESSIVE, 'ko'], // Case 'a' -> 'kočičk'. Dim 'ičk' -> 3, true. 'ko'.
            ['pejsek', StemmerMode::AGGRESSIVE, 'pejch'], // Case 'ek' is handled? No, DIMINUTIVE_RULES 2 chars 'ek' -> 1, true.
            ['parník', StemmerMode::AGGRESSIVE, 'parn'], // ik -> 1, true. 'parník' -> 'parní'. Palatalise 'parní' -> 'parn'
            ['panák', StemmerMode::AGGRESSIVE, 'paná'], // Case rule 'á' is not applied because word would be too short?

            // --- AUGMENTATIVE RULES (Aggressive) ---
            ['chlapák', StemmerMode::AGGRESSIVE, 'chlapá'], // ák -> 2, false
            ['babizna', StemmerMode::AGGRESSIVE, 'bab'], // Case 'a' -> 'babizn'. Aug 'izn' -> 2, true. 'bab'.

            // --- DERIVATIONAL RULES (Aggressive) ---
            ['učitel', StemmerMode::AGGRESSIVE, 'uk'], // itel -> 3, true
            ['slovník', StemmerMode::AGGRESSIVE, 'slovn'], // ovník -> 5, false
            ['blbost', StemmerMode::AGGRESSIVE, 'blb'], // ost -> 3, false
            ['rychlost', StemmerMode::AGGRESSIVE, 'rychl'], // ost -> 3, false

            // --- PALATALISATION CASES ---
            // ci, ce, či, če -> k
            ['vlci', StemmerMode::LIGHT, 'vlk'],
            ['matce', StemmerMode::LIGHT, 'matk'],
            // zi, ze, ži, že -> h
            ['praze', StemmerMode::LIGHT, 'prah'],
            ['knize', StemmerMode::LIGHT, 'knih'],
            ['bože', StemmerMode::LIGHT, 'boh'],
            // si, se, ši, še -> ch
            ['mouše', StemmerMode::LIGHT, 'mouch'],
            ['střeše', StemmerMode::LIGHT, 'střech'],
            // čtě, čté, čti, čtí -> ck
            ['američtí', StemmerMode::LIGHT, 'americk'],
            // ště, šté, šti, ští -> sk
            ['čínští', StemmerMode::LIGHT, 'čínsk'],
            // ři, ře -> r
            ['moři', StemmerMode::LIGHT, 'mor'],
            ['moře', StemmerMode::LIGHT, 'mor'],
            ['tváře', StemmerMode::LIGHT, 'tvár'],
            ['tváři', StemmerMode::LIGHT, 'tvár'],
            // ni, ne, ně, ní -> n
            ['písni', StemmerMode::LIGHT, 'písn'],
            ['písně', StemmerMode::LIGHT, 'písn'],
            // ti, te, tě, tí -> t
            ['kostě', StemmerMode::LIGHT, 'kost'],
            ['kostí', StemmerMode::LIGHT, 'kost'],
            // di, de, dě, dí -> d
            ['lodí', StemmerMode::LIGHT, 'lod'],
            ['lodě', StemmerMode::LIGHT, 'lod'],

            // --- SPECIAL WORDS / EXCLUSIONS ---
            ['internet', StemmerMode::LIGHT, 'internet'],
            ['internetem', StemmerMode::LIGHT, 'internet'],
            ['magnet', StemmerMode::LIGHT, 'magnet'],

            // --- VERBS ---
            ['dělat', StemmerMode::LIGHT, 'děl'],
            ['dělám', StemmerMode::LIGHT, 'děl'],
            ['děláš', StemmerMode::LIGHT, 'děl'],
            ['děláme', StemmerMode::LIGHT, 'děl'],
            ['děláte', StemmerMode::LIGHT, 'děl'],
            ['dělají', StemmerMode::LIGHT, 'děl'],
            ['dělal', StemmerMode::LIGHT, 'děl'],
            ['udělat', StemmerMode::LIGHT, 'uděl'],

            // --- MISC ---
            ['hrad', StemmerMode::LIGHT, 'hrad'],
            ['hrady', StemmerMode::LIGHT, 'hrad'],
            ['hradů', StemmerMode::LIGHT, 'hrad'],
            ['hradům', StemmerMode::LIGHT, 'hrad'],
            ['ženami', StemmerMode::LIGHT, 'žen'],
        ];
    }
}
