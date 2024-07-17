[![License](https://img.shields.io/packagist/l/synolia/sylius-akeneo-plugin.svg)](LICENCE)
[![CI](https://github.com/synolia/OroCommerceStockAlertPlugin/actions/workflows/ci.yml/badge.svg)](https://github.com/synolia/OroCommerceStockAlertPlugin/actions/workflows/ci.yml)
[![Version](http://poser.pugx.org/synolia/orocommerce-stock-alert-plugin/v)](https://packagist.org/packages/synolia/orocommerce-stock-alert-plugin)
[![Total Downloads](http://poser.pugx.org/synolia/orocommerce-stock-alert-plugin/downloads)](https://packagist.org/packages/synolia/orocommerce-stock-alert-plugin)

# Oro Stock Alert Bundle
This plugin allows the customer to subscribe to a product whose stock level is equal or below zero and to be notified by email when the product is back in stock

## Features

* Show the stock level in the product view and a subscribe button when the product stock level is equal or below zero - [Documentation](docs/SHOW.md)
* Receive an email when the product is back in stock - [Documentation](docs/EMAIL.md)
* View all products a customer has subscribed to (FO and BO) - [Documentation](docs/LIST.md)

## Requirements

| | Version |
| :--- |:--------|
| PHP  | 8.3     |
| OroCommerce | 6.0     |

## Installation

1. Install the Plugin using Composer:
```shell
composer require synolia/orocommerce-stock-alert-plugin
```
2. Run the Migrations
```shell
bin/console oro:migration:load --force
```
3. Clear Cache
```shell
bin/console cache:clear
```
4. Install & Build the Assets
```shell
bin/console oro:assets:install --symlink
```

## Contributing

* See [How to contribute](CONTRIBUTING.md)

## License

This library is under the [EUPL-1.2 license](LICENSE).

## Credits

Developed by [Synolia](https://synolia.com/).
