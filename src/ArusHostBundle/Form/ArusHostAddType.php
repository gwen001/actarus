<?php

namespace ArusHostBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusProjectBundle\Repository\ArusProjectRepository;


class ArusHostAddType extends AbstractType
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
			->add('project', 'entity', array(
					'empty_data'  => null,
					'empty_value' => '- - -',
					'constraints' => [new NotBlank()],
					'property' => 'name',
					'class' => 'ArusProjectBundle\\Entity\\ArusProject',
					'query_builder' => function(ArusProjectRepository $er){
						return $er->createQueryBuilder('p')->orderBy('p.name', 'ASC');
					})
			)
			->add( 'domain', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'name', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'recon', CheckboxType::class, ['required'=>false] )
		;
    }
}
