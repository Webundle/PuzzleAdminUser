<?php

namespace Puzzle\Admin\UserBundle\Form\Type;

use Puzzle\Admin\UserBundle\Form\Model\AbstractGroupType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * 
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 * 
 */
class GroupCreateType extends AbstractGroupType
{
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'group_create');
        $resolver->setDefault('validation_groups', ['Create']);
    }
    
    public function getBlockPrefix() {
        return 'admin_group_create';
    }
}