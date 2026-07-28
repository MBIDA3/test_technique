<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Infos;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserAndInfosEntityTest extends TestCase
{
    public function testUserEntitySettersAndGetters(): void
    {
        $user = new User();
        $user->setUsername('test_01');
        $user->setEmail('test_01@test.fr');
        $user->setPassword('hashed_password');
        $user->setRoles(['ROLE_ADMIN']);

        $this->assertSame('test_01', $user->getUsername());
        $this->assertSame('test_01@test.fr', $user->getEmail());
        $this->assertSame('hashed_password', $user->getPassword());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
        $this->assertContains('ROLE_USER', $user->getRoles());
        $this->assertSame('test_01', $user->getUserIdentifier());
    }

    public function testUserDefaultRoleContainsRoleUser(): void
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testInfosEntitySettersAndGetters(): void
    {
        $infos = new Infos();
        $infos->setRank('Silver');
        $infos->setVictoire('5');
        $infos->setDefaite('3');

        $this->assertSame('Silver', $infos->getRank());
        $this->assertSame('5', $infos->getVictoire());
        $this->assertSame('3', $infos->getDefaite());
    }

    public function testOneToOneRelationshipBetweenUserAndInfos(): void
    {
        $user = new User();
        $user->setUsername('gamer123');

        $infos = new Infos();
        $infos->setRank('Gold');
        $infos->setVictoire('10');
        $infos->setDefaite('2');
        $infos->setUser($user);

        $this->assertSame($user, $infos->getUser());
        $this->assertSame($infos, $user->getInfos());
    }
}
