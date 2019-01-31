<?php

namespace App\Controller;

use App\Util\Slugger;
use App\Entity\Animal;
use App\Form\AnimalType;
use App\Repository\AnimalRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/animal")
 */
class AnimalController extends AbstractController
{
    /**
     * @Route("/list", name="animal_list_index", methods={"GET"})
     */
    public function index(AnimalRepository $animalRepository): Response
    {
        // On vérifie que l'utilisateur soit admin ou modo ou le titulaire de sa fiche de présentation
       $this->denyAccessUnlessGranted(['IS_AUTHENTICATED_FULLY']);

       // On récupère les infos du user connecté
       $currentUser = $this->getUser();

       $animals = $animalRepository->findBy(['user' => $currentUser], ['title' => 'DESC']);

        return $this->render('animal/index.html.twig', [
            'animals' => $animals
        ]);
    }

    /**
     * @Route("/new", name="animal_new", methods={"GET","POST"})
     */
    public function new(Request $request, Slugger $slugger): Response
    {
        // On vérifie que l'utilisateur soit admin ou modo ou le titulaire de sa fiche de présentation
       $this->denyAccessUnlessGranted(['IS_AUTHENTICATED_FULLY']);

       // On récupère les infos du user connecté
       $currentUser = $this->getUser();

        $animal = new Animal();
        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $animal->setSlug($slugger->sluggify($animal->getTitle()));
            $animal->setUser($currentUser);

             // DEBUT Gestion picture 1
             $file = $animal->getPicture1();

            if(!is_null($file)){
                
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    
                $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                dump($e);
                }
            
                $animal->setPicture1($fileName);
            }
            else
            {
            $animal->setPicture1('default-picture.png');
            }
             // FIN Gestion picture 1

            // DEBUT Gestion picture 2
            $file = $animal->getPicture2();

            if(!is_null($file)){
                
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    
                $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                dump($e);
                }
                
