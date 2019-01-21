<?php

namespace App\Controller;

use App\Entity\Presentation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PresentationController extends AbstractController
{
    /**
     * @Route("/presentation/{id}/{slug}", name="presentation_show", methods={"GET"})
     * @ParamConverter("presentation", options={"mapping": {"id": "id"}})
     * @ParamConverter("presentation", options={"mapping": {"slug": "slug"}})
     */
    public function index(Presentation $presentation, EntityManagerInterface $em)
    {
        dd($presentation);

        return $this->render('presentation/show.html.twig', [
            'presentation' => $presentation
        ]);
    }
}
