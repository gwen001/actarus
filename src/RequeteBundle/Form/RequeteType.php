<?php

namespace RequeteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusProjectBundle\Repository\ArusProjectRepository;


class RequeteType extends AbstractType
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
            ->add( 'project', 'entity', array(
				'empty_data'  => null,
				'empty_value' => '- - -',
				'constraints' => [new NotBlank()),
				'property' => 'name',
				'class' => 'ArusProjectBundle\\Entity\\ArusProject',
				'query_builder' => function(ArusProjectRepository $er){
					return $er->createQueryBuilder('p')->orderBy('p.name', 'ASC');
				})
			)
			->add( 'url', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'host', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'port', TextType::class, ['empty_data'=>'80','constraints'=>[new NotBlank()]] )
            ->add( 'protocol', ChoiceType::class, ['choices'=>$this->options['t_protocol'],'constraints'=>[new NotBlank()]] )
            ->add( 'method', ChoiceType::class, ['choices'=>$this->options['t_method'],'constraints'=>[new NotBlank()]] )
			->add( 'path', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'query', TextType::class, ['required'=>false] )
			->add( 'header', TextareaType::class, ['required'=>false] )
			->add( 'cookie', TextareaType::class, ['required'=>false] )
			->add( 'data', TextareaType::class, ['required'=>false] )
		;
    }
}
