<?php

namespace ArusEntityAlertBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArusEntityAlertEditType extends AbstractType
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
            ->add( 'descr', TextareaType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'level', ChoiceType::class, ['choices'=>$this->options['t_level'],'choice_attr'=>function($o,$k,$i){return ['class'=>'alert_level_'.$k];}] )
            ->add( 'status', ChoiceType::class, ['choices'=>$this->options['t_status'],'choice_attr'=>function($o,$k,$i){return ['class'=>'alert_status_'.$k];}] )
		;
    }
}
