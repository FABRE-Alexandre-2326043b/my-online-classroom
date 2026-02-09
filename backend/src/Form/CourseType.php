<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du cours',
                'attr' => ['class' => 'form-control mb-3']
            ])
            ->add('pdfFile', FileType::class, [
                'label' => 'Support de cours (PDF)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control mb-3'],
                'constraints' => [
                    new File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide',
                    ])
                ],
            ])
            ->add('videoFile', FileType::class, [
                'label' => 'Vidéo du cours (MP4)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '200M',
                        'mimeTypes' => ['video/mp4'],
                        'mimeTypesMessage' => 'Veuillez uploader une vidéo MP4 valide',
                    ])
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description (laisser vide si générée par IA)',
                'required' => false,
                'attr' => ['class' => 'form-control mb-3', 'rows' => 5]
            ])
            ->add('generate_description', CheckboxType::class, [
                'label' => '✨ Générer le résumé via IA (nécessite le PDF)',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-check-input ms-2']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer le cours',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}