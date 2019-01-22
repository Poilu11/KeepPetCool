<?php

namespace App\Controller;

use App\Entity\Species;
use App\Form\SpeciesType;
use App\Repository\SpeciesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/species")
 */
class SpeciesController extends AbstractController
{
    /**
     * @Route("/", name="species_index", methods={"GET"})
     */
    public function index(SpeciesRepository $speciesRepository): Response
    {
        return $this->render('species/index.html.twig', [
            'species' => $speciesRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="species_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $species = new Species();
        $form = $this->createForm(SpeciesType::class, $species);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($species);
            $entityManager->flush();

            return $this->redirectToRoute('species_index');
        }

        return $this->render('species/new.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="species_edit", methods={"GET","POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, Species $species): Response
    {
        $form = $this->createForm(SpeciesType::class, $species);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('species_index', [
                'id' => $species->getId(),
            ]);
        }

        return $this->render('species/edit.html.twig', [
            'species' => $species,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="species_delete", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function delete(Request $request, Species $species): Response
    {
        // On vérifie que l'utilisateur soit administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // dd($species);

        $em = $this->getDoctrine()->getManager();
        $em->remove($species);
        $em->flush();

        $this->addFlash(
            'success',
            'Catégorie d\'espèce supprimée avec succès !'
        );


        return $this->redirectToRoute('species_index');
    }
}