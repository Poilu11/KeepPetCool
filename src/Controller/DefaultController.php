<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Util\NoteResolver;
use App\Entity\Presentation;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home_page", methods={"GET"})
     */
    public function home(NoteResolver $noteResolv)
    {

        $presentationRepository = $this->getDoctrine()->getRepository(Presentation::class);

        // Si utisateur non connecté
        // accès à toutes les présentations petsitter actives
        $presentations = $presentationRepository->findActivePresentationsByUserType('petsitter');

        // On récupère les infos de l'utilisateur connecté (si connecté)
        $currentUser = $this->getUser();

        // Si utisateur non connecté
        if (isset($currentUser) && !empty($currentUser))
        {
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
        $arrayNote = $noteResolv->getUsersNotes($presentations);
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
     * @Route("/search/{userType}-{city}-{radius}-{latAndLong}", name="search", methods={"GET", "POST"}, requirements={"userType"="\w*", "city"="[\w|_]*", "radius"="[0-9]*", "latAndLong" = "(\d*\.?\d*)\+?(\d*\.?\d*)"})
     */
    public function search($userType, $city, $radius, $latAndLong, UserRepository $userRepo, NoteResolver $noteResolv)
    {
        if(is_null($userType) || is_null($city) || is_null($radius))
        {
            if(is_null($userType))
            {
                $this->addFlash('danger', 'Vous devez sélectionner un type d\'utilisateur');
            }

            if(is_null($city))
            {
                $this->addFlash('danger', 'Vous devez sélectionner une ville');
            }
            
            if(is_null($radius))
            {
                $this->addFlash('danger', 'Vous devez saisir un rayon de recherche');
            }

            return $this->redirectToRoute('home');
        }

        $coords = explode('+', $latAndLong);
        $users = $userRepo->findUserNear($userType, $coords[0], $coords[1], $radius);

        /*
        $presentations = [];
        foreach($users as $user)
        {
           $presentations[] = $user->getPresentation();
        }

        $notes = $noteResolv->getUsersNotes($presentations);
        */

        
        $debug = 'La route fonctionne, voici les données en entrées de la recherche :'.PHP_EOL.PHP_EOL;

        $debug .= "Le type d'utilisateur recherché : ".$userType.PHP_EOL.PHP_EOL;

        $debug .= "Le zipcode de la ville : ".$city.PHP_EOL.PHP_EOL;

        $debug .= "Le rayon de la recherche : ".$radius.PHP_EOL.PHP_EOL;

        dd($debug);
        
        return $this->render('default/home.html.twig', [
            'presentations' => $presentations,
            'notes' => $notes
        ]);
    }
}
