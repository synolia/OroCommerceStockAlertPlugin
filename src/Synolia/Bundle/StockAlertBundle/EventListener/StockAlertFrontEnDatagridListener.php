<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Property\PropertyInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SearchBundle\Datagrid\Event\SearchResultAfter;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertFrontEnDatagridListener
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onResultAfter(SearchResultAfter $event)
    {
        /** @var ResultRecord[] $records */
        $records = $event->getRecords();
        if (\count($records) === 0) {
            return;
        }

        $productsIds = [];
        foreach ($records as $record) {
            $productsIds[] = $record->getValue('id');
        }

        $products = $this->entityManager
            ->getRepository(Product::class)
            ->findBy(['id' => $productsIds]);
        $stockAlerts = $this->entityManager
            ->getRepository(StockAlert::class)
            ->findBy(['product' => $products]);
        $stockAlerts = $this->keyByIdProductId($stockAlerts);

        foreach ($records as $record) {
            $productId = $record->getValue('id');
            if (array_key_exists($productId, $stockAlerts)) {
                $record->addData(['has_stock_alert' => true]);
            } else {
                $record->addData(['stock_alert' => false]);
            }
        }
    }

    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $config->offsetAddToArrayByPath(
            '[properties]',
            [
                'has_stock_alert' => [
                    'type' => 'field',
                    'frontend_type' => PropertyInterface::TYPE_BOOLEAN
                ]
            ]
        );
    }

    public function keyByIdProductId(array $stockAlerts): array
    {
        $array = [];
        foreach ($stockAlerts as $stockAlert) {
            $array[$stockAlert->getProduct()->getId()] = $stockAlert->getId();
        }
        return $array;
    }
}
