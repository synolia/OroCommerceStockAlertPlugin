<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Controller\Frontend;

use Oro\Bundle\LayoutBundle\Annotation\Layout;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Annotation\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;
use Synolia\Bundle\StockAlertBundle\Handler\StockAlertHandler;

class StockAlertController extends AbstractController
{
    /**
     * @Route("/", name="synolia_stock_alert_frontend_index")
     * @Layout(vars={"entity_class", "organization_id", "customer_user_id"})
     */
    public function indexAction(): array
    {
        $entityClass = StockAlert::class;
        return [
            'entity_class' => $entityClass,
            'organization_id' => $this->getUser()->getOrganization()->getId(),
            'customer_user_id' => $this->getUser()->getId(),
        ];
    }

    /**
     * @Route("/create/{id}", name="synolia_stock_alert_create", requirements={"id"="\d+"})
     * @ParamConverter("product", class="OroProductBundle:Product", options={"id" = "id"})
     */
    public function createAction(Product $product): JsonResponse
    {
        $handler = $this->get(StockAlertHandler::class);
        $stockAlert = $handler->create($product);
        if ($stockAlert) {
            return new JsonResponse([
                'status' => 'success',
                'message' => 'We will inform you as soon as this product is in stock',
                'stock' => $stockAlert->getId()
            ]);
        }
        return new JsonResponse([
            'success' => false
        ]);
    }

    /**
     * @Route(
     *     "/delete/{id}",
     *     name="synolia_stock_alert_delete",
     *     methods={"DELETE"},
     *     requirements={"id"="\d+"}
     * )
     * @CsrfProtection()
     * @ParamConverter("product", class="OroProductBundle:Product", options={"id" = "id"})
     */
    public function deleteAction(Product $product): JsonResponse
    {
        $handler = $this->get(StockAlertHandler::class);
        $handler->deleteByProduct($product);
        return new JsonResponse([
            'status' => 'success',
            'message' => 'The alert on that product stock level has been successfully deleted'
        ]);
    }
}
