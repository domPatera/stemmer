<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests\Driver;

use Dompat\Stemmer\Driver\EnglishDriver;
use Dompat\Stemmer\Enum\StemmerMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EnglishDriverTest extends TestCase
{
    private EnglishDriver $driver;

    protected function setUp(): void
    {
        $this->driver = new EnglishDriver();
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
            // --- Step 0: Apostrophes ---
            ["men's", StemmerMode::LIGHT, "men"],
            ["witnesses'", StemmerMode::LIGHT, "witness"],
            ["witness's", StemmerMode::LIGHT, "witness"],
            ["boy's", StemmerMode::LIGHT, "boy"],

            // --- Step 1a: Plurals ---
            ['ties', StemmerMode::LIGHT, 'tie'],
            ['cries', StemmerMode::LIGHT, 'cri'],
            ['cats', StemmerMode::LIGHT, 'cat'],
            ['caress', StemmerMode::LIGHT, 'caress'],
            ['caresses', StemmerMode::LIGHT, 'caress'],
            ['ponies', StemmerMode::LIGHT, 'poni'],
            ['ties', StemmerMode::LIGHT, 'tie'],
            ['caress', StemmerMode::LIGHT, 'caress'],
            ['cats', StemmerMode::LIGHT, 'cat'],
            ['gas', StemmerMode::LIGHT, 'gas'],

            // --- Step 1b: ed, ing, eed ---
            ['feed', StemmerMode::LIGHT, 'feed'],
            ['agreed', StemmerMode::LIGHT, 'agree'],
            ['plastered', StemmerMode::LIGHT, 'plaster'],
            ['bled', StemmerMode::LIGHT, 'bled'],
            ['motoring', StemmerMode::LIGHT, 'motor'],
            ['sing', StemmerMode::LIGHT, 'sing'],
            ['conflated', StemmerMode::LIGHT, 'conflate'],
            ['troubled', StemmerMode::LIGHT, 'trouble'],
            ['sized', StemmerMode::LIGHT, 'size'],
            ['hopping', StemmerMode::LIGHT, 'hop'],
            ['tanned', StemmerMode::LIGHT, 'tan'],
            ['falling', StemmerMode::LIGHT, 'fall'],
            ['hissing', StemmerMode::LIGHT, 'hiss'],
            ['fizzing', StemmerMode::LIGHT, 'fizz'],
            ['failing', StemmerMode::LIGHT, 'fail'],
            ['filing', StemmerMode::LIGHT, 'fil'],

            // --- Step 1c: y -> i ---
            ['happy', StemmerMode::LIGHT, 'happi'],
            ['sky', StemmerMode::LIGHT, 'sky'],
            ['boy', StemmerMode::LIGHT, 'boy'],
            ['syzygy', StemmerMode::LIGHT, 'syzygi'],

            // --- INVARIANTS / EXCEPTIONS ---
            ['skis', StemmerMode::LIGHT, 'ski'],
            ['skies', StemmerMode::LIGHT, 'sky'],
            ['dying', StemmerMode::LIGHT, 'die'],
            ['lying', StemmerMode::LIGHT, 'lie'],
            ['tying', StemmerMode::LIGHT, 'tie'],
            ['id', StemmerMode::LIGHT, 'id'],
            ['gently', StemmerMode::LIGHT, 'gentl'],
            ['ugly', StemmerMode::LIGHT, 'ugli'],
            ['early', StemmerMode::LIGHT, 'earli'],
            ['only', StemmerMode::LIGHT, 'onli'],
            ['singly', StemmerMode::LIGHT, 'singl'],
            ['news', StemmerMode::LIGHT, 'news'],
            ['atlas', StemmerMode::LIGHT, 'atlas'],
            ['cosmos', StemmerMode::LIGHT, 'cosmos'],
            ['bias', StemmerMode::LIGHT, 'bias'],
            ['andes', StemmerMode::LIGHT, 'andes'],

            // --- AGGRESSIVE MODE (Step 2, 3, 4, 5) ---

            // Step 2
            ['relational', StemmerMode::AGGRESSIVE, 'relat'],
            ['conditional', StemmerMode::AGGRESSIVE, 'condit'],
            ['rational', StemmerMode::AGGRESSIVE, 'ration'],
            ['valence', StemmerMode::AGGRESSIVE, 'valenc'],
            ['hesitancy', StemmerMode::AGGRESSIVE, 'hesit'],
            ['digitizer', StemmerMode::AGGRESSIVE, 'digit'],
            ['conformably', StemmerMode::AGGRESSIVE, 'conform'],
            ['radically', StemmerMode::AGGRESSIVE, 'radic'],
            ['differently', StemmerMode::AGGRESSIVE, 'differ'],
            ['vilely', StemmerMode::AGGRESSIVE, 'vile'],
            ['analogously', StemmerMode::AGGRESSIVE, 'analog'],
            ['viabilization', StemmerMode::AGGRESSIVE, 'viabil'],
            ['precedential', StemmerMode::AGGRESSIVE, 'precedenti'],
            ['declaration', StemmerMode::AGGRESSIVE, 'declar'],
            ['operator', StemmerMode::AGGRESSIVE, 'oper'],
            ['feudalism', StemmerMode::AGGRESSIVE, 'feudal'],
            ['decisiveness', StemmerMode::AGGRESSIVE, 'decis'],
            ['hopefulness', StemmerMode::AGGRESSIVE, 'hope'],
            ['callousness', StemmerMode::AGGRESSIVE, 'callous'],
            ['formality', StemmerMode::AGGRESSIVE, 'formal'],
            ['sensitivity', StemmerMode::AGGRESSIVE, 'sensit'],
            ['sensibility', StemmerMode::AGGRESSIVE, 'sensibl'],

            // Step 3
            ['triplicate', StemmerMode::AGGRESSIVE, 'triplic'],
            ['formative', StemmerMode::AGGRESSIVE, 'form'],
            ['formalize', StemmerMode::AGGRESSIVE, 'formal'],
            ['electricity', StemmerMode::AGGRESSIVE, 'electr'],
            ['electrical', StemmerMode::AGGRESSIVE, 'electr'],
            ['hopeful', StemmerMode::AGGRESSIVE, 'hope'],
            ['goodness', StemmerMode::AGGRESSIVE, 'good'],

            // Step 4
            ['revival', StemmerMode::AGGRESSIVE, 'reviv'],
            ['allowance', StemmerMode::AGGRESSIVE, 'allow'],
            ['inference', StemmerMode::AGGRESSIVE, 'infer'],
            ['airliner', StemmerMode::AGGRESSIVE, 'airlin'],
            ['gyroscopic', StemmerMode::AGGRESSIVE, 'gyroscop'],
            ['adjustable', StemmerMode::AGGRESSIVE, 'adjust'],
            ['defensible', StemmerMode::AGGRESSIVE, 'defens'],
            ['irritant', StemmerMode::AGGRESSIVE, 'irrit'],
            ['replacement', StemmerMode::AGGRESSIVE, 'replac'],
            ['adjustment', StemmerMode::AGGRESSIVE, 'adjust'],
            ['dependent', StemmerMode::AGGRESSIVE, 'depend'],
            ['adoption', StemmerMode::AGGRESSIVE, 'adopt'],
            ['homologism', StemmerMode::AGGRESSIVE, 'homolog'],
            ['activate', StemmerMode::AGGRESSIVE, 'activ'],
            ['angularity', StemmerMode::AGGRESSIVE, 'angular'],
            ['homologous', StemmerMode::AGGRESSIVE, 'homolog'],
            ['effective', StemmerMode::AGGRESSIVE, 'effect'],
            ['normalize', StemmerMode::AGGRESSIVE, 'normal'],

            // Step 5
            ['probate', StemmerMode::AGGRESSIVE, 'probat'],
            ['rate', StemmerMode::AGGRESSIVE, 'rate'],
            ['cease', StemmerMode::AGGRESSIVE, 'ceas'],
            ['controll', StemmerMode::AGGRESSIVE, 'control'],
            ['roll', StemmerMode::AGGRESSIVE, 'roll'],

            // Special cases for R1/R2
            ['gener', StemmerMode::AGGRESSIVE, 'gener'],
            ['general', StemmerMode::AGGRESSIVE, 'gener'],
            ['generous', StemmerMode::AGGRESSIVE, 'gener'],
            ['communication', StemmerMode::AGGRESSIVE, 'commun'],
            ['arsenal', StemmerMode::AGGRESSIVE, 'arsen'],

            // Double consonants (Step 1b)
            ['hopping', StemmerMode::AGGRESSIVE, 'hop'],
            ['tanned', StemmerMode::AGGRESSIVE, 'tan'],
            ['falling', StemmerMode::AGGRESSIVE, 'fall'],
            ['hissing', StemmerMode::AGGRESSIVE, 'hiss'],
            ['fizzing', StemmerMode::AGGRESSIVE, 'fizz'],
        ];
    }
}
