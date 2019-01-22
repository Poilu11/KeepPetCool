<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard", methods={"GET"})
     */
    public function dashboard(CommentRepository $commentRepository)
    {
        // On vérifie que l'utilisateur soit connecté
         $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération des informations de l'utilisateur
        // actuellement connecté
        $user = $this->getUser();

        $commentsToValidate = $commentRepository->findBy(['petsitter' => $user, 'isValidate' => false], ['createdAt' => 'DESC']);

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'comments' => $commentsToValidate
        ]);
    }

    /**
     * @Route("/profile/{id}/edit", name="profile_edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit($id, User $user, Request $request)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        // Si les données de l'user connecté sont différentes des données de l'user qu'on cherche à éditer, on éjecte et on redirige vers dashboard
        if($currentUser->getId() !== $user->getId())
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à modifier les informations d\'un tiers !'
            );

            $this->redirecToRoute('dashboard');
        }

        // TODO traitement du Form

        return $this->render('user/edit.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/profile/{id}/disable", name="profile_disable", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function disable(User $user, Request $request)
    {
        // On vérifie que l'utilisateur soit admin ou modo
        $this->denyAccessUnlessGranted(['ROLE_ADMIN','ROLE_MODO']);

        // Je récupere les informations de l'user connecté
        $currentUser = $this->getUser();

        if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à désactiver le compte de ce type d\'utilisateur'
            );

            $this->redirecToRoute('dashboard');
        }

        $this->redirectToRoute('home_page');
    }

    /**
     * @Route("/status", name="status", methods={"GET", "POST"})
     */
    public function status(Request $request)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('ROLE_ADMIN');


        return $this->render('user/status.html.twig');
    }

}
