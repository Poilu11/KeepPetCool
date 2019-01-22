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

        // Pour administrateurs et modérateurs
        $allPresentations = $presentationRepository->findAllPresentationsByUserType('petsitter');
        dump($allPresentations);

        // Pour utlisateurs connectés et non connectés
        $activePresentations = $presentationRepository->findActivePresentationsByUserType('petsitter');
        dd($activePresentations);


        // Methode permettant de calculer la moyenne des commentaires par petsitter
        $arrayNote = [];

        foreach($allPresentations as $currentPresentation)
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
        // Fin de la méthode permettant de calculer la moyenne
        

        // dd($arrayNote);

        return $this->render('default/home.html.twig', [
            'allPresentations' => $allPresentations,
            'activePresentations' => $activePresentations,
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
