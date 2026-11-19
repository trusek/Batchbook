<?php

namespace App\Form;

use App\Entity\Entry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formularz wpisu (szybki wpis na stronie nastawu). Pola: entry[occurredAt], entry[note].
 *
 * @extends AbstractType<Entry>
 */
final class EntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('occurredAt', DateTimeType::class, [
                'label' => 'Data i godzina',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'with_seconds' => false,
                'invalid_message' => 'Podaj poprawną datę i godzinę.',
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Notatka',
                'attr' => ['rows' => 3, 'autofocus' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Entry::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'entry';
    }
}
