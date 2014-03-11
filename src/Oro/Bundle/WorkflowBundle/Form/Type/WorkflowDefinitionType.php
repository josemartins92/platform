<?php

namespace Oro\Bundle\WorkflowBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkflowDefinitionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'label',
                'text',
                array(
                    'label' => 'oro.workflow.workflowdefinition.name.label',
                    'required' => true,
                    'constraints' => array(array('NotBlank' => null)),
                )
            )
            ->add(
                'related_entity',
                'oro_workflow_applicable_entities',
                array(
                    'label' => 'oro.workflow.workflowdefinition.related_entity.label',
                    'required' => true,
                    'constraints' => array(array('NotBlank' => null)),
                )
            )
            ->add(
                'steps_display_ordered',
                'checkbox',
                array(
                    'label' => 'oro.workflow.workflowdefinition.steps_display_ordered.label',
                    'required' => false,
                )
            )
            ->add(
                'transition_prototype_icon',
                'oro_icon_select',
                array(
                    'label' => 'Button icon',
                    'mapped' => false
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\WorkflowBundle\Entity\WorkflowDefinition'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_workflow_definition';
    }
}
