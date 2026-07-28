<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Infos;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // 1. Create Default Admin User
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@cisad.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'password123');
        $admin->setPassword($hashedPassword);

        $adminInfos = new Infos();
        $adminInfos->setRank('Challenger');
        $adminInfos->setVictoire('100');
        $adminInfos->setDefaite('0');
        $adminInfos->setUser($admin);

        $manager->persist($admin);

        // 2. Create Default Standard User
        $user = new User();
        $user->setUsername('user_demo');
        $user->setEmail('user@cisad.fr');
        $user->setRoles(['ROLE_USER']);
        $hashedPasswordUser = $this->passwordHasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPasswordUser);

        $userInfos = new Infos();
        $userInfos->setRank('Gold');
        $userInfos->setVictoire('15');
        $userInfos->setDefaite('10');
        $userInfos->setUser($user);

        $manager->persist($user);

        $manager->flush();
    }
}
