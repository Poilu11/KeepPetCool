<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Presentation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home_page", methods={"GET"})
     */
    public function home()
    {

        $presentationRepository = $this->getDoctrine()->getRepository(Presentation::class);


        // Si utisateur non connecté
        // accès à toutes les présentations petsitter actives
        $presentations = $presentationRepository->findActivePresentationsByUserType('petsitter');


        // Si utisateur non connecté
        if ($this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY'))
        {
            // On récupère les infos de l'utilisateur
            $currentUser = $this->getUser();

            if($currentUser->getType() == 'owner')
            {
                $presentations = $presentationRepository->findActivePresentationsByUserType('petsitter');
            }

            if($currentUser->getType() == 'petsitter')
            {
                $presentations = $presentationRepository->findActivePresentationsByUserType('owner');
            }

            if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
            {
                $presentations = $presentationRepository->findBy([], ['createdAt' => 'DESC']);
            }
        }


        // DEBUT calcul moyenne des notes de tous les petsitters
        $arrayNote = [];

        foreach($presentations as $currentPresentation)
        {
            $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
            $comments = $commentRepository->findBy(['petsitter' => $currentPresentation->getUser()->getId()]);
            $commentsCount = count($comments);

            // On initialise la variable d'addition
            $sum = 0;

            foreach($comments as $currentComment)
            {
                $sum += $currentComment->getNote();
            }

            if($commentsCount > 0)
            {
                $moy = $sum / $commentsCount;
            }
            else
            {
                $moy = 'NC';
            }

            $arrayNote[$currentPresentation->getUser()->getId()] = $moy;

        }
        // FIN calcul moyenne des notes de tous les petsitterse

        return $this->render('default/home.html.twig', [
            'presentations' => $presentations,
            'notes' => $arrayNote
        ]);
    }

    /**
     * @Route("/mentions-legales", name="mention", methods={"GET"})
     */
    public function mention()
    {
        
        return $this->render('default/mention.html.twig', [
            
        ]);
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     */
    public function faq()
    {
        
        return $this->render('default/faq.html.twig', [
            
        ]);
    }

    /**
     * @Route("/contact", name="contact", methods={"GET", "POST"})
     */
    public function contact()
    {
        
        return $this->render('default/contact.html.twig', [
            
        ]);
    }

    /**
     * @Route("/search", name="search", methods={"GET", "POST"})
     */
    public function search()
    {
        
        return $this->render('default/search.html.twig', [
            
        ]);
    }
}
