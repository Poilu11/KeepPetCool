<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
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
    public function home()
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

        // dump($presentations);

        // DEBUT calcul moyenne des notes de tous les petsitters
        $arrayNote = [];

        foreach($presentations as $currentPresentation)
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
        // FIN calcul moyenne des notes de tous les petsitterse

        // dd($arrayNote);

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
    public function search($userType, $city, $radius, $latAndLong, UserRepository $userRepo)
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
        $user = $userRepo->findOneBy(['username' => 'paulette']);

        $dist = sqrt(pow($user->getLatitude() - $coords[0],2) + pow($user->getLatitude() - $coords[0],2));
        dump($dist);
        */
        





        dump($users);

        

        
        $debug = 'La route fonctionne, voici les données en entrées de la recherche :'.PHP_EOL.PHP_EOL;

        $debug .= "Le type d'utilisateur recherché : ".$userType.PHP_EOL.PHP_EOL;

        $debug .= "Le zipcode de la ville : ".$city.PHP_EOL.PHP_EOL;

        $debug .= "Le rayon de la recherche : ".$radius.PHP_EOL.PHP_EOL;

        dd($debug);
        
        return $this->render('default/search.html.twig', [
            
        ]);
    }

    function getNotesArray(CommentRepository $commentRepo)
    {

    }
}
