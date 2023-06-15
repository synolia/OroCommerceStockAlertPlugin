<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Mailer;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\EmailBundle\Form\Model\Email;
use Oro\Bundle\EmailBundle\Model\From;
use Oro\Bundle\EmailBundle\Provider\EmailRenderer;
use Oro\Bundle\EmailBundle\Sender\EmailModelSender;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Webmozart\Assert\Assert;

class SimpleEmailMailer
{
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected ConfigManager $configManager,
        protected EmailRenderer $renderer,
        protected EmailModelSender $mailer
    ) {
    }

    public function send(
        array $listEmails,
        string $templateName,
        Website $website,
        array $emailTemplateParams = []
    ): void {
        $emailTemplate = $this->findEmailTemplateByName($templateName);

        [$subjectRendered, $templateRendered] = $this->renderer->compileMessage($emailTemplate, $emailTemplateParams);

        foreach ($listEmails as $listEmail) {
            $email = new Email();
            $email->setFrom($this->getSenderEmail($website));
            $email->setTo([$listEmail]);
            $email->setTemplate($emailTemplate);
            $email->setBody($templateRendered);
            $email->setType($emailTemplate->getType());
            $email->setSubject($subjectRendered);
            $email->setOrganization($emailTemplate->getOrganization());
            $this->mailer->send($email);
        }
    }

    public function getConfigByWebsite(Website $website, string $configName): string
    {
        $this->configManager->setScopeIdFromEntity($website);

        return $this->configManager->get($configName);
    }

    protected function findEmailTemplateByName(string $emailTemplateName): EmailTemplate
    {
        $emailTemplate = $this->entityManager
            ->getRepository(EmailTemplate::class)
            ->findOneBy(['name' => $emailTemplateName]);
        Assert::notNull($emailTemplate);

        return $emailTemplate;
    }

    protected function getSenderEmail(Website $website): string
    {
        $senderEmail = $this->getConfigByWebsite($website, 'oro_notification.email_notification_sender_email');
        $senderName = $this->getConfigByWebsite($website, 'oro_notification.email_notification_sender_name');
        if ($senderEmail && $senderName) {
            return From::emailAddress(
                $senderEmail,
                $senderName
            )->toString();
        }

        return From::emailAddress(
            $this->configManager->get('oro_notification.email_notification_sender_email'),
            $this->configManager->get('oro_notification.email_notification_sender_name')
        )->toString();
    }
}
