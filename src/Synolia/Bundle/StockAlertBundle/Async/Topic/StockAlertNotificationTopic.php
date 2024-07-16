<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async\Topic;

use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
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
                'customerUserId',
                'productName',
                'productSKU',
            ])
            ->setRequired([
                'customerUserId',
                'productName',
                'productSKU',
            ])
            ->addAllowedTypes('customerUserId', 'int')
            ->addAllowedTypes('productName', 'string')
            ->addAllowedTypes('productSKU', 'string')
        ;
    }
}
