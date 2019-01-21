<?php

namespace App\Controller;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PresentationController extends AbstractController
{
    /**
     * @Route("/presentation/new", name="presentation_new", methods={"GET", "POST"})
     */
    public function new()
    {
        // TODO

        return $this->render('presentation/new.html.twig', [
            
        ]);
    }

    /**
     * @Route("/presentation/{id}/edit", name="presentation_edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request)
    {
        // dd ('Nous sommes dans la page Edit');

        return $this->render('presentation/edit.html.twig', [
            
        ]);
    }

    /**
     * @Route("/presentation/{id}/disable", name="presentation_disable", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function disable(Request $request)
    {
        dd('Nous sommes dans la page de traitement Disable');

        $this->redirectToRoute('home_page');
    }

    /**
     * @Route("/presentation/{id}/{slug}", name="presentation_show", methods={"GET"})
     * @ParamConverter("presentation", options={"mapping": {"id": "id"}})
     * @ParamConverter("presentation", options={"mapping": {"slug": "slug"}})
     */
    public function show(Presentation $presentation, EntityManagerInterface $em)
    {

        return $this->render('presentation/show.html.twig', [
            'presentation' => $presentation
        ]);
    }
}
