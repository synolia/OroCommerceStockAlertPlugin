<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Mailer\DirectMailer;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Swift_Message;

class StockAlertNotificationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * @var DirectMailer
     */
    protected $mailer;

    public function __construct(ConfigManager $configManager, DirectMailer $mailer)
    {
        $this->configManager = $configManager;
        $this->mailer = $mailer;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $body = JSON::decode($message->getBody());
        if (empty($body['customerEmail'])) {
            return self::REJECT;
        }
        $message = new Swift_Message(
            'Stock Available',
            \sprintf(
                'Dear %s, <br><br> The product <strong>%s</strong> with SKU <strong>%s</strong> is now <strong>with stock</strong>',
                $body['customerFullName'],
                $body['productName'],
                $body['productSKU']
            ),
            'text/html'
        );
        $message->setFrom($this->getSenderEmail());
        $message->setTo($body['customerEmail']);
        $this->mailer->send($message);
        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [Topics::SYNOLIA_STOCK_ALERT_MESSAGE];
    }

    private function getSenderEmail()
    {
        return $this->configManager->get('oro_notification.email_notification_sender_email');
    }
}
