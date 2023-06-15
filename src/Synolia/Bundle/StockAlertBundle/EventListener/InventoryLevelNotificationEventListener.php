<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Synolia\Bundle\StockAlertBundle\Async\Topic\StockAlertNotificationTopic;
use Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository;
use Synolia\Bundle\StockAlertBundle\Handler\StockAlertHandler;

class InventoryLevelNotificationEventListener
{
    public $stockAlerts = [];

    public function __construct(
        protected StockAlertRepository $stockAlertRepository,
        protected StockAlertHandler $stockAlertHandler,
        protected MessageProducerInterface $messageProducer
    ) {
    }

    /**
     * @throws Exception
     */
    public function preUpdate(InventoryLevel $inventoryLevel, LifecycleEventArgs $args): void
    {
        if (!$args->getEntity() instanceof InventoryLevel) {
            return;
        }
        if (!$this->inventoryHasNewStock($args)) {
            return;
        }
        $alerts = $this->stockAlertRepository->findUnexpiredByProduct($inventoryLevel->getProduct());
        foreach ($alerts as $alert) {
            $this->stockAlerts[] = $alert;
            $this->sendStockAlertMessage($alert->getCustomerUser(), $inventoryLevel->getProduct());
        }
    }

    public function postUpdate(): void
    {
        $this->stockAlertHandler->deleteStockAlerts($this->stockAlerts);
    }

    protected function inventoryHasNewStock(LifecycleEventArgs $args): bool
    {
        /** @var PreUpdateEventArgs $args */
        $oldQuantity = floatval($args->getOldValue('quantity'));
        /** @var PreUpdateEventArgs $args */
        $newQuantity = floatval($args->getNewValue('quantity'));
        return $oldQuantity <= 0 && $newQuantity > 0;
    }

    /**
     * @throws Exception
     */
    protected function sendStockAlertMessage(CustomerUser $customerUser, Product $product): void
    {
        $this->messageProducer->send(StockAlertNotificationTopic::getName(), [
            'customerEmail' => $customerUser->getEmail(),
            'customerFullName' => $customerUser->getFullName(),
            'productName' => $product->getName()->getString(),
            'productSKU' => $product->getSku()
        ]);
    }
}
