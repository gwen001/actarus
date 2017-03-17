<?php

namespace ArusTaskCallbackBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;

use ArusTaskBundle\Repository\ArusTaskRepository;


class ArusTaskCallbackType extends AbstractType
{
	public function __construct( $options=null ) {
		if( is_array($options) && isset($options['t_task']) ) {
			foreach ($options['t_task'] as &$t) {
				$t = $t['name'];
			}
			foreach ($options['task_callback'] as $k=>&$c) {
				$c = $k;
			}
		}
		$this->options = $options;
	}

	
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add( 'task', 'entity', array(
					'empty_data'  => null,
					'empty_value' => '- - -',
					'constraints' => [new NotBlank()],
					'property' => 'name',
					'class' => 'ArusTaskBundle\\Entity\\ArusTask',
					'query_builder' => function(ArusTaskRepository $er){
						return $er->createQueryBuilder('t')->orderBy('t.name', 'ASC');
					})
			)
			->add( 'regex', TextType::class, ['constraints'=>[new NotBlank()]] )
			->add( 'action', ChoiceType::class, ['choices'=>$this->options['task_callback'],'choices_as_values'=>true,'empty_data' =>null,'empty_value'=>'- - -'] )
			->add( 'param_text', TextType::class, ['required'=>false] )
			->add( 'param_task', ChoiceType::class, ['choices'=>$this->options['t_task'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
			->add( 'param_alert_level', ChoiceType::class, ['choices'=>$this->options['alert_level'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
			->add( 'param_technology', ChoiceType::class, ['choices'=>$this->options['t_technology'],'required'=>false,'empty_data' =>null,'empty_value'=>'- - -'] )
		;
    }
}
