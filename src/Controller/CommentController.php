<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PresentationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{idPetsitter}/new/{idOwner}", name="comment_new", methods={"POST"}, requirements={"idPetsitter"="\d+"}, requirements={"idOwner"="\d+"})
     * @ParamConverter("user", options={"mapping": {"idPetsitter": "id"}})
     * @ParamConverter("user", options={"mapping": {"idOwner": "id"}})
     */
    public function new(Request $request, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Récupération des informations de l'utilisateur
        // actuellement connecté
        $user = $this->getUser();

        if($user->getType() !== 'owner')
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à laisser un commentaire'
            );

            return $this->redirectToRoute('dashboard');
        }


        return $this->render('comment/new.html.twig', [
            
        ]);
    }

    /**
     * @Route("/comment/{id}/disable", name="comment_disable", methods={"GET"})
     */
    public function disable(Comment $comment, PresentationRepository $presentationRepository, EntityManagerInterface $em)
    {
       // On vérifie que l'utilisateur soit admin ou modo
       $this->denyAccessUnlessGranted(['ROLE_ADMIN','ROLE_MODO']);

       if($comment->getIsActive())
       {
            $comment->setIsActive(false);
            $em->flush();

            $this->addFlash(
                'success',
                'Commentaire désactivé avec succès !'
            );

       }
       else
       {
            $comment->setIsActive(true);
            $em->flush();

            $this->addFlash(
                'success',
                'Commentaire activé avec succès !'
            );
       }

       $petsitter = $comment->getPetsitter();
       $presentation = $presentationRepository->findOneBy(['user' => $petsitter]);

        return $this->redirectToRoute('presentation_show', [
            'id' => $presentation->getId(),
            'slug' => $presentation->getSlug()
            ]);
    }

    /**
     * @Route("/comment/{id}/validate", name="comment_validate", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function validate(Comment $comment, EntityManagerInterface $em)
    {
       // On vérifie que l'utilisateur soit connecté
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
       // On récupère les informations du user actuellement connecté
       $currentUser = $this->getUser();

       // On vérifie que le user connecté correspond au petsitter
       // concerné par la commentaire, sinon on ne traite pas la demande de validation
       if($currentUser->getId() !== $comment->getPetsitter()->getId())
       {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas valider le commentaire concernant un tiers !'
            );

            return $this->redirectToRoute('dashboard');
       }

       // On valide le commentaire
       $comment->setIsValidated(true);
       $comment->setIsDisplayed(false);
       $em->flush();

       $this->addFlash(
            'success',
            'Commentaire correctement validé !'
        );

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/comment/{id}/notvalidate", name="comment_not_validate", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function notValidate(Comment $comment, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupère les informations du user actuellement connecté
        $currentUser = $this->getUser();

        // On vérifie que le user connecté correspond au petsitter
        // concerné par la commentaire, sinon on ne traite pas la demande de validation
        if($currentUser->getId() !== $comment->getPetsitter()->getId())
        {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas refuser le commentaire concernant un tiers !'
            );

            return $this->redirectToRoute('dashboard');
        }

        // On ne valide pas le commentaire
        // et on demande que le commentaire n'apparaisse plus
        $comment->setIsDisplayed(false);
        $em->flush();

        $this->addFlash(
            'success',
            'Commentaire refusé avec succès !'
        );

        return $this->redirectToRoute('dashboard');
    }
}
