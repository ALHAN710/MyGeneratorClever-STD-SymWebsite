<?php

namespace App\Form;

use App\Entity\User;
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

class UserType extends ApplicationType
{
    private $entId;
    private $role = "ROLE_ADMIN";

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->entId = $options['entId'];
        if (!$options['forZone'] && !$options['forSite']) {
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
                                'CUSTOMER'  => 'ROLE_CUSTOMER',
                                'MANAGEMENT' => 'ROLE_MANAGER',
                                'NOC-SUPERVISOR' => 'ROLE_NOC_SUPERVISOR',
                                'ADMINISTRATOR' => 'ROLE_ADMIN',
                            ],
                        ],
                    ]
                )

                // ->add('createdAt')
                // ->add('countryCode')
                // ->add('password')
                // ->add('verificationCode')
                // ->add('verified')
                // ->add('avatar')
                // ->add('enterprise')
            ;
            if (!$options['isEdit']) {
                $builder
                    ->add(
                        'password',
                        PasswordType::class,
                        $this->getConfiguration("Password *", "Please enter the password...")
                    );
            }
        } else {
            $builder
                ->add(
                    'name',
                    TextType::class,
                    $this->getConfiguration("User Name", "Please enter the user name...", [
                        'required' => false,
                    ])
                )
                ->add(
                    'userNam',
                    EntityType::class,
                    [
                        // looks for choices from this entity
                        'class' => User::class,
                        'query_builder' => function (EntityRepository $er) {
                            $user_ = $er->createQueryBuilder('u')
                                ->innerJoin('u.enterprise', 'e')
                                ->where('e.id = :entId')
                                //->andWhere('u.roles = :role')
                                ->setParameters(array(
                                    'entId'    => $this->entId,
                                    //'role'  => $this->role,
                                ));
                            //->orderBy('u.username', 'ASC');
                            //dump($user_);
                            return $user_;
                        },
                        // uses the User.username property as the visible option string
                        'choice_label' => function ($user) {
                            return $user->getFirstName() . ' ' . $user->getLastName();
                        },

                        // used to render a select box, check boxes or radios
                        // 'multiple' => true,
                        // 'expanded' => true,
                        'label'    => 'User Name'
                    ]
                );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'entId'      => 0,
            'role'      => '',
            'forZone'    => false,
            'forSite'    => false,
            'isEdit'     => false,
        ]);
    }
}
