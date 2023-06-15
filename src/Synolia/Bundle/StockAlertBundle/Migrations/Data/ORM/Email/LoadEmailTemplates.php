<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Migrations\Data\ORM\Email;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\EmailBundle\Migrations\Data\ORM\AbstractEmailFixture;
use Oro\Bundle\EmailBundle\Model\EmailTemplate;
use Oro\Bundle\MigrationBundle\Fixture\VersionedFixtureInterface;

class LoadEmailTemplates extends AbstractEmailFixture implements VersionedFixtureInterface
{
    public function getEmailsDir(): string
    {
        return $this->container
            ->get('kernel')
            ->locateResource('@SynoliaStockAlertBundle/Migrations/Data/ORM/data/emails')
        ;
    }

    public function getVersion(): string
    {
        return 'v1_0';
    }

    protected function findExistingTemplate(ObjectManager $manager, array $template): ?EmailTemplate
    {
        if (empty($template['params']['name'])) {
            return null;
        }

        return $manager->getRepository('OroEmailBundle:EmailTemplate')->findOneBy([
            'name' => $template['params']['name'],
        ]);
    }
}
