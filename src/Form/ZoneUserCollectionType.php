<?php

namespace App\Form;

use App\Entity\Site;
use App\Entity\Zone;
use App\Form\UserType;
//use Symfony\Component\Form\AbstractType;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ZoneUserCollectionType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entId = $options['entId'];
        $builder
            ->add(
                'name',
                TextType::class,
                $this->getConfiguration("Name *", "Please enter the name...")
            )
            ->add(
                'users',
                CollectionType::class,
                [
                    'entry_type'   => UserType::class,

                    'allow_add'    => true,
                    'allow_delete' => true,
                    'entry_options' => array(

                        'entId'   => $options['entId'],
                        'forZone' => $options['forZone'],
                    ),
                ]
            )
            /*->add(
                'site',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Site::class,

                    // uses the User.username property as the visible option string
                    'query_builder' => function (EntityRepository $er) use ($entId) {
                        return $er->createQueryBuilder('s')
                            //->select('c.name')
                            ->Join('s.enterprise', 'e')
                            ->where('e.id = :entId')
                            ->setParameter('entId', $entId);
                    },
                    'choice_label' => 'name',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )*/
            //->add('name')
            //->add('powerSubscribed')
            //->add('type')
            //->add('smartMods')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'  => Zone::class,
            'entId'       => 0,
            'forZone'     => true,
        ]);
    }
}
