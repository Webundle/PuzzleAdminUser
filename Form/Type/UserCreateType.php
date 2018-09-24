<?php
namespace Puzzle\Admin\UserBundle\Form\Type;

use Puzzle\Admin\UserBundle\Form\Model\AbstractUserType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 */
class UserCreateType extends AbstractUserType
{
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		
		$resolver->setDefault('csrf_token_id', 'user_create');
		$resolver->setDefault('validation_groups', ['Create']);
	}
	
	public function getBlockPrefix() {
		return 'admin_user_create';
	}
}
