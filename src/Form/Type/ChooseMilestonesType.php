<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseMilestonesType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('milestone', ChoiceType::class, [
            'label' => "Milestones",
            'choices' => $this->getChoiceValues($options['milestones']),
            'required' => false
        ]);
    }

    private function getChoiceValues(array $milestones): array
    {
        $result = ["All" => null];
        foreach($milestones as $milestone) {
            $result += [$milestone->getTitle() => $milestone->getGitlabId()];
        }

        return $result;
    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('milestones');
    }
}
