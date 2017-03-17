<?php

namespace ArusDomainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusProjectBundle\Repository\ArusProjectRepository;


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
			->add( 'page', HiddenType::class )
            ->add( 'id', TextType::class, ['required'=>false] )
			->add( 'name', TextType::class, ['required'=>false] )
			->add( 'project', 'entity', array(
					'required' => false,
					'empty_data'  => null,
					'empty_value' => '- - -',
					'property' => 'name',
					'class' => 'ArusProjectBundle\\Entity\\ArusProject',
					'query_builder' => function(ArusProjectRepository $er){
						return $er->createQueryBuilder('p')->orderBy('p.name', 'ASC');
					})
			)
            ->add( 'status', ChoiceType::class, ['choices'=>$this->options['t_status'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -','choice_attr'=>function($o,$k,$i){return ['class'=>'domain_status_'.$k];}] )
			->add( 'min_created_at', TextType::class, ['required'=>false] )
			->add( 'max_created_at', TextType::class, ['required'=>false] )
		;
    }
}
