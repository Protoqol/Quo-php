![Quo Preview](https://cms.protoqol.nl/assets/2ecc5f44-5fe5-4f15-95d6-ba365f4fcd5c)

![Build status](https://img.shields.io/github/actions/workflow/status/Protoqol/Quo-php/testkit.yml?style=flat-square&color=%23ec135b&logo=php)
![Packagist Version](https://img.shields.io/packagist/v/protoqol/quo-php?style=flat-square&logo=packagist&color=%23ec135b)
![PHP Version](https://img.shields.io/packagist/php-v/protoqol/quo-php?style=flat-square&logo=php&color=%23ec135b)
![LGPL-3.0-only license](https://img.shields.io/packagist/l/Protoqol/quo-php?style=flat-square&color=%23ea135a)

Quo is a cross-platform variable dumper designed to make debugging easier. It receives data from your application and
displays it in a clean desktop interface, allowing you to inspect complex values in real-time without cluttering your
terminal or browser console.

> **Note**: This package requires the [Quo desktop client](https://github.com/Protoqol/Quo) to be running to display the
> debug data.

### Noteworthy features

- **Multiple arguments**: Inspect multiple variables or expressions in a single call.
- **Rich Metadata**: Capture stack traces, system metrics, memory addresses, and more.

### Requirements

- **PHP**: >= 7.1

### Installation

Add `quo-php` to your project using composer:

```bash
composer require protoqol/quo-php --dev
```

### Usage

Use the `quo` function and pass variables to inspect:

```php
<?php

require_once 'vendor/autoload.php';

$user_id = 42;
$user = [
    'id' => 1,
    'username' => 'jdoe'
];

// Dump a single variable
quo($user_id);

// Dump multiple variables at once
quo($user_id, $user);

// Some quick maths
quo(42 * 42);
```

### Configuration

You can customise the Quo server address in your `composer.json` or environment variables.

#### Via composer.json

Add an `extra` block to your `composer.json`:

```json
{
  "extra": {
    "quo-php": {
      "host": "127.0.0.1",
      "port": 7312
    }
  }
}
```

> The correct port can be found in the bottom left in the Quo client. Do note that it is **not** to change host.

---

## Dependency justification

| Package       | Used for                                                                                                                     |
|---------------|------------------------------------------------------------------------------------------------------------------------------|
| `ext-json`    | Serializes debug data into JSON format for transmission to the Quo client.                                                   |
| `ext-curl`    | Transmits the captured debug payloads to the Quo desktop client over HTTP.                                                   |
| `ramsey/uuid` | Generates unique UUIDv4 identifiers for each individual dump event, enabling the client to uniquely identify and track them. |

---

## License

Quo is open-source software licensed under the [GPL-3 licence](LICENSE).

