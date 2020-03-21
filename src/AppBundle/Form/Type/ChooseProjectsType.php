<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseProjectsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('gitlabProjects');
    }

    public function getBlockPrefix()
    {
        return 'app_bundle_choose_projects_type';
    }
}
