<?php

namespace App\Controller;

use App\Entity\Presentation;
use App\Repository\CommentRepository;
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
    public function show($id, Presentation $presentation, CommentRepository $commentRepository, EntityManagerInterface $em)
    {

        // DEBUT Calcul moyenne des notes
        // On récupère l'ensemble des commentaires associés au petsitter
        $comments = $commentRepository->findBy(['petsitter' => $id]);
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
        // FIN Calcul moyenne des notes

        return $this->render('presentation/show.html.twig', [
            'presentation' => $presentation,
            'note' => $moy
        ]);
    }
}
