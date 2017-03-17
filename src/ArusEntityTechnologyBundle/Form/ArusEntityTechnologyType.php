<?php

namespace ArusEntityTechnologyBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusTechnologyBundle\Repository\ArusTechnologyRepository;


class ArusEntityTechnologyType extends AbstractType
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
			->add( 'technology', 'entity', array(
					'empty_data'  => null,
					'empty_value' => '- - -',
					'constraints' => [new NotBlank()],
					'property' => 'name',
					'class' => 'ArusTechnologyBundle\\Entity\\ArusTechnology',
					'query_builder' => function(ArusTechnologyRepository $er){
						return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
					})
			)
			->add( 'version', TextType::class, ['required'=>false] )
		;
    }
}
