<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Controller\Frontend;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\LayoutBundle\Attribute\Layout;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Attribute\CsrfProtection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;
use Synolia\Bundle\StockAlertBundle\Form\Type\StockAlertType;
use Synolia\Bundle\StockAlertBundle\Handler\StockAlertHandler;

class StockAlertController extends AbstractController
{
    protected TranslatorInterface $translator;
    protected ObjectManager $manager;
    protected StockAlertHandler $handler;

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function setManager(ObjectManager $manager): void
    {
        $this->manager = $manager;
    }

    public function setHandler(StockAlertHandler $handler): void
    {
        $this->handler = $handler;
    }

    #[Route(path: '/form/{productId}', name: 'synolia_frontend_stock_alert_form', methods: ['POST']) ]
    public function form(Request $request, int $productId): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $product = $this->manager->getRepository(Product::class)->find($productId);

        $formEntity = new StockAlert();
        $form = $this->createForm(
            StockAlertType::class,
            $formEntity,
            [
                'product' => $productId,
                'action' => $this->generateUrl('synolia_frontend_stock_alert_form', ['productId' => $productId]),
            ],
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->handler->create($product);

                $this->addFlash(
                    'success',
                    $this->translator->trans('synolia.stockalert.alert.register.confirmation_message')
                );

                return $this->redirectToRoute('oro_product_frontend_product_view', ['id' => $productId]);
            } catch (\Exception $exception) {
                $this->addFlash(
                    'error',
                    $this->translator->trans('synolia.stockalert.alert.register.error_message')
                );
            }
        }

        return $this->render('@SynoliaStockAlert/layouts/default/synolia_stock_alert_frontend_index/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/', name: 'synolia_stock_alert_frontend_index')]
    #[Layout(vars: ['entity_class','organization_id','customer_user_id'])]
    public function indexAction(): array
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var CustomerUser $user */
        $user = $this->getUser();

        return [
            'entity_class' => StockAlert::class,
            'organization_id' => $user->getOrganization()->getId(),
            'customer_user_id' => $user->getId(),
        ];
    }

    #[Route(path: '/create/{id}', name: 'synolia_stock_alert_create', requirements: ['id' => '\d+']) ]
    #[ParamConverter('product', class: Product::class, options: ['id' => 'id'])]
    public function createAction(Product $product): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        try {
            $stockAlert = $this->handler->create($product);

            if ($stockAlert) {
                return new JsonResponse([
                    'status' => 'success',
                    'message' => $this->translator->trans('synolia.stockalert.alert.register.confirmation_message'),
                    'stock' => $stockAlert->getId(),
                ]);
            }
        } catch (\Exception $exception) {
        }

        return new JsonResponse([
            'success' => false,
        ]);
    }

    #[Route(path: '/delete/{id}', name: 'synolia_stock_alert_delete', requirements: ['id' => '\d+'], methods: ['DELETE']) ]
    #[CsrfProtection()]
    #[ParamConverter('product', class: Product::class, options: ['id' => 'id'])]
    public function deleteAction(Product $product): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        try {
            $this->handler->deleteByProduct($product);

            return new JsonResponse([
                'status' => 'success',
                'message' => $this->translator->trans('synolia.stockalert.alert.unregister.confirmation_message'),
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'success' => false,
        ]);
    }
}
