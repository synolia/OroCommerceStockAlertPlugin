<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Handler;

use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TokenAccessor
     */
    protected $tokenAccessor;

    public function __construct(
        EntityManager $entityManager,
        TokenAccessor $tokenAccessor
    ) {
        $this->entityManager = $entityManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    public function create($product)
    {
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        if (!$user instanceof CustomerUser || !$organization instanceof Organization) {
            return false;
        }

        $stockAlert = $this->getStockAlert($product, $user, $organization);
        if ($stockAlert instanceof StockAlert) {
            return $stockAlert;
        }

        $stockAlert = new StockAlert();
        $stockAlert->setProduct($product);
        $stockAlert->setCustomer($user->getCustomer());
        $stockAlert->setCustomerUser($user);
        $stockAlert->setOrganization($organization);
        $stockAlert->setExpirationDate($this->getExpirationDate());
        $this->entityManager->persist($stockAlert);
        $this->entityManager->flush();

        return $stockAlert;
    }

    public function delete(StockAlert $stockAlert)
    {
        $this->entityManager->remove($stockAlert);
        $this->entityManager->flush();
    }

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

    public function deleteStockAlerts(array $stockAlerts)
    {
        foreach ($stockAlerts as $stockAlert) {
            $this->delete($stockAlert);
        }
    }

    private function getExpirationDate(): DateTime
    {
        return (new DateTime())->add(new DateInterval('P3M'));
    }

    private function getStockAlert(Product $product, CustomerUser $user, Organization $organization)
    {
        $stockAlertRepository = $this->entityManager->getRepository(StockAlert::class);
        return $stockAlertRepository->findOneBy([
            'product' => $product,
            'customerUser' => $user,
            'organization' => $organization
        ]);
    }
}
