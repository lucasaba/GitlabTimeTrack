<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseProjectsType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($options['gitlabProjects'] as $project) {
            $builder->add('project_'.$project['gitlabId'], CheckboxType::class, array(
                'label' => $project['name'],
                'value' => $project['gitlabId'],
                'data' => ($project['associated'] ? true : false),
                'required' => false
            ));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('gitlabProjects');
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'app_choose_projects_type';
    }
}
