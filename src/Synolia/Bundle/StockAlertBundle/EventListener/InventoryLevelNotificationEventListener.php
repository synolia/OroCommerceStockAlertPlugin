<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\Bundle\StockAlertBundle\Async\Topic\StockAlertNotificationTopic;
use Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;
use Synolia\Bundle\StockAlertBundle\Handler\StockAlertHandler;

class InventoryLevelNotificationEventListener
{
    public array $stockAlerts = [];

    public function __construct(
        protected StockAlertRepository $stockAlertRepository,
        protected StockAlertHandler $stockAlertHandler,
        protected MessageProducerInterface $messageProducer,
        protected TranslatorInterface $translator,
    ) {
    }

    /**
     * @throws Exception
     */
    public function preUpdate(InventoryLevel $inventoryLevel, PreUpdateEventArgs $args): void
    {
        if (!$args->getObject() instanceof InventoryLevel) {
            return;
        }
        if (!$this->inventoryHasNewStock($args)) {
            return;
        }
        /** @var StockAlert[] $alerts */
        $alerts = $this->stockAlertRepository->findUnexpiredByProduct($inventoryLevel->getProduct());
        foreach ($alerts as $alert) {
            $this->stockAlerts[] = $alert;
            $this->sendStockAlertMessage(
                $inventoryLevel->getProduct(),
                $alert->getCustomerUser()
            );
        }
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function postUpdate(): void
    {
        $this->stockAlertHandler->deleteStockAlerts($this->stockAlerts);
    }

    protected function inventoryHasNewStock(PreUpdateEventArgs $args): bool
    {
        if (!$args->hasChangedField('quantity')) {
            return false;
        }

        $oldQuantity = (float) $args->getOldValue('quantity');
        $newQuantity = (float) $args->getNewValue('quantity');

        return $oldQuantity <= 0 && $newQuantity > 0;
    }

    /**
     * @throws Exception
     */
    protected function sendStockAlertMessage(
        Product $product,
        CustomerUser $customerUser
    ): void {
        $this->messageProducer->send(StockAlertNotificationTopic::getName(), [
            'customerUserId' => $customerUser->getId(),
            'productName' => $product->getName()->getString(),
            'productSKU' => $product->getSku(),
        ]);
    }
}
