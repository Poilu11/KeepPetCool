<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comment;
use App\Util\NoteResolver;
use App\Util\CoordResolver;
use App\Entity\Presentation;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Repository\PresentationRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home_page", methods={"GET"})
     */
    public function home(NoteResolver $noteResolver)
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
        $arrayNote = $noteResolver->getUsersNotesFromPres($presentations);
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
     * @Route("/search/{userType}|{adress}|{radius}|{latAndLong}", name="search", methods={"GET", "POST"}, requirements={"userType"="\w*", "adress"="([\w\+\-\',]*)|(adress)", "radius"="[0-9]*|(radius)", "latAndLong" = "(\d*\.?\d*)_(\d*\.?\d*)|(latAndLong)"})
     */
    public function search($userType, $adress, $radius, $latAndLong, UserRepository $userRepo, PresentationRepository $presRepo, NoteResolver $noteResolv, CoordResolver $coordResolv)
    {
        if (!$adress) {
            throw $this->createNotFoundException('Adresse non trouvée');
        }
        // Je check si les informations de la search bar sont vides, si c'est la cas je rajoute des flash messages et je redirige vers la home.
        

        if(empty($userType) || $adress==='' || $radius==='')
        {
            if(empty($userType))
            {
                $this->addFlash('danger', 'Vous devez sélectionner un type d\'utilisateur');
            }

            if($adress==='')
            {
                $this->addFlash('danger', 'Vous devez sélectionner une ville ou un code postal');
            }
            
            if($radius==='')
            {
                $this->addFlash('danger', 'Vous devez saisir un rayon de recherche');
            }

            return $this->redirectToRoute('home_page');
        }


        // Je vérifie que les latitudes et longitudes sorties par la route sont bien conformes.
        // preg_match retourne 1 si ok, 0 si pas ok, false si error.
        if(preg_match('/(\d+\.?\d*)_(\d+\.?\d*)/', $latAndLong) !== 1)
        {
            //Si les latitudes et longitudes ne sont pas conformes, alors que récupère les latitude et longitude sur locationiq.
            $coords = $coordResolv->getCoords($adress);

            if($coords[0] === 'NC' || $coords[1] === 'NC')
            {
                $this->addFlash('danger', 'Impossible de vous localiser l\'emplacement, veuillez resaisir un emplacement dans la barre de recherche.');

                return $this->redirectToRoute('home_page');
            }
        }
        else
        {
            // Si les coordonnées sortant de la route sont valides :
            // Les coordonnées sortant de la route étant au format aaa.aa_bb.bbb, j'utilise explode pour récupérer ma latitude et ma longtude séparemment.
            $coords = explode('_', $latAndLong);
        }


        // On récupère les présentations des users alentours. Ici s'effectue le calcul de la distance.
        $users = $userRepo->findUserNear($userType, $coords[0], $coords[1], $radius);
        $presentations = [];
        foreach($users as $user)
        {
            if(!is_null($presRepo->findOneBy(['user' => $user])))
            {
                $presentations[] = $presRepo->findOneBy(['user' => $user]);
            }
        }

        // Retour de l'état de la recherche à l'utilisateur.
        if(empty($presentations))
        {
            $flashAdress = str_replace('+', ' ', $adress);
            $this->addFlash('danger', 'Personne n\'a été trouvé aux alentours de cette adresse : '.$flashAdress.'.');
        }
        else
        {
            $flashAdress = str_replace('+', ' ', $adress);
            $flashUserType = ($userType==='owner')?'propriétaire':'petsitters';
            $this->addFlash('success', 'Voici les '.$flashUserType.' à proximité de : '.$flashAdress.'.');
        }

        // Les notes moyennes des notes pour chaque user à partir des présentations
        $notes = $noteResolv->getUsersNotesFromPres($presentations);
        
        return $this->render('default/home.html.twig', [
            'presentations' => $presentations,
            'notes' => $notes
        ]);
    }
}
