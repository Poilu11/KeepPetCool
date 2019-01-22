<?php

namespace App\Controller;

use App\Entity\User;
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
    public function new($idPetsitter, $idOwner, User $user, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em)
    {
        dd('Page création');

        // TODO traitement du formulaire


        return $this->render('comment/new.html.twig', [
            
        ]);
    }
}
