<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener\Frontend;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Event\BuildResultProductListEvent;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class ProductListStockAlertListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenAccessorInterface $tokenAccessor
    ) {
    }

    public function onBuildResult(BuildResultProductListEvent $event): void
    {
        $productViews = $event->getProductViews();
        if (\count($productViews) === 0) {
            return;
        }

        $productIds = [];
        foreach ($productViews as $productView) {
            $productIds[] = $productView->getId();
        }

        $user = $this->tokenAccessor->getUser();
        $organization = $this->tokenAccessor->getOrganization();

        $products = $this->entityManager
            ->getRepository(Product::class)
            ->findBy(['id' => $productIds]);
        $stockAlerts = $this->entityManager
            ->getRepository(StockAlert::class)
            ->findBy(['product' => $products, 'customerUser' => $user, 'organization' => $organization]);
        $stockAlerts = $this->keyByIdProductId($stockAlerts);
        $productLevelQuantities = $this->getProductLevelQuantities($products);

        foreach ($productViews as $productView) {
            $productId = $productView->getId();
            $productView->set('has_stock_alert', array_key_exists($productId, $stockAlerts));
            $productView->set('sy_quantity', $productLevelQuantities[$productId][$productView->get('unit')] ?? 0);
        }
    }

    protected function keyByIdProductId(array $stockAlerts): array
    {
        $array = [];
        foreach ($stockAlerts as $stockAlert) {
            $array[$stockAlert->getProduct()->getId()] = $stockAlert->getId();
        }
        return $array;
    }

    protected function getProductLevelQuantities(array $products): array
    {
        /** @var InventoryLevelRepository $inventoryLevelRepository */
        $inventoryLevelRepository = $this->entityManager->getRepository(InventoryLevel::class);
        $productLevelQuantities = $inventoryLevelRepository->getQuantityForProductCollection($products);

        return $this->formatProductLevelQuantities($productLevelQuantities);
    }

    protected function formatProductLevelQuantities($inventoryLevelRepository): array
    {
        $formattedQuantities = [];

        foreach ($inventoryLevelRepository as $item) {
            $productId = $item['product_id'];
            $code = $item['code'];

            $formattedQuantities[$productId][$code] = (float) $item['quantity'];
        }

        return $formattedQuantities;
    }
}
