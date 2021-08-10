[![License](https://img.shields.io/packagist/l/synolia/sylius-akeneo-plugin.svg)](LICENCE)
![Tests](TODO)
[![Version](TODO)](TODO)
[![Total Downloads](TODO)](TODO)

# Oro Stock Alert Bundle
This plugin allows the customer to subscribe to a product out of stock, to be notified when it is back in stock

## Features

* Show the stock in the product view and subscribe when not in stock - [Documentation](docs/SHOW.md)
* Receive an email when the product is back in stock - [Documentation](docs/EMAIL.md)
* View all products a customer has subscribed to (FO and BO) - [Documentation](docs/LIST.md)

## Requirements

| | Version |
| :--- | :--- |
| PHP  | 7.4, 8.0 |
| OroCommerce | 4.2 |

## Installation

1. Install the plugin using composer:
```shell
composer require synolia/orocommerce-stock-alert-plugin
```
2. Run the migrations
```shell
bin/console oro:migration:load --force
```
3. Clear cache
```shell
bin/console cache:clear
```

## Contributing

* See [How to contribute](CONTRIBUTING.md)

## License

This library is under the [EUPL-1.2 license](LICENSE).

## Credits

Developed by [Synolia](https://synolia.com/).