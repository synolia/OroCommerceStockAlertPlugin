<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Mailer\UserTemplateEmailSender;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Synolia\Bundle\StockAlertBundle\Async\Topic\StockAlertNotificationTopic;

class StockAlertNotificationProcessor implements
    MessageProcessorInterface,
    TopicSubscriberInterface
{
    public function __construct(
        protected UserTemplateEmailSender $userTemplateEmailSender,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws \JsonException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $body = JSON::decode($message->getBody());

        $id = $body['customerUserId'];

        $customerUserRepository = $this->entityManager->getRepository(CustomerUser::class);
        /** @var CustomerUser $customerUser */
        $customerUser = $customerUserRepository->findOneBy(['id' => $id]);

        $emailTemplateParams = [
            'customerFullName' => $customerUser->getFullName(),
            'productName' => $body['productName'],
            'productSKU' => $body['productSKU'],
            'websiteName' => $customerUser->getWebsite(),
        ];

        $this->userTemplateEmailSender->sendUserTemplateEmail(
            $customerUser,
            'synolia_stock_alert_email',
            $emailTemplateParams
        );

        return self::ACK;
    }

    public static function getSubscribedTopics(): array
    {
        return [StockAlertNotificationTopic::getName()];
    }
}
