<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Twig;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ProductBundle\Entity\Product;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;
use Synolia\Bundle\StockAlertBundle\Layout\DataProvider\InventoryQuantityDataProvider;
use Synolia\Bundle\StockAlertBundle\Layout\DataProvider\StockAlertDataProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QuantityExtension extends AbstractExtension
{
    /**
     * @var InventoryQuantityDataProvider
     */
    protected $inventoryQuantityDataProvider;
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var StockAlertDataProvider
     */
    protected $stockAlertDataProvider;

    public function __construct(
        InventoryQuantityDataProvider $inventoryQuantityDataProvider,
        EntityManager $entityManager,
        StockAlertDataProvider $stockAlertDataProvider
    ) {
        $this->inventoryQuantityDataProvider = $inventoryQuantityDataProvider;
        $this->entityManager = $entityManager;
        $this->stockAlertDataProvider = $stockAlertDataProvider;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'synolia_product_quantity',
                [$this, 'getProductQuantity']
            ),
            new TwigFunction(
                'synolia_product_has_stock_alert',
                [$this, 'productHasStockAlert']
            )
        ];
    }

    public function getProductQuantity($product): int
    {
        $product = $this->getProductObjec($product);
        return $this->inventoryQuantityDataProvider->getAvailableQuantity($product);
    }

    public function productHasStockAlert($product): bool
    {
        $product = $this->getProductObjec($product);
        $stockAlert = $this->stockAlertDataProvider->getStockAlertForProduct($product);
        if ($stockAlert instanceof StockAlert) {
            return true;
        }
        return false;
    }

    protected function getProductObjec($product) : Product
    {
        if ($product instanceof  Product) {
            return $product;
        }
        return $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $product['id']]);
    }
}
