<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UserProfileType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstName',
                TextType::class,
                $this->getConfiguration("First Name *", "Please enter your first name...")
            )
            ->add(
                'lastName',
                TextType::class,
                $this->getConfiguration("Last Name *", "Please enter your last name...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email *", "Please enter your Email address...")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Phone Number *", "Please enter your phone number...")
            )
            //->add('roles')
            ->add(
                'avatar',
                FileType::class,
                $this->getConfiguration(
                    "User Avatar (IMG file)",
                    "",
                    [
                        // unmapped means that this field is not associated to any entity property
                        'mapped' => false,

                        // make it optional so you don't have to re-upload the IMG file
                        // every time you edit the Product details
                        'required' => false,

                        // unmapped fields can't define their validation using annotations
                        // in the associated entity, so you can use the PHP constraint classes
                        'constraints' => [
                            new File([
                                'maxSize' => '1024k',
                                'mimeTypes' => [
                                    'image/png',
                                    'image/jpeg',
                                ],
                                'mimeTypesMessage' => 'Please upload a valid Image format(jpeg, png)',
                            ])
                        ],
                    ]
                )
            )
            //->add('sites')
            //->add('countryCode')
            //->add('createdAt')
            //->add('password')
            //->add('verificationCode')
            //->add('verified')
            //->add('enterprise')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
