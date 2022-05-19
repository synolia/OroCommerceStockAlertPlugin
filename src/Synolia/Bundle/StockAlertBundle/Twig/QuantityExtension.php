<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Twig;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Model\ProductView;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;
use Synolia\Bundle\StockAlertBundle\Layout\DataProvider\InventoryQuantityDataProvider;
use Synolia\Bundle\StockAlertBundle\Layout\DataProvider\StockAlertDataProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class QuantityExtension extends AbstractExtension
{
    protected InventoryQuantityDataProvider $inventoryQuantityDataProvider;
    protected EntityManager $entityManager;
    protected StockAlertDataProvider $stockAlertDataProvider;

    public function __construct(
        InventoryQuantityDataProvider $inventoryQuantityDataProvider,
        EntityManager $entityManager,
        StockAlertDataProvider $stockAlertDataProvider
    ) {
        $this->inventoryQuantityDataProvider = $inventoryQuantityDataProvider;
        $this->entityManager = $entityManager;
        $this->stockAlertDataProvider = $stockAlertDataProvider;
    }

    public function getFunctions(): array
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

    public function getProductQuantity(mixed $product): int
    {
        $product = $this->getProductObj($product);
        return $this->inventoryQuantityDataProvider->getAvailableQuantity($product);
    }

    public function productHasStockAlert(mixed $product): bool
    {
        $product = $this->getProductObj($product);
        $stockAlert = $this->stockAlertDataProvider->getStockAlertForProduct($product);
        if ($stockAlert instanceof StockAlert) {
            return true;
        }
        return false;
    }

    protected function getProductObj(mixed $product): Product
    {
        if ($product instanceof ProductView) {
            return $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $product->getId()]);
        }
        if (is_array($product)) {
            return $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $product['id']]);
        }
        return $product;
    }
}
