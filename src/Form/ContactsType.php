<?php

namespace App\Form;

use App\Entity\Contacts;
use App\Entity\Site;
use App\Form\ApplicationType;
//use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ContactsType extends ApplicationType
{
    private $entId;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId = $options['entId'];
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration("First Name *", "Please enter the first name...")
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration("Last Name *", "Please enter the last name...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email *", "Please enter the Email address...")
            )
            ->add(
                'countryCode',
                TextType::class,
                $this->getConfiguration("Country Code *", "Example : +237")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Phone Number *", "Please enter the phone number...")
            )
            ->add(
                'site',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Site::class,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('st')
                            ->innerJoin('st.enterprise', 'e')
                            ->where('e.id = :entId')
                            //->andWhere(':role in u.roles')
                            ->setParameters(array(
                                'entId'    => $this->entId,
                                //'role'  => $this->role,
                            ));
                        //->orderBy('u.sitename', 'ASC');
                    },
                    // uses the site.sitename property as the visible option string
                    'choice_label' => function ($site) {
                        return $site->getName();
                    },

                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                    'label'    => 'Site'
                ]
            )
            //->add('site')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contacts::class,
            'entId'      => 0,
        ]);
    }
}
