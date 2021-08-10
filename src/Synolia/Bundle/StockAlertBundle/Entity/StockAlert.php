<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerAwareTrait;
use Oro\Bundle\CustomerBundle\Entity\Ownership\FrontendCustomerUserAwareTrait;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\OrganizationBundle\Entity\OrganizationAwareInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\UserBundle\Entity\Ownership\UserAwareTrait;
use Synolia\Bundle\StockAlertBundle\Model\ExtendStockAlert;

/**
 * @ORM\Table(name="synolia_stock_alert")
 * @ORM\Entity(repositoryClass="Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository")
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id",
 *              "frontend_owner_type"="FRONTEND_USER",
 *              "frontend_owner_field_name"="customerUser",
 *              "frontend_owner_column_name"="customer_user_id",
 *              "frontend_customer_field_name"="customer",
 *              "frontend_customer_column_name"="customer_id"
 *          },
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "entity"={
 *              "icon"="fa-shopping-cart"
 *          }
 *      }
 * )
 */
class StockAlert extends ExtendStockAlert implements
    OrganizationAwareInterface,
    CustomerOwnerAwareInterface,
    DatesAwareInterface
{
    use DatesAwareTrait;
    use UserAwareTrait;
    use FrontendCustomerAwareTrait;
    use FrontendCustomerUserAwareTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ProductBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $product;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiration_date", type="datetime", nullable=true)
     */
    protected $expirationDate;


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

    public function getExpirationDate(): DateTime
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(DateTime $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }
}
