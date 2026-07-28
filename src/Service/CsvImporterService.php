<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\UserCsvDto;
use App\Entity\Infos;
use App\Entity\User;
use App\Exception\CsvImportException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CsvImporterService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {
    }

    public function importFromPath(string $filePath): int
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new CsvImportException(sprintf('Le fichier CSV est introuvable ou unlisible : "%s"', $filePath));
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new CsvImportException('Impossible d\'ouvrir le fichier CSV.');
        }

        $header = fgetcsv($handle, 0, ';');
        if ($header === false || count($header) < 3) {
            rewind($handle);
            $header = fgetcsv($handle, 0, ',');
            $delimiter = ',';
        } else {
            $delimiter = ';';
        }

        if ($header === false) {
            fclose($handle);
            throw new CsvImportException('Le fichier CSV est vide ou mal formaté.');
        }

        $header = array_map(fn($col) => strtolower(trim((string) $col)), $header);

        $dtos = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;
            if (count($row) === 1 && empty($row[0])) {
                continue;
            }

            if (count($row) !== count($header)) {
                fclose($handle);
                throw new CsvImportException(sprintf('Ligne %d : le nombre de colonnes ne correspond pas à l\'en-tête.', $lineNumber));
            }

            $data = array_combine($header, $row);
            if ($data === false) {
                fclose($handle);
                throw new CsvImportException(sprintf('Ligne %d : erreur lors de la lecture des données.', $lineNumber));
            }

            $dto = new UserCsvDto();
            $dto->username = trim($data['username'] ?? '');
            $dto->email = trim($data['email'] ?? '');
            $dto->password = trim($data['password'] ?? '');
            $dto->roles = trim($data['roles'] ?? 'ROLE_USER');
            $dto->rank = isset($data['rank']) && $data['rank'] !== '' ? trim($data['rank']) : null;
            $dto->victoire = isset($data['victoire']) && $data['victoire'] !== '' ? trim($data['victoire']) : null;
            $dto->defaite = isset($data['defaite']) && $data['defaite'] !== '' ? trim($data['defaite']) : null;

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                fclose($handle);
                $errorMsg = (string) $errors->get(0)->getMessage();
                throw new CsvImportException(sprintf('Ligne %d (%s) : %s', $lineNumber, $dto->username, $errorMsg));
            }

            $dtos[] = $dto;
        }

        fclose($handle);

        if (empty($dtos)) {
            return 0;
        }

        return $this->entityManager->wrapInTransaction(function () use ($dtos): int {
            $count = 0;
            $userRepository = $this->entityManager->getRepository(User::class);

            foreach ($dtos as $dto) {
                $existingUser = $userRepository->findOneBy(['username' => $dto->username]);
                if ($existingUser !== null) {
                    continue;
                }

                $user = new User();
                $user->setUsername($dto->username);
                $user->setEmail($dto->email);
                
                $roles = array_filter(array_map('trim', explode(',', $dto->roles)));
                if (empty($roles)) {
                    $roles = ['ROLE_USER'];
                }
                $user->setRoles($roles);

                $hashedPassword = $this->passwordHasher->hashPassword($user, $dto->password);
                $user->setPassword($hashedPassword);

                $infos = new Infos();
                $infos->setRank($dto->rank);
                $infos->setVictoire($dto->victoire);
                $infos->setDefaite($dto->defaite);
                $user->setInfos($infos);

                $this->entityManager->persist($user);
                $count++;
            }

            $this->entityManager->flush();

            return $count;
        });
    }
}
