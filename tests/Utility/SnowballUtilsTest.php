<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests\Utility;

use Dompat\Stemmer\Utility\SnowballUtils;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class SnowballUtilsTest extends TestCase
{
    #[DataProvider('r1Provider')]
    public function testFindR1(string $word, string $vowels, int $expected): void
    {
        $this->assertSame($expected, SnowballUtils::findR1($word, $vowels));
    }

    /**
     * @return array<array{string, string, int}>
     */
    public static function r1Provider(): array
    {
        return [
            ['beautiful', 'aeiouy', 4],
            ['beauty', 'aeiouy', 4],
            ['apple', 'aeiouy', 1],
            ['try', 'aeiouy', 3],
            ['stress', 'aeiouy', 4],
            ['abc', 'aeiouy', 1],
        ];
    }

    #[DataProvider('r2Provider')]
    public function testFindR2(string $word, string $vowels, int $r1Index, int $expected): void
    {
        $this->assertSame($expected, SnowballUtils::findR2($word, $vowels, $r1Index));
    }

    /**
     * @return array<array{string, string, int, int}>
     */
    public static function r2Provider(): array
    {
        return [
            ['beautiful', 'aeiouy', 4, 6],
            ['communism', 'aeiouy', 3, 5],
        ];
    }
}
