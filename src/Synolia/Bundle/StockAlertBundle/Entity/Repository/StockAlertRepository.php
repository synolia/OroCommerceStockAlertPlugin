<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Entity\Repository;

use Oro\Bundle\ProductBundle\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
