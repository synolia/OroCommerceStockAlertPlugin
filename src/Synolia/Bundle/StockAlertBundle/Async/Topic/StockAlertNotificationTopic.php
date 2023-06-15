<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async\Topic;

use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockAlertNotificationTopic extends AbstractTopic
{
    public static function getName(): string
    {
        return 'synolia.stock_alert.notification';
    }

    public static function getDescription(): string
    {
        return '';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'customerEmail',
                'customerFullName',
                'productName',
                'productSKU',
            ])
            ->setRequired([
                'customerEmail',
                'customerFullName',
                'productName',
                'productSKU',
            ])
            ->addAllowedTypes('customerEmail', 'string')
            ->addAllowedTypes('customerFullName', 'string')
            ->addAllowedTypes('productName', 'string')
            ->addAllowedTypes('productSKU', 'string')
        ;
    }
}
