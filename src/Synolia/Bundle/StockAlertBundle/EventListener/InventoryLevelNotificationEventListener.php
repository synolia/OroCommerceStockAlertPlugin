<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityExtendBundle\PropertyAccess;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Oro\Component\MessageQueue\Transport\Exception\Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\Bundle\StockAlertBundle\Async\Topic\StockAlertNotificationTopic;
use Synolia\Bundle\StockAlertBundle\Entity\Repository\StockAlertRepository;
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
            $accessor = PropertyAccess::createPropertyAccessor();
            $this->sendStockAlertMessage(
                $inventoryLevel->getProduct(),
                $alert->getCustomerUser(),
                $accessor->getValue($alert, 'recipient_email')
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
    protected function sendStockAlertMessage(
        Product $product,
        ?CustomerUser $customerUser = null,
        ?string $recipientEmail = null
    ): void {
        $email = null;
        $fullName = $this->translator->trans('synolia.stockalert.customer.fullname');

        if ($customerUser instanceof CustomerUser) {
            $email = $customerUser->getEmail();
            $fullName = $customerUser->getFullName();
        } elseif (!empty($recipientEmail)) {
            $email = $recipientEmail;
        }

        if (!empty($email)) {
            $this->messageProducer->send(StockAlertNotificationTopic::getName(), [
                'customerEmail' => $email,
                'customerFullName' => $fullName,
                'productName' => $product->getName()->getString(),
                'productSKU' => $product->getSku(),
            ]);
        }
    }
}
