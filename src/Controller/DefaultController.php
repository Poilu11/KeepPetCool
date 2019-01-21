<?php

namespace App\Controller;

use App\Entity\Presentation;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
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
            // 'controller_name' => 'DefaultController',
        ]);
    }
}
