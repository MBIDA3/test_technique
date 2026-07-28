<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Infos;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAndUserProfileControllerTest extends WebTestCase
{
    private function createUsersAndSchema(): array
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }

        $admin = new User();
        $admin->setUsername('admin_test');
        $admin->setEmail('admin_test@test.fr');
        $admin->setPassword('password123');
        $admin->setRoles(['ROLE_ADMIN']);

        $adminInfos = new Infos();
        $adminInfos->setRank('Challenger');
        $adminInfos->setVictoire('50');
        $adminInfos->setDefaite('5');
        $admin->setInfos($adminInfos);

        $user = new User();
        $user->setUsername('user_test');
        $user->setEmail('user_test@test.fr');
        $user->setPassword('password123');
        $user->setRoles(['ROLE_USER']);

        $userInfos = new Infos();
        $userInfos->setRank('Silver');
        $userInfos->setVictoire('10');
        $userInfos->setDefaite('8');
        $user->setInfos($userInfos);

        $em->persist($admin);
        $em->persist($user);
        $em->flush();

        return [$client, $admin, $user];
    }

    public function testStandardUserAccessToAdminIsForbidden(): void
    {
        [$client, $admin, $user] = $this->createUsersAndSchema();

        $client->loginUser($user);
        $client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessAdminDashboardAndUserList(): void
    {
        [$client, $admin, $user] = $this->createUsersAndSchema();

        $client->loginUser($admin);
        $client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Gestion des Utilisateurs');
    }

    public function testUserCanAccessProfilePage(): void
    {
        [$client, $admin, $user] = $this->createUsersAndSchema();

        $client->loginUser($user);
        $client->request('GET', '/profile');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mon Profil');
        $this->assertSelectorTextContains('.rank-badge', 'Silver');
    }
}
