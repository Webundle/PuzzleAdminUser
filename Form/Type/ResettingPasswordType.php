<?php
namespace Puzzle\Admin\UserBundle\Form\Type;

use Puzzle\ConnectBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class ResettingPasswordType extends AbstractType
{	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('plainPassword', Type\RepeatedType::class, [
						'required' => true,
						'type' => Type\PasswordType::class,
						'options' => [
								'translation_domain' => 'admin'
						],
						'first_options' => [
								'label' => 'form.label.user.password',
								'label_attr' => [
										'class' => 'uk-form-label'
								],
								'attr' => [
										'class' => 'md-input pword',
										'placeholder' => 'form.placeholder.user.password'
								]
						],
						'second_options' => [
								'label' => 'form.label.user.password_confirmation',
								'label_attr' => [
										'class' => 'uk-form-label'
								],
								'attr' => [
										'class' => 'md-input pword',
										'placeholder' => 'form.placeholder.user.password_confirmation'
								]
						]
					]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
				'data_class' 			=> User::class,
				'csrf_token_id' 		=> 'reset_password',
				'validation_groups' 	=> ['ResetPassword'],
				'translation_domain' 	=> 'admin'
		]);
	}
	
	public function getBlockPrefix() {
		return 'admin_change_password';
	}
}
