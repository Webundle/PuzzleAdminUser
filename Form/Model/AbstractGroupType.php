<?php

namespace Puzzle\Admin\UserBundle\Form\Model;

use Puzzle\ConnectBundle\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Puzzle\ConnectBundle\Entity\User;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class AbstractGroupType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.group.name',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('description', TextareaType::class, [
                'translation_domain' => 'admin',
                'label' => 'user.label.group.description',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'cols' => 10
                ],
                'required' => false
            ])
            ->add('users', EntityType::class, array(
                'translation_domain' => 'admin',
                'label' => 'user.label.group.users',
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'attr' => [
                    'class' => 'select'
                ],
                'class' => User::class,
                'choice_label' => 'fullName',
                'multiple' => true,
                'required' => false
            ))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' 			=> Group::class,
            'translation_domain' 	=> 'admin'
        ]);
    }
}
