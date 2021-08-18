<?php

namespace App\Form;

use App\Entity\Site;
//use Symfony\Component\Form\AbstractType;
use App\Form\UserType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class SiteUserCollectionType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$entId = $options['entId'];
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Name *", "Please enter the name...")
            )
            /*->add(
                'mainsInterruptDayLimit',
                NumberType::class,
                $this->getConfiguration("Daily Mains Interruption Limit ", "Please enter the limit...", [
                    'required' => false,
                    'attr' => [
                        'min' => 1
                    ]
                ])
            )*/
            /*->add(
                'currency',
                ChoiceType::class,
                [
                    'choices' => [
                        'XAF'      => 'XAF',
                        //'No PUE Calculation'   => 'No PUE Calculation',

                    ],
                    'label'    => 'Currency'
                ]
            )*/
            ->add(
                'users',
                CollectionType::class,
                [
                    'entry_type'   => UserType::class,

                    'allow_add'    => true,
                    'allow_delete' => true,
                    'entry_options' => array(

                        'entId'   => $options['entId'],
                        'forSite' => $options['forSite'],
                    ),
                ]
            )
            // ->add('slug')
            // ->add('powerSubscribed')
            // ->add('createdAt')
            // ->add('latitude')
            // ->add('longitude')
            // ->add('enterprise')
            // ->add('tarification')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
            'entId'       => 0,
            'forSite'     => true,
        ]);
    }
}
