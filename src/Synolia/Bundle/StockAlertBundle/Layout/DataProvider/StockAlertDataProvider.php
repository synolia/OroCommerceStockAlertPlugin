<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Layout\DataProvider;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertDataProvider
{
    protected EntityManager $entityManager;

    protected TokenAccessor $tokenAccessor;

    public function __construct(
        EntityManager $entityManager,
        TokenAccessor $tokenAccessor
    ) {
        $this->entityManager = $entityManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    /**
     * @throws NotSupported
     */
    public function getStockAlertForProduct(Product $product): ?StockAlert
    {
        $stockAlertRepository = $this->entityManager->getRepository(StockAlert::class);
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        if ($user && $organization) {
            return $stockAlertRepository->findOneBy([
                'product' => $product,
                'customerUser' => $user,
                'organization' => $organization,
            ]);
        }

        return null;
    }
}
