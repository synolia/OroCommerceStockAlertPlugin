<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\ProductBundle\Entity\Product;

class StockAlertRepository extends ServiceEntityRepository
{
    public function findUnexpiredByProduct(Product $product)
    {
        $queryBuilder = $this->createQueryBuilder('sa')
            ->where('sa.product = :product')
            ->andWhere('sa.expirationDate > CURRENT_DATE()')
            ->setParameter('product', $product);

        $query = $queryBuilder->getQuery();
        return $query->execute();
    }
}
