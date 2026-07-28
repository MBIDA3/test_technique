<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Infos;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InfosType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rank', ChoiceType::class, [
                'label' => 'Rang / Classement',
                'choices' => [
                    'Unranked' => 'Unranked',
                    'Iron' => 'Iron',
                    'Bronze' => 'Bronze',
                    'Silver' => 'Silver',
                    'Gold' => 'Gold',
                    'Platine' => 'Platine',
                    'Diamant' => 'Diamant',
                    'Master' => 'Master',
                    'Challenger' => 'Challenger',
                ],
                'required' => false,
            ])
            ->add('victoire', TextType::class, [
                'label' => 'Nombre de Victoires',
                'required' => false,
            ])
            ->add('defaite', TextType::class, [
                'label' => 'Nombre de Défaites',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Infos::class,
        ]);
    }
}
