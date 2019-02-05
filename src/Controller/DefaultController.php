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
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="welcome", methods={"GET"})
     */
    public function welcome()
    {
        // Notre page d'accueil explicative

        return $this->render('default/welcome.html.twig', [
        ]);
    }

    /**
     * @Route("/index", name="home_page", methods={"GET"})
     */
    public function home(NoteResolver $noteResolver, PaginatorInterface $paginator, Request $request)
    {
        // On récupère les infos de l'utilisateur connecté (si connecté)
        $currentUser = $this->getUser();

        // On récupère le Repository de PresetationEntity
        $presentationRepository = $this->getDoctrine()->getRepository(Presentation::class);

        // Si utisateur non connecté
        // accès à toutes les présentations petsitter actives
        if(!isset($currentUser) || empty($currentUser))
        {
            $allPresentations = $presentationRepository->findAllPresentations(true);
            dump($allPresentations);
        }
        
        // Si utisateur connecté
        if (isset($currentUser) && !empty($currentUser))
        {
            // Si utilisateur a le rôle de User
            if($currentUser->getRole()->getCode() == 'ROLE_USER')
            {
                $allPresentations = $presentationRepository->findAllPresentations(true);
            }

            // Si utilisateur a le rôle de Admin ou Modo
            if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
            {
                $allPresentations = $presentationRepository->findAllPresentations();
            }
        }

        // UTILISATION DES FILTRES
        $filter = $request->query->get('filter');

        if(isset($filter) && !empty($filter))
        {
            // Gestion 404
            if ($filter != 'petsitter' || $filter != 'owner') {
                throw $this->createNotFoundException('Cette rechercheche n\'a pas pu aboutir !');
            }

            // Utilisateur non connecté
            if(!isset($currentUser) || empty($currentUser))
            {
                $allPresentations = $presentationRepository->findAllPresentationsByUserType($filter, true);
                dump($allPresentations);
            }
            
            // Si utilisateur connecté
            if (isset($currentUser) && !empty($currentUser))
            {
                // Si utilisateur a le rôle de User
                if($currentUser->getRole()->getCode() == 'ROLE_USER')
                {
                    $allPresentations = $presentationRepository->findAllPresentationsByUserType($filter, true);
                }

                // Si utilisateur a le rôle de Admin ou Modo
                if($currentUser->getRole()->getCode() == 'ROLE_ADMIN' || $currentUser->getRole()->getCode() == 'ROLE_MODO')
                {
                    $allPresentations = $presentationRepository->findAllPresentationsByUserType($filter);                    
                }
            }
        }

        // DEBUT calcul moyenne des notes de tous les petsitters
        $arrayNote = $noteResolver->getUsersNotesFromPres($allPresentations);
        // FIN calcul moyenne des notes de tous les petsitterse

        // PAGINATION
        // https://packagist.org/packages/knplabs/knp-paginator-bundle
        // Pour la pagination, injection des services PaginatorInterface et Request
        // https://stackoverflow.com/questions/48740064/symfony-4-knppaginator-bundle-service-not-found-even-though-it-exists-in-app

        $presentations = $paginator->paginate(
            // Doctrine Query, not results
            $allPresentations,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            7
        );

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
    public function faq(Request $request)
    {
        // Si requête Ajax
        if ($request->isXmlHttpRequest())
        {
            $array = [
                 0 => [
                    'One',
                    'Question une ?',
                    'nim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.'
                ],
                1 => [
                    'Two',
                    'Question deux ?',
                    'nim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.'
                ],
                2 => [
                    'Three',
                    'Question Trois ?',
                    'nim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven\'t heard of them accusamus labore sustainable VHS.'
                ]
            ];

            $jsonArray = $this->json($array);

            return $jsonArray;
        }

        dump('Ajax Controller KO');
        
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
    public function search($userType, $adress, $radius, $latAndLong, UserRepository $userRepo, PresentationRepository $presRepo, NoteResolver $noteResolv, CoordResolver $coordResolv, PaginatorInterface $paginator, Request $request)
    {

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

        // PAGINATION
        // https://packagist.org/packages/knplabs/knp-paginator-bundle
        // Pour la pagination, injection des services PaginatorInterface et Request
        // https://stackoverflow.com/questions/48740064/symfony-4-knppaginator-bundle-service-not-found-even-though-it-exists-in-app
        $presentations = $paginator->paginate(
            // Doctrine Query, not results
            $presentations,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            7
        );
        
        return $this->render('default/home.html.twig', [
            'presentations' => $presentations,
            'notes' => $notes,
            'adress' => $adress,
            'radius' => $radius,
            'userType' => $userType
        ]);
    }
}
