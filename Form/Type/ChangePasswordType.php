<?php
namespace Puzzle\Admin\UserBundle\Form\Type;

use Puzzle\ConnectBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class ChangePasswordType extends AbstractType
{	
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('currentPassword', Type\PasswordType::class, [
						'required' => true,
						'mapped' => false,
						'translation_domain' => 'admin',
						'label' => 'form.label.user.currentPassword',
						'constraints' => [
								new NotBlank(),
								new UserPassword([
										'message' => 'user.currentPassword.invalid'
								])
						],
						'label_attr' => [
								'class' => 'uk-form-label'
						],
						'attr' => [
								'class' => 'md-input',
								'placeholder' => 'form.placeholder.user.currentPassword'
						]
					])
				->add('plainPassword', Type\RepeatedType::class, [
						'required' => true,
						'type' => Type\PasswordType::class,
						'options' => [
								'translation_domain' => 'app'
						],
						'first_options' => [
								'label' => 'form.label.user.password',
								'label_attr' => [
										'class' => 'uk-form-label'
								],
								'attr' => [
										'class' => 'md-input',
										'placeholder' => 'form.placeholder.user.password'
								]
						],
						'second_options' => [
								'label' => 'form.label.user.password_confirmation',
								'label_attr' => [
										'class' => 'uk-form-label'
								],
								'attr' => [
										'class' => 'md-input',
										'placeholder' => 'form.placeholder.user.password_confirmation'
								]
						]
					]);
	}
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
				'data_class'            => User::class,
				'csrf_token_id' 		=> 'change_password',
				'validation_groups' 	=> ['ChangePassword'],
				'translation_domain' 	=> 'app'
		]);
	}
	
	public function getBlockPrefix() {
		return 'app_change_password';
	}
}
