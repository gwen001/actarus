<?php

namespace ArusProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ArusProjectAddType extends AbstractType
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
			->add( 'recon', CheckboxType::class, ['required'=>false] )
        ;
    }
}
