<?php

namespace App\Form;

use App\Entity\Zone;
use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class ZoneType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Name *", "Please enter the name...")
            )
            ->add(
                'powerSubscribed',
                NumberType::class,
                $this->getConfiguration("Power Subscribed (kVA)", "Please enter the power subscribed...", [
                    //'required' => false,
                    'attr' => [
                        'min' => 1
                    ]
                ])
            )
            ->add(
                'type',
                ChoiceType::class,
                [
                    'choices' => [
                        'PUE Calculation'      => 'PUE Calculation',
                        'No PUE Calculation'   => 'No PUE Calculation',

                    ],
                    'label'    => 'Type'
                ]
            )
            //->add('site')
            //->add('smartMods')
            //->add('users')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
        ]);
    }
}
