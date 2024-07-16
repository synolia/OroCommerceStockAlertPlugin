<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Layout\DataProvider;

use Oro\Bundle\InventoryBundle\Provider\InventoryQuantityProviderInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;

class InventoryQuantityDataProvider
{

    public function __construct(
        protected InventoryQuantityProviderInterface $inventoryQuantityProvider
    ) {
    }

    public function getAvailableQuantity(Product $product): int
    {
        $unit = $product->getPrimaryUnitPrecision()->getUnit();

        if (!$unit instanceof ProductUnit) {
            return 0;
        }

        return $this->inventoryQuantityProvider->getAvailableQuantity($product, $unit);
    }
}
