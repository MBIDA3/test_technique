<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Infos;
use App\Entity\User;
use App\Exception\CsvImportException;
use App\Form\CsvImportType;
use App\Form\InfosType;
use App\Form\UserType;
use App\Repository\InfosRepository;
use App\Repository\UserRepository;
use App\Service\CsvImporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'app_admin_dashboard')]
    public function dashboard(UserRepository $userRepository, InfosRepository $infosRepository): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'total_users' => $userRepository->count([]),
            'total_infos' => $infosRepository->count([]),
            'latest_users' => $userRepository->findBy([], ['id' => 'DESC'], 5),
        ]);
    }

    #[Route('/users', name: 'app_admin_user_index', methods: ['GET'])]
    public function userIndex(UserRepository $userRepository): Response
    {
        return $this->render('admin/user_index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/users/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function userNew(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

            if ($user->getInfos() === null) {
                $infos = new Infos();
                $infos->setRank('Unranked');
                $infos->setVictoire('0');
                $infos->setDefaite('0');
                $user->setInfos($infos);
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', sprintf('Utilisateur "%s" créé avec succès !', $user->getUsername()));

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'title' => 'Créer un Nouvel Utilisateur',
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function userEdit(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string|null $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            if (!empty($plainPassword)) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $em->flush();

            $this->addFlash('success', sprintf('Utilisateur "%s" mis à jour.', $user->getUsername()));

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form,
            'user' => $user,
            'title' => 'Modifier l\'Utilisateur ' . $user->getUsername(),
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function userDelete(Request $request, User $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_user_' . $user->getId(), (string)$request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('app_admin_user_index');
    }

    #[Route('/infos/{id}/edit', name: 'app_admin_infos_edit', methods: ['GET', 'POST'])]
    public function infosEdit(Infos $infos, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(InfosType::class, $infos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Statistiques mises à jour avec succès.');

            return $this->redirectToRoute('app_admin_user_index');
        }

        return $this->render('admin/infos_form.html.twig', [
            'form' => $form,
            'infos' => $infos,
        ]);
    }

    #[Route('/csv-import', name: 'app_admin_csv_import', methods: ['GET', 'POST'])]
    public function csvImport(Request $request, CsvImporterService $csvImporter): Response
    {
        $form = $this->createForm(CsvImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            try {
                $count = $csvImporter->importFromPath($file->getPathname());
                $this->addFlash('success', sprintf('Succès ! %d utilisateurs ont été importés avec succès.', $count));

                return $this->redirectToRoute('app_admin_user_index');
            } catch (CsvImportException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('admin/csv_import.html.twig', [
            'form' => $form,
        ]);
    }
}
