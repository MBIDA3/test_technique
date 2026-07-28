<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\User;
use App\Service\CsvImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CsvImporterServiceTest extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        if (!empty($metadata)) {
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
        }
    }

    public function testImportValidCsvFileCreatesUsersAndInfos(): void
    {
        $container = static::getContainer();

        /** @var CsvImporterService $csvImporter */
        $csvImporter = $container->get(CsvImporterService::class);

        $csvFilePath = $container->getParameter('kernel.project_dir') . '/test.csv';
        $this->assertFileExists($csvFilePath);

        $importedCount = $csvImporter->importFromPath($csvFilePath);

        $this->assertSame(7, $importedCount);

        // Verify User test_01 created with rank Silver, 5 victories, 5 defeats
        $userRepository = $container->get('doctrine')->getRepository(User::class);
        /** @var User|null $user1 */
        $user1 = $userRepository->findOneBy(['username' => 'test_01']);

        $this->assertNotNull($user1);
        $this->assertSame('test_01@test.fr', $user1->getEmail());
        $this->assertNotNull($user1->getInfos());
        $this->assertSame('Silver', $user1->getInfos()->getRank());
        $this->assertSame('5', $user1->getInfos()->getVictoire());
        $this->assertSame('5', $user1->getInfos()->getDefaite());

        // Verify User test_06 created as ROLE_ADMIN
        /** @var User|null $user6 */
        $user6 = $userRepository->findOneBy(['username' => 'test_06']);
        $this->assertNotNull($user6);
        $this->assertContains('ROLE_ADMIN', $user6->getRoles());
    }
}
