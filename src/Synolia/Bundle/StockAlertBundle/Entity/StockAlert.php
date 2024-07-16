<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerAwareTrait;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerUserAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\UserBundle\Entity\Ownership\UserAwareTrait;
use Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository;

#[ORM\Entity(repositoryClass: StockAlertRepository::class)]
#[ORM\Table(name: 'synolia_stock_alert')]
#[Config(
    defaultValues: [
        'entity' => ['icon' => 'fa-shopping-cart'],
        'dataaudit' => ['auditable' => true],
        'ownership' => [
            'owner_type' => 'USER',
            'owner_field_name' => 'owner',
            'owner_column_name' => 'user_owner_id',
            'organization_field_name' => 'organization',
            'organization_column_name' => 'organization_id',
            'frontend_owner_type' => 'FRONTEND_USER',
            'frontend_owner_field_name' => 'customerUser',
            'frontend_owner_column_name' => 'customer_user_id',
            'frontend_customer_field_name' => 'customer',
            'frontend_customer_column_name' => 'customer_id',
        ],
    ]
)]
class StockAlert implements
    ExtendEntityInterface,
    OrganizationAwareInterface,
    CustomerOwnerAwareInterface,
    DatesAwareInterface
{
    use DatesAwareTrait;
    use ExtendEntityTrait;
    use FrontendCustomerAwareTrait;
    use FrontendCustomerUserAwareTrait;
    use UserAwareTrait;

    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Product $product;

    #[ORM\Column(name: 'expiration_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTime $expirationDate;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }
}
