![Quo for php](assets/quo-php-trans.png)

<p align="center">
    <a href="https://github.com/protoqol/quo-php/actions/workflows/testkit.yml">	
       <img alt="Github action status" src="https://github.com/protoqol/quo-php/actions/workflows/testkit.yml/badge.svg">
    </a>    
    <a href="https://packagist.org/packages/protoqol/quo-php">	
       <img alt="Packagist Version" src="https://img.shields.io/packagist/v/protoqol/quo-php.svg">
    </a>
    <a href="https://twitter.com/intent/follow?screen_name=Protoqol_XYZ">
        <img src="https://img.shields.io/twitter/follow/Protoqol_XYZ.svg?label=%40Protoqol_XYZ&style=social"
            alt="Follow Protoqol on Twitter">
    </a>
</p>

# Quo for PHP

[Quo is a free, open-source, client-side debugger and can be found here](https://github.com/Protoqol/Quo).

This specific package is a companion package for Quo for PHP.

> Using a framework? No problem.
>
> Quo is framework-agnostic and can run on any PHP (see requirements) project using composer.

## Installation

#### Requirements

| Requirement | Version    |
|-------------|------------|
| PHP         | ^7.1, ^8.1 |
| ext-json    | *          |
| ext-iconv   | *          |
| ext-curl    | *          |

[//]: # (#### Looking for another companion package?)

[//]: # ()

[//]: # (- Javascript &#40;unreleased&#41;)

#### To install run the following command.

```bash
composer require protoqol/quo-php
```

## Usage
Quo has a function called `quo()` which you can call anywhere, every variable passed to it will appear in your Quo Client.

```php
quo($var, ...$moreVars);
```

## Configuration

> Note that for the beta version of Quo a custom hostname and port can not be used yet, this will always default to 127.0.0.1:7312.
> However, when your PHP application does not run on the top level of your OS it might be required to change the hostname and port.
> The config hostname should always point to your top level localhost address.

Quo for PHP has an .ini file located at meta/quo-config.ini. This file stores the configuration Quo uses.

```ini
[general]
# Comma separated hostnames where Quo should not run.
DISABLED_ON_DOMAIN = <disabled_domain>

[exception]
# Should Quo throw an exception when no connection is made to the client?
# If you're trying to get Quo to work this might come in useful, if not, keep it off.
NO_CONNECTION = 0

[http]
# Where Quo sends its payload to
HOSTNAME = 127.0.0.1
PORT = 7312
# Internal use only - holds timestamp of last connection check.
CONNECTION_VERIFIED = 0

[encryption]
# Encrypt all data sent?
ENABLED = 0
# If ENABLED = 1 you should supply the public key retrieved from the Quo client here.
PUBLIC_KEY = <key>

[debug]
# Throw all exceptions, everywhere, anytime.
ENABLED = 0
```

## CLI

You can edit the configuration via the CLI with the following commands.

#### Change default host and port.

```bash
php ./vendor/bin/quo [hostname] [port]
```

#### Change host and port to pre-configurations.

```bash
php ./vendor/bin/quo [ -vb --virtualbox ]  # Changes it to `10.0.2.2:7312`
php ./vendor/bin/quo [ -d --docker ]       # Changes it to `host.docker.internal:7312`
php ./vendor/bin/quo [ -l --local ]        # Changes it to `127.0.0.1:7312`
```

## Custom config

You can also store a quo-config file in your project root directory.
Create a new .ini file in wherever you want and use the command below to set it as default config.

```bash
php ./vendor/bin/quo set-custom-config [absolute_file_path_to_ini]
```

And you're all set!

## Issues

#### Issues, bugs and feature requests can be reported [here!](https://github.com/Protoqol/quo-php/issues/new/choose)

## Contributing

See [Contributing](CONTRIBUTING.md) to see how you can contribute to Quo for PHP!

## Contributors

- [Quinten Justus](https://github.com/QuintenJustus)
- [Contributors](https://github.com/Protoqol/quo-php/graphs/contributors)

## License

Quo for PHP is licensed under the MIT License. Please see [License File](LICENSE) for more information.
