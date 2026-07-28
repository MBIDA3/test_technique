<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserCsvDto
{
    #[Assert\NotBlank(message: 'Le nom d\'utilisateur est obligatoire.')]
    public string $username = '';

    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email n\'est pas valide.')]
    public string $email = '';

    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    public string $password = '';

    public string $roles = 'ROLE_USER';

    public ?string $rank = null;

    public ?string $victoire = null;

    public ?string $defaite = null;
}
