# PHP Stemmer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dompat/stemmer.svg?style=flat-square)](https://packagist.org/packages/dompat/stemmer)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892bf.svg?style=flat-square)](https://php.net)
[![Total Downloads](https://img.shields.io/packagist/dt/dompat/stemmer.svg?style=flat-square)](https://packagist.org/packages/dompat/stemmer)

A strictly-typed stemming library for PHP 8.3+. This library helps reduce words to their base form, which is essential for quality full-text search, indexing, or text analysis.

## ✨ Features

- **Modern PHP:** Fully utilizes PHP 8.3+ features (Enums, strict typing).
- **Multiple Modes:**
  - `LIGHT`: Removes only basic suffixes (plurals, cases). Ideal for result highlighting (words remain readable).
  - `AGGRESSIVE`: Reduces words to their morphological root. Ideal for search indexes.
- **Customizable:** Easily extend with your own language drivers.
- **Supported Languages:** Czech, English

## 🚀 Installation

You can install the library via [Composer](https://getcomposer.org/):

```bash
composer require dompat/stemmer
```

## 📖 Usage

### Basic Example

```php
use Dompat\Stemmer\Stemmer;
use Dompat\Stemmer\Driver\CzechDriver;
use Dompat\Stemmer\Driver\EnglishDriver;
use Dompat\Stemmer\Enum\StemmerMode;

// 1. Using the main Stemmer manager
$stemmer = new Stemmer([
    new CzechDriver('cs'),
    new EnglishDriver('en'),
]);

echo $stemmer->stem('městě', 'cs'); // "město"
echo $stemmer->stem('working', 'en'); // "work"

// 2. Using drivers directly (optional)
$czechDriver = new CzechDriver('cs');
echo $czechDriver->stem('nejkrásnějšímu', StemmerMode::AGGRESSIVE); // "krásn"

// 3. Custom locale for specific needs (e.g., Slovak)
$skDriver = new CzechDriver('sk');
echo $skDriver->stem('meste', StemmerMode::LIGHT); // "mesto"
```

### Runtime Driver Registration

```php
use Dompat\Stemmer\Stemmer;
use Your\Custom\CustomDriver;

$stemmer = new Stemmer();
$stemmer->addDriver(new CustomDriver('xy'));

echo $stemmer->stem('word', 'xy');
```

## 🌍 Supported Languages

| Language | Code | Driver |
| :--- | :--- | :--- |
| **Czech** | `cs` | `CzechDriver` |
| **English** | `en` | `EnglishDriver` |

> **Missing a language?** Feel free to create your own driver by implementing `DriverInterface` and submit a Pull Request!

## ⚙️ Mode Differences

### LIGHT
Suitable for **autocomplete** and **word highlighting** in text. Removes only the most necessary suffixes so the word remains understandable to the user.

### AGGRESSIVE
Suitable for **search indexing**. Reduces the word to its core, increasing search relevance across different word forms.

### Comparison Table

| Word (EN)     | Light Mode    | Aggressive Mode |
|:--------------|:--------------|:----------------|
| `declaration` | `declaration` | `declar`        |
| `happiness`   | `happiness`   | `happi`         |
| `happy`       | `happi`       | `happi`         |
| `working`     | `work`        | `work`          |

| Word (CS)        | Light Mode | Aggressive Mode |
|:-----------------|:-----------|:----------------|
| `nejkrásnějšímu` | `krásnějš` | `krás`          |
| `čínští`         | `čínsk`    | `číns`          |
| `babizna`        | `babizn`   | `bab`           |
| `městě`          | `měst`     | `měst`          |

## 🛠 Development and Testing

If you want to contribute to the library, you can run tests and static analysis:

```bash
# Run tests
vendor/bin/phpunit tests

# Static analysis (PHPStan)
vendor/bin/phpstan analyse
```

## 📄 License

This library is licensed under the [MIT License](LICENSE).
