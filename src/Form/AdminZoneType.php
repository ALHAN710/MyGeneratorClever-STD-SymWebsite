<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Zone;
use App\Form\SmartModType;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminZoneType extends ApplicationType
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
                        'Air Conditioner'      => 'Air Conditioner',
                        'AVR'                  => 'AVR',
                        'Genset'               => 'Genset',
                    ],
                    'label'    => 'Type'
                ]
            )
            ->add(
                'site',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Site::class,

                    // uses the User.username property as the visible option string
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            //->select('c.name')
                            //->Join('s.enterprise', 'e')
                            //->where('e.id = :entId')
                            //->setParameter('entId', $entId)
                        ;
                    },
                    'choice_label' => function ($site) {
                        return $site->getName() . '(' . $site->getEnterprise()->getSocialReason() . ')';
                    },
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            //->add('users')
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
                            'forZone' => $options['forZone'],
                        ),
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Zone::class,
            'isEdit' => false,
            'entId'   => 0,
            'forZone' => false,
        ]);
    }
}
