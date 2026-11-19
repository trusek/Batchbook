<?php

namespace App\Form;

use App\Entity\Batch;
use App\Entity\BatchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Batch>
 */
final class BatchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa',
            ])
            ->add('type', EnumType::class, [
                'label' => 'Typ',
                'class' => BatchType::class,
                'choice_label' => static fn (BatchType $type): string => $type->label(),
                'placeholder' => false,
            ])
            ->add('startDate', DateType::class, [
                'label' => 'Data rozpoczęcia',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => false,
                'attr' => ['rows' => 6],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Batch::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'batch';
    }
}
