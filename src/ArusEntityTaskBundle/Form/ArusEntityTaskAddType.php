<?php

namespace ArusEntityTaskBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusTaskBundle\Repository\ArusTaskRepository;


class ArusEntityTaskAddType extends AbstractType
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
			->add( 'command', TextareaType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'task', 'entity', array(
					'empty_data'  => null,
					'empty_value' => 'Choose the type of the task',
					'constraints' => [new NotBlank()],
					'property' => 'name',
					'class' => 'ArusTaskBundle\\Entity\\ArusTask',
					'query_builder' => function(ArusTaskRepository $er){
						return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
					})
			)
		;
    }
}
