<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Mailer\Mailer;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class StockAlertNotificationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface
{
    /**
     * @var ConfigManager
     */
    protected $configManager;
    /**
     * @var Mailer
     */
    protected $mailer;

    public function __construct(
        ConfigManager $configManager,
        Mailer $mailer,
        TranslatorInterface $translator,
        Environment $twig
    )
    {
        $this->configManager = $configManager;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->twig = $twig;
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
        $message = new Email();
        $message->from($this->getSenderEmail())
            ->to($body['customerEmail'])
            ->subject($this->translator->trans('synolia.stockalert.mail.subject'))
            ->html(
                $this->twig->render('@SynoliaStockAlert/mail/available.html.twig', $body)
            );

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
