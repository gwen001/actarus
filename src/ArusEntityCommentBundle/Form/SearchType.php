<?php

namespace ArusEntityCommentBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchType extends AbstractType
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
			->setMethod( 'GET' )
			->add( 'page', HiddenType::class)
            ->add( 'id', TextType::class, ['required'=>false] )
			->add( 'entity_type', ChoiceType::class, ['choices'=>$this->options['t_entity_type'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
            ->add( 'entity_id', TextType::class, ['required'=>false] )
			->add( 'comment', TextType::class, ['required'=>false] )
			->add( 'min_created_at', TextType::class, ['required'=>false] )
			->add( 'max_created_at', TextType::class, ['required'=>false] )
		;
    }
}
