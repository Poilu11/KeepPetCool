<?php

namespace App\Controller;

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
        $repositoryPresentation = $this->getDoctrine()->getRepository(Presentation::class);

        // Pour administrateurs et modérateurs
        $allPresentations = $repositoryPresentation->findBy([], ['createdAt' => 'DESC']);

        // Pour utlisateurs connectés et non connectés
        $activePresentations = $repositoryPresentation->findBy(['isActive' => true], ['createdAt' => 'DESC']);

        dump($allPresentations);
        dd($activePresentations);

        return $this->render('default/home.html.twig', [
            'allPresentations' => $allPresentations,
            'activePresentations' => $activePresentations
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
