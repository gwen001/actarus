<?php

namespace RequeteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add( 'id', TextType::class, ['required'=>false] )
            ->add( 'project_id', ChoiceType::class, ['choices'=>$this->options['t_project'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
			->add( 'url', TextType::class, ['required'=>false] )
			//->add( 'host', TextType::class )
			//->add( 'port', TextType::class )
            //->add( 'protocol', ChoiceType::class, ['choices'=>$this->options['t_protocol'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
            ->add( 'method', ChoiceType::class, ['choices'=>$this->options['t_method'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
			//->add( 'path', TextType::class, ['required'=>false] )
			//->add( 'query', TextType::class, ['required'=>false] )
			->add( 'data', TextType::class, ['required'=>false] )
			->add( 'header', TextType::class, ['required'=>false] )
			->add( 'cookie', TextType::class, ['required'=>false] )
			->add( 'min_created_at', TextType::class, ['required'=>false] )
			->add( 'max_created_at', TextType::class, ['required'=>false] )
		;
    }
}
