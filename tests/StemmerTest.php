<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Tests;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Enum\StemmerMode;
use Dompat\Stemmer\Exception\DriverNotFoundException;
use Dompat\Stemmer\Stemmer;
use PHPUnit\Framework\TestCase;

final class StemmerTest extends TestCase
{
    public function testConstructorRegistersDrivers(): void
    {
        $driver1 = $this->createStub(DriverInterface::class);
        $driver1->method('getLocale')->willReturn('en');

        $driver2 = $this->createStub(DriverInterface::class);
        $driver2->method('getLocale')->willReturn('cs');

        $stemmer = new Stemmer([$driver1, $driver2]);

        $this->assertSame($driver1, $stemmer->getDriver('en'));
        $this->assertSame($driver1, $stemmer->getDriver('en_US'));
        $this->assertSame($driver2, $stemmer->getDriver('cs'));
    }

    public function testAddDriver(): void
    {
        $stemmer = new Stemmer();
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getLocale')->willReturn('de');

        $stemmer->addDriver($driver);

        $this->assertSame($driver, $stemmer->getDriver('de'));
    }

    public function testStemDelegatesToDriver(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->method('getLocale')->willReturn('en');
        $driver->expects($this->once())
            ->method('stem')
            ->with('testing', StemmerMode::LIGHT)
            ->willReturn('test');

        $stemmer = new Stemmer([$driver]);

        $result = $stemmer->stem('testing', 'en');
        $this->assertSame('test', $result);
    }

    public function testStemWithMode(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->method('getLocale')->willReturn('cs');
        $driver->expects($this->once())
            ->method('stem')
            ->with('nejlepší', StemmerMode::AGGRESSIVE)
            ->willReturn('lepš');

        $stemmer = new Stemmer([$driver]);

        $result = $stemmer->stem('nejlepší', 'cs', StemmerMode::AGGRESSIVE);
        $this->assertSame('lepš', $result);
    }

    public function testGetDriverThrowsExceptionWhenNotFound(): void
    {
        $stemmer = new Stemmer();

        $this->expectException(DriverNotFoundException::class);
        $this->expectExceptionMessage('No driver found for locale "fr". No drivers are registered.');
        
        $stemmer->getDriver('fr');
    }

    public function testGetDriverThrowsExceptionWithAvailableDrivers(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getLocale')->willReturn('en');
        
        $stemmer = new Stemmer([$driver]);

        $this->expectException(DriverNotFoundException::class);
        $this->expectExceptionMessage('No driver found for locale "cs". Available drivers:');
        
        $stemmer->getDriver('cs');
    }

    public function testGetDriverNormalizesLocale(): void
    {
        $driver = $this->createStub(DriverInterface::class);
        $driver->method('getLocale')->willReturn('en');
        
        $stemmer = new Stemmer([$driver]);
        
        $this->assertSame($driver, $stemmer->getDriver('EN-gb'));
    }
}
