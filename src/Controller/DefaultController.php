<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home_page")
     */
    public function home()
    {
        return $this->render('presentation/home.html.twig', [
            // 'controller_name' => 'DefaultController',
        ]);
    }
}
