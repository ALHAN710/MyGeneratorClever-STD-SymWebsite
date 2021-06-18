<?php

namespace App\Form;

use App\Entity\Enterprise;
//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AdminEnterpriseType extends ApplicationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'socialReason',
                TextType::class,
                $this->getConfiguration("Social Reason *", "Please enter the social reason...")
            )
            ->add(
                'niu',
                TextType::class,
                $this->getConfiguration("NIU", "Please enter the niu...", [
                    'required' => false,
                ])
            )
            ->add(
                'rccm',
                TextType::class,
                $this->getConfiguration("RCCM", "Please enter the rccm...", [
                    'required' => false,
                ])
            )
            ->add(
                'address',
                TextType::class,
                $this->getConfiguration("Address *", "Please enter the address...")
            )
            ->add(
                'phoneNumber',
                TextType::class,
                $this->getConfiguration("Phone Number *", "Please enter the phone number...")
            )
            ->add(
                'email',
                EmailType::class,
                $this->getConfiguration("Email *", "Please enter the Email address...")
            )
            ->add(
                'logo',
                FileType::class,
                $this->getConfiguration(
                    "Logo (IMG file)",
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
            ->add(
                'country',
                ChoiceType::class,
                [
                    'choices' => [
                        'Cameroon'      => 'Cameroon',
                    ],
                    'label'    => 'Country'
                ]
            )
            // ->add('createdAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Enterprise::class,
        ]);
    }
}
