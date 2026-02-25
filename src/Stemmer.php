<?php

declare(strict_types=1);

namespace Dompat\Stemmer;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Enum\StemmerMode;
use Dompat\Stemmer\Exception\DriverNotFoundException;

/**
 * Main entry point for the Stemmer library.
 *
 * This manager delegates the stemming process to specific drivers based on the locale.
 * It ensures the correct algorithm is selected for the given language.
 */
final class Stemmer
{
    /**
     * @var array<string, DriverInterface>
     */
    private array $drivers = [];

    /**
     * @param iterable<DriverInterface> $drivers List of drivers to register immediately.
     */
    public function __construct(iterable $drivers = [])
    {
        foreach ($drivers as $driver) {
            $this->addDriver($driver);
        }
    }

    /**
     * Stems a word using the driver for the specified locale.
     *
     * @param string $word The word to be stemmed.
     * @param string $locale The locale (e.g., 'en', 'en_US', 'cs').
     * @param StemmerMode $mode The stemming mode (LIGHT for highlighting, AGGRESSIVE for search).
     *
     * @return string The stemmed root of the word.
     * @throws DriverNotFoundException If no driver is registered for the given locale.
     */
    public function stem(string $word, string $locale, StemmerMode $mode = StemmerMode::LIGHT): string
    {
        return $this->getDriver($locale)->stem($word, $mode);
    }

    /**
     * Registers a new language driver.
     */
    public function addDriver(DriverInterface $driver): self
    {
        $this->drivers[$driver->getLocale()] = $driver;

        return $this;
    }

    /**
     * Retrieves the driver responsible for the given locale.
     *
     * It automatically normalizes the locale (e.g., converts 'en_US' to 'en').
     *
     * @throws DriverNotFoundException
     */
    public function getDriver(string $locale): DriverInterface
    {
        $lang = strtolower(substr($locale, 0, 2));

        if (!isset($this->drivers[$lang])) {
            throw new DriverNotFoundException($lang, $this->drivers);
        }

        return $this->drivers[$lang];
    }
}
