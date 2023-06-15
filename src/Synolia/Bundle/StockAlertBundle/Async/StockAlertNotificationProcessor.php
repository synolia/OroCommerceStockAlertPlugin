<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async;

use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Synolia\Bundle\StockAlertBundle\Async\Topic\StockAlertNotificationTopic;
use Synolia\Bundle\StockAlertBundle\Mailer\SimpleEmailMailer;

class StockAlertNotificationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface
{
    public function __construct(
        protected WebsiteManager $websiteManager,
        protected SimpleEmailMailer $simpleEmailMailer,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $body = JSON::decode($message->getBody());
        if (empty($body['customerEmail'])) {
            return self::REJECT;
        }

        $website = $this->websiteManager->getCurrentWebsite();
        if (!$website) {
            return self::REJECT;
        }

        $this->simpleEmailMailer->send(
            [$body['customerEmail']],
            'synolia_stock_alert_email',
            $website,
            [
                'customerFullName' => $body['customerFullName'],
                'productName' => $body['productName'],
                'productSKU' => $body['productSKU'],
                'websiteName' => $website->getName(),
            ]
        );

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [StockAlertNotificationTopic::getName()];
    }
}
