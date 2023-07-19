<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Oro\Bundle\UserBundle\Entity\User;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertHandler
{
    public function __construct(
        protected EntityManager $entityManager,
        protected TokenAccessor $tokenAccessor,
        protected ConfigManager $configManager
    ) {
    }

    /**
     * @throws OptimisticLockException
     * @throws NotSupported
     * @throws ORMException
     */
    public function create($product, ?string $recipientEmail = null): StockAlert|bool
    {
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        $stockAlert = $this->getStockAlert($product, $user, $organization, $recipientEmail);
        if ($stockAlert instanceof StockAlert) {
            return $stockAlert;
        }

        $stockAlert = new StockAlert();
        $stockAlert->setProduct($product);
        $stockAlert->setOwner($this->getOwner());
        if ($user instanceof CustomerUser) {
            $stockAlert->setCustomer($user->getCustomer());
            $stockAlert->setCustomerUser($user);
        }
        if ($organization instanceof Organization) {
            $stockAlert->setOrganization($organization);
        }
        if (!empty($recipientEmail)) {
            $accessor = PropertyAccess::createPropertyAccessor();
            $accessor->setValue($stockAlert, 'recipient_email', $recipientEmail);
        }
        $stockAlert->setExpirationDate($this->getExpirationDate());
        $this->entityManager->persist($stockAlert);
        $this->entityManager->flush();

        return $stockAlert;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function delete(StockAlert $stockAlert): void
    {
        $this->entityManager->remove($stockAlert);
        $this->entityManager->flush();
    }

    /**
     * @throws NotSupported
     */
    public function deleteByProduct(Product $product): bool
    {
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        if (!$user instanceof CustomerUser || !$organization instanceof Organization) {
            return false;
        }

        $stockAlert = $this->getStockAlert($product, $user, $organization);
        if (!$stockAlert instanceof StockAlert) {
            return false;
        }

        $this->delete($stockAlert);

        return true;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function deleteStockAlerts(array $stockAlerts): void
    {
        foreach ($stockAlerts as $stockAlert) {
            $this->delete($stockAlert);
        }
    }

    protected function getExpirationDate(): \DateTime
    {
        return (new \DateTime())->add(new \DateInterval('P3M'));
    }

    /**
     * @throws NotSupported
     */
    protected function getStockAlert(
        Product $product,
        ?CustomerUser $user,
        ?Organization $organization,
        ?string $recipientEmail = null
    ): ?StockAlert {
        $stockAlertRepository = $this->entityManager->getRepository(StockAlert::class);

        $params = ['product' => $product];
        if ($user instanceof CustomerUser) {
            $params['customerUser'] = $user;
        }
        if ($organization instanceof Organization) {
            $params['organization'] = $organization;
        }
        if (!empty($recipientEmail)) {
            $params['recipient_email'] = $recipientEmail;
        }

        return $stockAlertRepository->findOneBy($params);
    }

    /**
     * @throws NotSupported
     */
    protected function getOwner(): ?User
    {
        $ownerId = $this->configManager->get('oro_customer.default_customer_owner');
        if (!$ownerId) {
            throw new \RuntimeException('Application owner is not set');
        }

        return $this->entityManager->getRepository(User::class)->find($ownerId);
    }
}
