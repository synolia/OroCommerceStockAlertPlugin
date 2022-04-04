<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Oro\Bundle\CustomerBundle\EventListener\AbstractCustomerViewListener;

class CustomerViewListener extends AbstractCustomerViewListener
{
    protected function getCustomerViewTemplate(): string
    {
        return '@SynoliaStockAlert/Customer/alert_view.html.twig';
    }

    protected function getCustomerLabel(): string
    {
        return 'Stock Alert';
    }

    protected function getCustomerUserViewTemplate(): string
    {
        return '@SynoliaStockAlert/CustomerUser/alert_view.html.twig';
    }

    protected function getCustomerUserLabel(): string
    {
        return 'Stock Alert';
    }
}
