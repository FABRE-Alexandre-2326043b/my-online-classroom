<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\CallbackTransformer;

class QuizQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => 'Intitulé de la question',
                'attr' => ['class' => 'form-control mb-2 fw-bold'],
                'row_attr' => ['class' => 'col-md-12 mb-3'],
            ])
            ->add('options', TextareaType::class, [
                'label' => 'Choix possibles (Une réponse par ligne)',
                'attr' => ['class' => 'form-control mb-2', 'rows' => 6],
                'row_attr' => ['class' => 'col-md-12 mb-3'],
                'help' => 'Écrivez chaque choix sur une nouvelle ligne.'
            ])
            ->add('correct_index', IntegerType::class, [
                'label' => 'Numéro de la bonne réponse (0 pour la 1ère ligne, 1 pour la 2ème...)',
                'attr' => ['class' => 'form-control', 'min' => 0],
                'row_attr' => ['class' => 'col-md-12'],
            ])
        ;

        $builder->get('options')->addModelTransformer(new CallbackTransformer(
            function ($optionsAsArray) {
                if (!$optionsAsArray) return '';
                return implode("\n", $optionsAsArray);
            },
            function ($optionsAsString) {
                return array_map('trim', explode("\n", $optionsAsString));
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}