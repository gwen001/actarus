<?php

namespace ArusProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArusProjectEditType extends AbstractType
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
            ->add( 'name', TextType::class, ['constraints'=>[new NotBlank()]] )
            ->add( 'handle', TextType::class, ['constraints'=>[new NotBlank()]] )
            ->add( 'status', ChoiceType::class, ['choices'=>$this->options['t_status'],'choice_attr'=>function($o,$k,$i){return ['class'=>'project_status_'.$k];}] )
        ;
    }
}
