<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\SmartMod;
use App\Entity\Enterprise;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class SmartModType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['forZone'] && !$options['forSite']) {
            $builder
                ->add(
                    'name',
                    TextType::class,
                    $this->getConfiguration("Name *", "Please enter the name...")
                )
                ->add(
                    'moduleId',
                    TextType::class,
                    $this->getConfiguration("Module ID *", "Please enter the id of module...")
                )
                ->add(
                    'modType',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'choices' => [
                            ''                => '',
                            'FUEL'            => 'FUEL',
                            'GRID'            => 'GRID',
                            'DC'              => 'DC',
                            'Load Meter'      => 'Load Meter',
                            'Climate'         => 'Climate',
                            'Air Conditioner' => 'Air Conditioner',
                            //'2'   => 2,

                        ],
                        'label'    => 'Module Type *'
                    ]
                )
                ->add(
                    'fuelPrice',
                    NumberType::class,
                    $this->getConfiguration("Fuel Price", "Please enter the fuel price...", [
                        'required' => false,
                        'attr' => [
                            //'min' => 1
                        ]
                    ])
                )
                ->add(
                    'levelZone',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'choices' => [
                            ''  => null,
                            '1'   => 1,
                            '2'   => 2,

                        ],
                        'label'    => 'Level Zone'
                    ]
                )
                ->add(
                    'nbPhases',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'choices' => [
                            ''  => null,
                            '1'   => 1,
                            '3'   => 3,

                        ],
                        'label'    => 'Number of phasis'
                    ]
                )
                ->add(
                    'subType',
                    ChoiceType::class,
                    [
                        'required' => false,
                        'choices' => [
                            ''  => null,
                            'Production' => 'Production',
                            'Support'    => 'Support',
                            'Indoor' => 'Indoor',
                            'Outdoor'    => 'Outdoor',

                        ],
                        'label'    => 'SubType'
                    ]
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
                //->add('site')
                //->add('noDatetimeData')
                //->add('zones')
            ;
        } else {
            $builder
                ->add(
                    'modName',
                    TextType::class,
                    $this->getConfiguration("Module Name", "Please enter the Module name...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'smartModName',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => SmartMod::class,
                        'query_builder' => function (EntityRepository $er) {
                            return $er->createQueryBuilder('sm')
                                // ->innerJoin('sm.enterprise', 'e')
                                // ->where('e.id = :entId')
                                // //->andWhere(':role in u.roles')
                                // ->setParameters(array(
                                //     'entId'    => $this->entId,
                                //     //'role'  => $this->role,
                                // ))
                            ;
                            //->orderBy('u.username', 'ASC');
                        },
                        // uses the User.username property as the visible option string
                        'choice_label' => function ($smartMod) {
                            return $smartMod->getName() . '(' . $smartMod->getEnterprise()->getSocialReason() . ')';
                        },

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                        'label'    => 'Module Name'
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SmartMod::class,
            'entId'   => 0,
            'forZone'     => false,
            'forSite'     => false,
        ]);
    }
}
