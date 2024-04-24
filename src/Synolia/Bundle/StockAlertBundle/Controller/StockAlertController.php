<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertController extends AbstractController
{
    #[Route(path: '/', name: 'synolia_stock_alert_index')]
    #[Template('@SynoliaStockAlert/StockAlert/index.html.twig')]
    public function indexAction(): array
    {
        return [
            'entity_class' => StockAlert::class,
        ];
    }
}
