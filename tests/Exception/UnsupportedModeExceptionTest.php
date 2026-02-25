<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests\Exception;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Enum\StemmerMode;
use Dompat\Stemmer\Exception\UnsupportedModeException;
use PHPUnit\Framework\TestCase;

final class UnsupportedModeExceptionTest extends TestCase
{
    public function testExceptionMessageFormatting(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $receivedMode = StemmerMode::AGGRESSIVE;
        $supportedModes = [StemmerMode::LIGHT];

        $exception = new UnsupportedModeException($driver, $receivedMode, $supportedModes);

        $this->assertStringContainsString('does not support mode "Dompat\Stemmer\Enum\StemmerMode::AGGRESSIVE"', $exception->getMessage());
        $this->assertStringContainsString('Supported modes: ["Dompat\Stemmer\Enum\StemmerMode::LIGHT"]', $exception->getMessage());
        $this->assertSame($driver, $exception->getDriver());
        $this->assertSame($receivedMode, $exception->getReceivedMode());
        $this->assertSame($supportedModes, $exception->getSupportedModes());
    }
}
