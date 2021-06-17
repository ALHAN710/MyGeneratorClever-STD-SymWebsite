<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Enterprise;
use App\Form\SmartModType;
use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminSiteType extends ApplicationType
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
                'currency',
                ChoiceType::class,
                [
                    //'required' => false,
                    'choices' => [
                        'XAF'   => 'XAF',
                        //'2'   => 2,

                    ],
                    'label'    => 'Currency'
                ]
            )
            ->add(
                'mainsInterruptDayLimit',
                NumberType::class,
                $this->getConfiguration("Daily Mains Interruption Limit ", "Please enter the limit...", [
                    'required' => false,
                    'attr' => [
                        'min' => 1
                    ]
                ])
            )
            ->add(
                'latitude',
                NumberType::class,
                $this->getConfiguration("Latitude", "Please enter the latitude...", [
                    'required' => false,
                    'attr' => [
                        //'min' => 1
                    ]
                ])
            )
            ->add(
                'longitude',
                NumberType::class,
                $this->getConfiguration("Longitude", "Please enter the longitude...", [
                    'required' => false,
                    'attr' => [
                        //'min' => 1
                    ]
                ])
            )
            ->add(
                'enterprise',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Enterprise::class,

                    // uses the User.username property as the visible option string
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                            //->select('c.name')
                            // ->Join('s.enterprise', 'e')
                            // ->where('e.id = :entId')
                            // ->setParameter('entId', $entId)
                        ;
                    },
                    'choice_label' => 'socialReason',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            // ->add(
            //     'fuelPrice',
            //     NumberType::class,
            //     $this->getConfiguration("Fuel Price", "Please enter the fuel price...", [
            //         'required' => false,
            //         'attr' => [
            //             //'min' => 1
            //         ]
            //     ])
            // )
            // ->add('powerSubscribed')
            // ->add('slug')
            // ->add('createdAt')
            // ->add('users')
        ;

        if ($options['isEdit']) {
            $builder
                ->add(
                    'smartMods',
                    CollectionType::class,
                    [
                        'entry_type'   => SmartModType::class,

                        'allow_add'    => true,
                        'allow_delete' => true,
                        'entry_options' => array(
                            'entId'   => $options['entId'],
                            'forSite' => $options['forSite'],
                        ),
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Site::class,
            'isEdit' => false,
            'forSite' => true,
            'entId' => 0
        ]);
    }
}
