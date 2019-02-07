<?php

namespace App\Controller;

use App\Repository\AnimalRepository;
use App\Repository\PresentationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api", methods={"GET"})
     */
    public function api(Request $request, PresentationRepository $presentationRepository, AnimalRepository $animalRepository)
    {
        $filter = $request->query->get('filter');

        if(isset($filter) || !empty($filter))
        {
            if($filter == 'all' || $filter == 'petsitter' || $filter =='owner' || $filter =='animal')
            {
                if($filter == 'all')
                {
                    $presentations = $presentationRepository->findBy(['isActive' => true]);
                    
                    foreach($presentations as $presentation)
                    {
                        $response[$presentation->getId()] = [
                            $presentation->getTitle(),
                            $presentation->getBody(),
                            $presentation->getUser()->getUsername()
                        ];
                    }
                   
                    return $this->json($response);
                }

                if($filter == 'petsitter' || $filter == 'owner')
                {
                    $presentations = $presentationRepository->findAllPresentationsByUserType($filter, true);
                    
                    foreach($presentations as $presentation)
                    {
                        $response[$presentation->getId()] = [
                            $presentation->getTitle(),
                            $presentation->getBody(),
                            $presentation->getUser()->getUsername()
                        ];
                    }

                    return $this->json($response);
                }

                if($filter == 'animal')
                {
                    $animals = $animalRepository->findBy(['isActive' => true]);
                    
                    foreach($animals as $animal)
                    {
                        $response[$animal->getId()] = [
                            $animal->getTitle(),
                            $animal->getName(),
                            $animal->getBody(),
                            $animal->getUser()->getUsername()
                        ];
                    }
                    
                    return $this->json($response);
                }
            }
            else
            {
                throw $this->createNotFoundException('Aucun résultat !');
            }
        }
        else
        {
            throw $this->createNotFoundException('Aucun résultat !');
        }
        
    }
}
