<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Enterprise;
use App\Form\ApplicationType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AdminUserType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email *", "Please enter your Email address...")
            )
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
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Phone Number *", "Please enter the phone number...")
            )
            ->add(
                'roles',
                CollectionType::class,
                [

                    'entry_type'   => ChoiceType::class,
                    'entry_options'  => [
                        'label' => false,
                        'attr' => ['class' => 'form-control'],
                        'choices' => [
                            'MANAGEMENT' => 'ROLE_MANAGER',
                            'NOC-SUPERVISOR' => 'ROLE_NOC_SUPERVISOR',
                            'ADMINISTRATOR' => 'ROLE_ADMIN',
                        ],
                    ],
                ]
            )

            ->add(
                'enterprise',
                EntityType::class,
                [
                    // looks for choices from this entity
                    'class' => Enterprise::class,
                    'attr' => ['class' => 'form-control'],
                    // uses the User.username property as the visible option string
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('e')
                            //->select('c.name')
                            //->Join('s.enterprise', 'e')
                            //->where('e.id = :entId')
                            //->setParameter('entId', $entId)
                        ;
                    },
                    'choice_label' => 'socialReason',
                    // used to render a select box, check boxes or radios
                    // 'multiple' => true,
                    // 'expanded' => true,
                ]
            )
            // ->add('createdAt')
            // ->add('countryCode')
            // ->add('password')
            // ->add('verificationCode')
            // ->add('verified')
            // ->add('avatar')
        ;
        if (!$options['isEdit']) {
            $builder
                ->add(
                    'password',
                    PasswordType::class,
                    $this->getConfiguration("Password *", "Please enter the password...")
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'isEdit'     => false,
        ]);
    }
}
