<?php

declare(strict_types=1);

namespace Dompat\Stemmer\Contract;

interface DriverInterface extends \Stringable
{
    public function getLocale(): string;

    public function stem(string $word, StemmerModeInterface $mode): string;
}
