<?php

namespace App\Controller;

use App\Util\Slugger;
use App\Util\NoteResolver;
use App\Entity\Presentation;
use App\Form\PresentationType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PresentationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
                'slug' => $presentation->getSlug(),
                'id' => $presentation->getId()
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
                'slug' => $presentation->getSlug(),
                'id' => $presentation->getId()
            ]);
        }

        return $this->render('presentation/edit.html.twig', [
            'presentation' => $presentation,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/presentation/{id}/disable", name="presentation_disable", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function disable(Presentation $presentation, Request $request, EntityManagerInterface $em)
    {
        // On vérifie que l'utilisateur soit admin ou modo ou le titulaire de sa fiche de présentation
       $this->denyAccessUnlessGranted(['IS_AUTHENTICATED_FULLY']);

       $currentUser = $this->getUser();

       if($currentUser->getRole()->getCode() !== 'ROLE_ADMIN' && $currentUser->getRole()->getCode() !== 'ROLE_MODO' && $currentUser->getId() !== $presentation->getUser()->getId())
       {
            $this->addFlash(
                'danger',
                'Vous n\'avez pas l\'autorisaton de désactiver la présentation d\'un tiers'
            );

            return $this->redirectToRoute('dashboard');
       }

       if($presentation->getIsActive())
       {
            $presentation->setIsActive(false);
            $em->flush();

            $this->addFlash(
                'success',
                'Présentation désactivée avec succès !'
            );

       }
       else
       {
            $presentation->setIsActive(true);
            $em->flush();

            $this->addFlash(
                'success',
                'Présentation activée avec succès !'
            );
       }

        return $this->redirectToRoute('presentation_show', [
            'slug' => $presentation->getSlug(),
            'id' => $presentation->getId()
        ]);
    }

    /**
     * @Route("/presentation/disablelist", name="presentation_disable_list", methods={"GET"})
     */
    public function disableListPresentation(PresentationRepository $presentationRepository)
    {
        // On vérifie que l'utilisateur soit admin ou modo
       $this->denyAccessUnlessGranted(['ROLE_ADMIN','ROLE_MODO']);

       // On récupère la liste de tous les publications désactivées
       $presentations = $presentationRepository->findBy(['isActive' => false], ['createdAt' => 'DESC']);

       return $this->render('presentation/listDisablePresentations.html.twig', [
            'presentations' => $presentations
        ]);
    }

    /**
     * @Route("/presentation/{id}/{slug}", name="presentation_show", methods={"GET"}, requirements={"id"="\d+"})
     * @ParamConverter("presentation", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("presentation", options={"mapping": {"id": "id"}})
     */
    public function show($id, $slug, Presentation $presentation, PresentationRepository $presentationRepository, CommentRepository $commentRepository, NoteResolver $noteResolver, EntityManagerInterface $em)
    {
        // On vérifie que le slug est égal au slug de la présentation recherché par l'id
        if($slug !== $presentationRepository->find($id)->getSlug())
        {
            throw $this->createNotFoundException('Présentation non trouvée');
        }

        $currentUser = $this->getUser();

        if(!$presentation->getIsActive())
        {
            // Utilisateur non connecté
            if(!isset($currentUser))
            {
                $this->addFlash(
                    'danger',
                    'Présentation actuellement bloquée !'
                );

                return $this->redirectToRoute('home_page');
            }

            // Utilisateur connecté avec le rôle User
            if(isset($currentUser) && $currentUser->getRole()->getCode() == 'ROLE_USER')
            {
                $this->addFlash(
                    'danger',
                    'Présentation actuellement bloquée !'
                );

                return $this->redirectToRoute('home_page');
            }

        }

        // Gestion de l'affichage des commentaires selons le rôle de l'utilisateur
        // DEBUT Calcul moyenne des notes
        // On récupère l'ensemble des commentaires associés au petsitter
        if(isset($currentUser) && !empty($currentUser))
        {
            if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
            {
                $comments = $commentRepository->findBy([
                    'petsitter' => $presentation->getUser(),
                    'isValidated' => true
                    ], ['createdAt' => 'ASC']);
                $commentsCount = count($comments);
            }
            else
            {
                $comments = $commentRepository->findBy([
                    'petsitter' => $presentation->getUser(),
                    'isActive' => true,
                    'isValidated' => true
                    ], ['createdAt' => 'ASC']);
                $commentsCount = count($comments);
            }
        }
        // Utilisateur non connecté
        else
        {
            $comments = $commentRepository->findBy([
                'petsitter' => $presentation->getUser(),
                'isActive' => true,
                'isValidated' => true
                ], ['createdAt' => 'ASC']);
            $commentsCount = count($comments);
        }


        // NE PAS EFFACER, MERCI :-)
        // NE PAS EFFACER, MERCI :-)

        // On initialise la variable d'addition
        // $sum = 0;

        // foreach($comments as $currentComment)
        // {
        //     $sum += $currentComment->getNote();
        // }

        // if($commentsCount > 0)
        // {
        //     $moy = $sum / $commentsCount;
        // }
        // else
        // {
        //     $moy = 'NC';
        // }
        // FIN Calcul moyenne des notes

        return $this->render('presentation/show.html.twig', [
            'presentation' => $presentation,
            'comments' => $comments,
            'nbComments' => count($comments),
            'note' => $noteResolver->getUserNoteFromPres($presentation)
        ]);
    }

}
