<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertController extends AbstractController
{
    /**
     * @Route("/", name="synolia_stock_alert_index")
     * @Template
     */
    public function indexAction(): array
    {
        return [
            'entity_class' => StockAlert::class,
        ];
    }
}
