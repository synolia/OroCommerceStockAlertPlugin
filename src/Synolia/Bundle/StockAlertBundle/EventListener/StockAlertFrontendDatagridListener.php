<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertFrontendDatagridListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenAccessorInterface $tokenAccessor
    ) {
    }

    public function onResultAfter(SearchResultAfter $event): void
    {
        /** @var ResultRecord[] $records */
        $records = $event->getRecords();
        if (\count($records) === 0) {
            return;
        }

        $productIds = [];
        foreach ($records as $record) {
            $productIds[] = $record->getValue('id');
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

        foreach ($records as $record) {
            $productId = $record->getValue('id');
            $record->addData(['has_stock_alert' => array_key_exists($productId, $stockAlerts)]);
            $record->addData(['quantity' => $productLevelQuantities[$productId][$record->getValue('unit')] ?? 0]);
        }
    }

    public function onBuildBefore(BuildBefore $event): void
    {
        $config = $event->getConfig();
        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                'has_stock_alert' => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_BOOLEAN
                ],
                'quantity' => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_DECIMAL
                ]
            ]
        );
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
