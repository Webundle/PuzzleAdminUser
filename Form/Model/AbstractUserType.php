<?php

namespace Puzzle\Admin\UserBundle\Form\Model;

use Puzzle\ConnectBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AbstractUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.firstName',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control',
                    'placeholder'   => 'user.label.user.firstName'
                ],
            ])
            ->add('lastName', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.lastName',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control',
                    'placeholder'   => 'user.label.user.lastName'
                ],
            ])
            ->add('email', EmailType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.email',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control',
                    'placeholder'   => 'user.label.user.email'
                ],
            ])
            ->add('username', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.username',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control',
                    'placeholder'   => 'user.label.user.username'
                ],
            ])
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent correspondre',
                'options' => ['required' => false],
                'first_options'  => [
                    'translation_domain' => 'admin',
                    'label' => 'user.label.user.password',
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'attr' => [
                        'class'         => 'form-control',
                        'placeholder'   => 'user.label.user.password'
                    ]
                ],
                'second_options'  => [
                    'translation_domain' => 'admin',
                    'label' => 'user.label.user.password_repeated',
                    'label_attr' => [
                        'class' => 'form-label'
                    ],
                    'attr' => [
                        'class'         => 'form-control',
                        'placeholder'   => 'user.label.user.password_repeated'
                    ]
                ],
                'required' => false
            ))
            ->add('credentialsExpiresAt', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.credentialsExpiresAt',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control pickadate',
                    'placeholder'   => 'user.label.user.credentialsExpiresAt'
                ],
                'required' => false
            ])
            ->add('accountExpiresAt', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.user.accountExpiresAt',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class'         => 'form-control pickadate',
                    'placeholder'   => 'user.label.user.credentialsExpiresAt'
                ],
                'required' => false
            ])
            ->add('enabled', CheckboxType::class, array(
                'translation_domain' => 'admin',
                'label' => 'user.label.user.enabled',
                'attr' => [
                    'class' => 'switchery'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => false,
            ))
            ->add('locked', CheckboxType::class, array(
                'translation_domain' => 'admin',
                'label' => 'user.label.user.locked',
                'attr' => [
                    'class' => 'switchery'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'required' => false,
            ))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' 			=> User::class,
            'translation_domain' 	=> 'admin'
        ]);
    }
}
