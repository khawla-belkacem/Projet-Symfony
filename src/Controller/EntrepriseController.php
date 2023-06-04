<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Entreprise;

class EntrepriseController extends AbstractController
{
    /**
     * @Route("/entreprise", name="app_entreprise")
     */
    public function index(): Response
    {
        {
            $entityManager = $this->getDoctrine()->getManager();
            //ajout entreprise
            $entreprise = new entreprise();
            $entreprise->setTitre('khawla');
            $entreprise->setEmail('khawla@mail.com');
            $entreprise->setSpecialite('informatique');
            $entreprise->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($entreprise);
            $entityManager->flush();
            return $this->render('entreprise/index.html.twig', [
                'id'=>$entreprise->getId()
            ]);
        }}
        /**
         * @Route("/entreprise/{id}", name="entreprise_show")
         */
        public function show($id)
    {
        $entreprise = $this->getDoctrine()
            ->getRepository(Entreprise::class)
            ->find($id);
        if (!$entreprise) {
            throw $this->createNotFoundException(
                'No entreprise found for id '.$id
            );
        }
        $ent=$this->getDoctrine()->getManager();


        return $this->render('entreprise/show.html.twig', [
            'entreprise' =>$entreprise,

        ]);
    }}
