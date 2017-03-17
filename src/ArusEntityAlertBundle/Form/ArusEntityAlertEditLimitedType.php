<?php

namespace ArusEntityAlertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArusEntityAlertEditLimitedType extends AbstractType
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
			->add( 'entity_id', HiddenType::class )
			->add( 'level', ChoiceType::class, ['data'=>'0','choices'=>$this->options['t_level'],'multiple'=>false,'expanded'=>true] )
			->add( 'descr', TextareaType::class, ['constraints'=>[new NotBlank()]] )
		;
    }
}
