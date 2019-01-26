<?php

namespace App\Controller;

use App\Util\Slugger;
use App\Entity\Presentation;
use App\Form\PresentationType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PresentationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PresentationController extends AbstractController
{
    /**
     * @Route("/presentation/new", name="presentation_new", methods={"GET", "POST"})
     */
    public function new(Request $request, PresentationRepository $presentationRepository, Slugger $slugger, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupere les informations du user connecté
        $currentUser = $this->getUser();

        // Dans le cas où l'utilisateur a déjà créé une fiche de présentation,
        // on le redirige vers la page d'édition de sa présentation
        // afin de limiter le nbr de présentation à une seule par utilisateur
        if(count($presentationRepository->findBy(['user' => $currentUser])) > 0)
        {
            // On récupère la présentation de l'utilisateur
            // et l'id de la présentation
            $presentation = $presentationRepository->findOneBy(['user' => $currentUser]);
            $presentationId = $presentation->getId();

            // Redirection vers la page d'édition de la présentation
            return $this->redirectToRoute('presentation_edit', ['id' => $presentationId]);
        }


        $presentation = new Presentation();

        $form = $this->createForm(PresentationType::class, $presentation);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $presentation->setUser($currentUser);
            $presentation->setSlug($slugger->sluggify($presentation->getTitle()));

            // dd($presentation);

            $em->persist($presentation);
            $em->flush();

            $this->addFlash(
                'success',
                'Votre présentation a été créée avec succès !'
            );
            
            return $this->redirectToRoute('presentation_show', [
                'id' => $presentation->getId(),
                'slug' => $presentation->getSlug(),
            ]);
        }

        return $this->render('presentation/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/presentation/{id}/edit", name="presentation_edit", methods={"GET", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Presentation $presentation, Request $request, Slugger $slugger, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupere les informations du user connecté
        $currentUser = $this->getUser();

        // On vérifie que le currentUser et le même utilisateur
        // que le user concerné par la fiche de présentation
        if($currentUser !== $presentation->getUser())
        {
            $this->addFlash(
                'danger',
                'Vous ne pouvez pas éditer la fiche de présentation d\'un tiers !'
            );
            
            return $this->redirectToRoute('dashboard');
        }

        $form = $this->createForm(PresentationType::class, $presentation);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $presentation->setSlug($slugger->sluggify($presentation->getTitle()));

            $em->flush();

            $this->addFlash(
                'success',
                'Votre présentation a été correctement modifiée !'
            );
            
            return $this->redirectToRoute('presentation_show', [
                'id' => $presentation->getId(),
                'slug' => $presentation->getSlug(),
            ]);
        }

        return $this->render('presentation/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/presentation/{id}/disable", name="presentation_disable", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function disable(Request $request)
    {
        dd('Nous sommes dans la page de traitement Presentation Disable');

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
