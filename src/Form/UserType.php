<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur (`username`)',
                'constraints' => [
                    new NotBlank(message: 'Le nom d\'utilisateur est requis.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'constraints' => [
                    new NotBlank(message: 'L\'email est requis.'),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle de l\'utilisateur',
                'choices' => [
                    'Utilisateur Standard (ROLE_USER)' => 'ROLE_USER',
                    'Administrateur (ROLE_ADMIN)' => 'ROLE_ADMIN',
                ],
                'multiple' => false,
                'expanded' => false,
            ]);

        // Model Transformer to convert single string selection to array for User::$roles
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    return is_array($rolesArray) && count($rolesArray) ? $rolesArray[0] : 'ROLE_USER';
                },
                function ($rolesString) {
                    return [$rolesString];
                }
            ));

        $passwordConstraints = [];
        if (!$isEdit) {
            $passwordConstraints[] = new NotBlank(message: 'Le mot de passe est requis.');
        }
        $passwordConstraints[] = new Length(min: 6, minMessage: 'Au moins {{ limit }} caractères.');

        $builder->add('plainPassword', PasswordType::class, [
            'mapped' => false,
            'required' => !$isEdit,
            'label' => $isEdit ? 'Nouveau mot de passe (laisser vide pour conserver l\'actuel)' : 'Mot de passe',
            'constraints' => $passwordConstraints,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}
