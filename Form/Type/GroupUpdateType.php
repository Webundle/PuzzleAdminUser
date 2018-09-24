<?php

namespace Puzzle\Admin\UserBundle\Form\Type;

use Puzzle\Admin\UserBundle\Form\Model\AbstractGroupType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 *
 * @author AGNES Gnagne Cédric <cecenho55@gmail.com>
 *
 */
class GroupUpdateType extends AbstractGroupType
{
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefault('csrf_token_id', 'group_update');
        $resolver->setDefault('validation_groups', ['Update']);
    }
    
    public function getBlockPrefix() {
        return 'admin_group_update';
    }
}