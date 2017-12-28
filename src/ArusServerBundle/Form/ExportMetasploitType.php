<?php

namespace ArusServerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusServerBundle\Form\SearchType;


class ExportMetasploitType extends SearchType
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
		parent::buildForm( $builder, $options );
		
        $builder
			->add( 'export_full', ChoiceType::class, ['data'=>'full','choices'=>['full'=>'All results','page'=>'Only the current page'],'multiple'=>false,'expanded'=>true/*,'choices_as_values'=>true*/] )
			->setMethod( 'POST' );
		;
    }
}
