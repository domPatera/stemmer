<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Exception;

use Dompat\Stemmer\Contract\DriverInterface;
use Dompat\Stemmer\Contract\StemmerModeInterface;

final class UnsupportedModeException extends StemmerException
{
    /**
     * @param DriverInterface $driver
     * @param StemmerModeInterface $receivedMode
     * @param StemmerModeInterface[] $supportedModes
     */
    public function __construct(
        private readonly DriverInterface      $driver,
        private readonly StemmerModeInterface $receivedMode,
        private readonly array                $supportedModes
    ) {
        $driverName = get_class($driver);
        $receivedName = $this->formatMode($receivedMode);

        $supportedNames = array_map(
            fn(StemmerModeInterface $m) => $this->formatMode($m),
            $supportedModes
        );

        $message = sprintf(
            'Driver "%s" does not support mode "%s". Supported modes: ["%s"].',
            $driverName,
            $receivedName,
            implode('", "', $supportedNames)
        );

        parent::__construct($message);
    }

    private function formatMode(StemmerModeInterface $mode): string
    {
        /** @noinspection PhpConditionAlreadyCheckedInspection */
        if ($mode instanceof \UnitEnum) {
            return sprintf('%s::%s', get_class($mode), $mode->name);
        }

        return get_class($mode);
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function getReceivedMode(): StemmerModeInterface
    {
        return $this->receivedMode;
    }

    /**
     * @return StemmerModeInterface[]
     */
    public function getSupportedModes(): array
    {
        return $this->supportedModes;
    }
}