                $animal->setPicture2($fileName);
            }
            // FIN Gestion picture 2

            // DEBUT Gestion picture 3
            $file = $animal->getPicture3();

            if(!is_null($file)){
                
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    
                $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                dump($e);
                }
                
                $animal->setPicture3($fileName);
            }
            // FIN Gestion picture 3

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($animal);
            $entityManager->flush();

            // Message Flash
            $this->addFlash(
                'success',
                'Votre fiche animal a correctement été crée !'
            );

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('animal/new.html.twig', [
            'animal' => $animal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/{id}", name="animal_show", methods={"GET"}, requirements={"id"="\d+"})
     * @ParamConverter("animal", options={"mapping": {"slug": "slug"}})
     * @ParamConverter("animal", options={"mapping": {"id": "id"}})
     */
    public function show($slug, $id, Animal $animal, AnimalRepository $animalRepository): Response
    {
        // On vérifie que le slug est égal au slug de la fiche animal recherchée par l'id
        if($slug !== $animalRepository->find($id)->getSlug())
        {
            throw $this->createNotFoundException('Fiche animal non trouvée');
        }
            
        return $this->render('animal/show.html.twig', [
            'animal' => $animal,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="animal_edit", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Animal $animal, Slugger $slugger): Response
    {
        ///////////////////////////////////////////////////////////////////////////
        // On récupère les anciennes images
        $oldPicture1 = $animal->getPicture1();
        if(!empty($oldPicture1)) {
            
            $animal->setPicture1(
                new File($this->getParameter('picture_directory').'/'.$oldPicture1)
            );
        }

        $oldPicture2 = $animal->getPicture2();
        if(!empty($oldPicture2)) {
           
            $animal->setPicture2(
                new File($this->getParameter('picture_directory').'/'.$oldPicture2)
            );
        }

        $oldPicture3 = $animal->getPicture3();
        if(!empty($oldPicture3)) {
            
            $animal->setPicture3(
                new File($this->getParameter('picture_directory').'/'.$oldPicture3)
            );
        }
        ///////////////////////////////////////////////////////////////////////////



        $form = $this->createForm(AnimalType::class, $animal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On resette le slug pour qu'en cas d'edit du titre
            // le slug soit modifié
            $animal->setSlug($slugger->sluggify($animal->getTitle()));

            // PICTURE 1
            // Si je souhaite setté une nouvelle image picture1
            if(!is_null($animal->getPicture1())){
                $file = $animal->getPicture1();
            
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    dump($e);
                }
                
                // Je stocke le nouveau nom de fichier
                $animal->setPicture1($fileName);
                // Si je remplace mon ancienne image par une nouvelle, je teste dans un premier temps s'il y en avait déjà un à supprimer ;)
                if(!empty($oldPicture1)){

                    // Mise en place condition pour ne pas supprimer
                    // l'image par défaut
                    if($oldPicture1 !== 'default-picture.png')
                    {
                        unlink(
                            $this->getParameter('picture_directory') .'/'.$oldPicture1
                        );
                    }

                }
            } else { // Sinon, on garde l'ancienne valeur que j'avais deja en BDD
                $animal->setPicture1($oldPicture1);//ancien nom de fichier
            }

            // PICTURE 2
            // Si je souhaite setté une nouvelle image picture2
            if(!is_null($animal->getPicture2())){
                $file = $animal->getPicture2();
            
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    dump($e);
                }
                
                // Je stocke le nouveau nom de fichier
                $animal->setPicture2($fileName);
                // Si je remplace mon ancienne image par une nouvelle, je teste dans un premier temps s'il y en avait déjà un à supprimer ;)
                if(!empty($oldPicture2)){

                    // Mise en place condition pour ne pas supprimer
                    // l'image par défaut
                    if($oldPicture2 !== 'default-picture.png')
                    {
                        unlink(
                            $this->getParameter('picture_directory') .'/'.$oldPicture2
                        );
                    }

                }
            } else { // Sinon, on garde l'ancienne valeur que j'avais deja en BDD
                $animal->setPicture2($oldPicture2);//ancien nom de fichier
            }

            // PICTURE3
            // Si je souhaite setté une nouvelle image picture3
            if(!is_null($animal->getPicture3())){
                $file = $animal->getPicture3();
            
                $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('picture_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    dump($e);
                }
                
                // Je stocke le nouveau nom de fichier
                $animal->setPicture3($fileName);
                // Si je remplace mon ancienne image par une nouvelle, je teste dans un premier temps s'il y en avait déjà un à supprimer ;)
                if(!empty($oldPicture3)){

                    // Mise en place condition pour ne pas supprimer
                    // l'image par défaut
                    if($oldPicture3 !== 'default-picture.png')
                    {
                        unlink(
                            $this->getParameter('picture_directory') .'/'.$oldPicture3
                        );
                    }

                }
            } else { // Sinon, on garde l'ancienne valeur que j'avais deja en BDD
                $animal->setPicture3($oldPicture3);//ancien nom de fichier
            }

            
            // Message Flash
            $this->addFlash(
                'success',
                'La mise à jour de votre fiche animal a bien été effectuée !'
            );

            // Enregistrement en BDD
            $this->getDoctrine()->getManager()->flush();

            // Redirection sur la fiche animal
            return $this->redirectToRoute('animal_show', [
                'slug' => $animal->getSlug(),
                'id' => $animal->getId(),
            ]);
        }

        return $this->render('animal/edit.html.twig', [
            'animal' => $animal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="animal_delete", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Animal $animal): Response
    {
        // On vérifie que l'utilisateur soit connecté
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // On récupere les informations du user connecté
        $currentUser = $this->getUser();

        // Si les données de l'user connecté sont différentes des données du user associé à la fiche animale
        // on redirige vers dashboard
        if($currentUser !== $animal->getUser())
        {
            $this->addFlash(
                'danger',
                'Vous n\'êtes pas autorisé à supprimer la fiche animal d\'un tiers !'
            );

            return $this->redirectToRoute('dashboard');
        }

        // Je récupère picture1, 2 et 3
        $picture1 = $animal->getPicture1();
        $picture2 = $animal->getPicture2();
        $picture3 = $animal->getPicture3();

        // On vérifie que le user n'a pas
        // l'image par défaut
        if($picture1 !== 'default-image.png' && !is_null($picture1))
        {
            // On supprime la picture1 (sauf s'il s'agit de celle par défaut)
            unlink(
                $this->getParameter('picture_directory') .'/'.$picture1
            );
        }

        if(!is_null($picture2))
        {
            unlink(
                $this->getParameter('picture_directory') .'/'.$picture2
            );
        }

        if(!is_null($picture3))
        {
            unlink(
                $this->getParameter('picture_directory') .'/'.$picture3
            );
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($animal);
        $em->flush();

        $this->addFlash(
            'danger',
            'Votre fiche anima a bien été supprimée !'
        );

        return $this->redirectToRoute('animal_list_index');
    }


    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

}
