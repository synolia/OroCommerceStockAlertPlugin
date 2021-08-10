<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Layout\DataProvider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertDataProvider
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

    public function getStockAlertForProduct(Product $product)
    {
        $stockAlertRepository = $this->entityManager->getRepository(StockAlert::class);
        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        return $stockAlertRepository->findOneBy([
            'product' => $product,
            'customerUser' => $user,
            'organization' => $organization
        ]);
    }
}
