<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Exception;

use Dompat\Stemmer\Contract\DriverInterface;

final class DriverNotFoundException extends StemmerException
{
    /**
     * @param DriverInterface[] $availableDrivers
     */
    public function __construct(
        private readonly string $missingLocale,
        private readonly array  $availableDrivers = []
    ) {
        $message = sprintf('No driver found for locale "%s".', $missingLocale);

        if ($availableDrivers !== []) {
            $info = [];
            foreach ($availableDrivers as $driver) {
                $info[] = sprintf('%s [%s]', get_class($driver), $driver->getLocale());
            }

            $message .= sprintf(' Available drivers: %s.', implode('; ', $info));
        } else {
            $message .= ' No drivers are registered.';
        }

        parent::__construct($message);
    }

    public function getMissingLocale(): string
    {
        return $this->missingLocale;
    }

    /**
     * @return DriverInterface[]
     */
    public function getAvailableDrivers(): array
    {
        return $this->availableDrivers;
    }
}
