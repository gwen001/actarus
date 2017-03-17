<?php

namespace ArusDomainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArusDomainQuickEditType extends AbstractType
{
    public function __construct( $options=null ) {
        $this->options = $options;
    }


	/**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->setMethod( 'POST' )
			->add( 'status', ChoiceType::class, ['choices'=>$this->options['t_status'],'multiple'=>false,'expanded'=>true] )
        ;
    }
}
