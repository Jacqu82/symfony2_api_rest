<?php

namespace AppBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UpdateProgrammerType extends ProgrammerType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults([
            'is_edit' => true
        ]);
    }

    public function getName()
    {
        return 'update_programmer';
    }
}
