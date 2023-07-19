<?php

declare(strict_types=1);

namespace Synolia\Bundle\StockAlertBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Synolia\Bundle\StockAlertBundle\Entity\StockAlert;

class StockAlertType extends AbstractType
{
    public const NAME = 'synolia_stock_alert_type';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'send',
                SubmitType::class,
                [
                    'label' => 'synolia.stockalert.alert.register.title',
                ],
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'product' => 0,
                'data_class' => StockAlert::class,
            ])
            ->setAllowedTypes('product', 'int')
        ;
    }

    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
