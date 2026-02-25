<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests\Exception;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Exception\DriverNotFoundException;
use PHPUnit\Framework\TestCase;

final class DriverNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessageWithAvailableDrivers(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getLocale')->willReturn('en');
        $className = get_class($driver);

        $exception = new DriverNotFoundException('cs', ['en' => $driver]);

        $this->assertSame('cs', $exception->getMissingLocale());
        $this->assertSame(['en' => $driver], $exception->getAvailableDrivers());
        $this->assertStringContainsString('No driver found for locale "cs".', $exception->getMessage());
        $this->assertStringContainsString("Available drivers: $className [en].", $exception->getMessage());
    }

    public function testExceptionMessageWithoutDrivers(): void
    {
        $exception = new DriverNotFoundException('fr');

        $this->assertSame('fr', $exception->getMissingLocale());
        $this->assertSame([], $exception->getAvailableDrivers());
        $this->assertStringContainsString('No driver found for locale "fr". No drivers are registered.', $exception->getMessage());
    }
}
