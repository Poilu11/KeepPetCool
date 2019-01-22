<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment_index", methods={"GET"})
     */
    public function index(CommentRepository $commentRepository)
    {
        $comments = $commentRepository->findBy([], ['createdAt' => 'DESC']);

        // dd($comments);

        return $this->render('comment/index.html.twig', [
            'comments' => $comments
        ]);
    }

    /**
     * @Route("/comment/{idPetsitter}/new/{idOwner}", name="comment_new", methods={"POST"}, requirements={"idPetsitter"="\d+"}, requirements={"idOwner"="\d+"})
     * @ParamConverter("user", options={"mapping": {"idPetsitter": "id"}})
     * @ParamConverter("user", options={"mapping": {"idOwner": "id"}})
     */
    public function new(Request $request, EntityManagerInterface $em)
    {
        // TODO traitement du formulaire


        return $this->render('comment/new.html.twig', [
            
        ]);
    }

    /**
     * @Route("/comment/{id}/disable", name="comment_disable", methods={"GET"})
     */
    public function disable(Comment $comment, EntityManagerInterface $em)
    {
       // On vérifie que l'utilisateur soit admin ou modo
       $this->denyAccessUnlessGranted(['ROLE_ADMIN','ROLE_MODO']);

       if($comment->getIsActive())
       {
            $comment->setIsActive(false);
            $em->flush;

            $this->addFlash(
                'success',
                'Commentaire désactivé avec succès !'
            );

       }
       else
       {
            $comment->setIsActive(true);
            $em->flush;

            $this->addFlash(
                'success',
                'Commentaire activé avec succès !'
            );
       }

        return $this->redirectToRoute('comment_index');
    }

    /**
     * @Route("/comment/{id}/validate", name="comment_validate", methods={"GET"})
     */
    public function validate(Comment $comment)
    {
       // On vérifie que l'utilisateur soit connecté
       $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    
       $currentUser = $this->getUser();

       if($currentUser->getId() !== $comment->getUser()->getId())
       {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas valider le commentaire concernant un tiers !'
            );

            return $this->redirectToRoute('dashboard');
       }

        return $this->redirectToRoute('dashboard');
    }
}
