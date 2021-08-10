<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Synolia\Bundle\StockAlertBundle\Async\Topics;
use Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository;
use Synolia\Bundle\StockAlertBundle\Handler\StockAlertHandler;

class InventoryLevelNotificationEventListener
{
    /** @var StockAlertRepository */
    protected $stockAlertRepository;
    /** @var StockAlertHandler  */
    protected $stockAlertHandler;
    /* @var MessageProducerInterface */
    private $messageProducer;

    public $stockAlerts = [];

    public function __construct(
        StockAlertRepository $stockAlertRepository,
        StockAlertHandler $stockAlertHandler,
        MessageProducerInterface $messageProducer
    ) {
        $this->stockAlertRepository = $stockAlertRepository;
        $this->stockAlertHandler = $stockAlertHandler;
        $this->messageProducer = $messageProducer;
    }

    public function preUpdate(InventoryLevel $inventoryLevel, LifecycleEventArgs $args)
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

    public function postUpdate()
    {
        $this->stockAlertHandler->deleteStockAlerts($this->stockAlerts);
    }

    private function inventoryHasNewStock(LifecycleEventArgs $args): bool
    {
        /** @var PreUpdateEventArgs $args */
        $oldQuantity = floatval($args->getOldValue('quantity'));
        /** @var PreUpdateEventArgs $args */
        $newQuantity = floatval($args->getNewValue('quantity'));
        return $oldQuantity <= 0 && $newQuantity > 0;
    }

    private function sendStockAlertMessage(CustomerUser $customerUser, Product $product)
    {
        $this->messageProducer->send(Topics::SYNOLIA_STOCK_ALERT_MESSAGE, [
            'customerEmail' => $customerUser->getEmail(),
            'customerFullName' => $customerUser->getFullName(),
            'productName' => $product->getName()->getString(),
            'productSKU' => $product->getSku()
        ]);
    }
}
