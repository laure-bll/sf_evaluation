<?php

namespace App\Controller;

use Datetime;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContenuPanierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContenuPanierController extends AbstractController
{
    #[Route('/contenu/panier', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository): Response
    {
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findAll(),
        ]);
    }

    public function new(Request $request, PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository, EntityManagerInterface $em, Produit $produit): Response
    {
        // Vérifie si l'utilisateur a déjà un panier.
        $panier = $em->getRepository(Panier::class)->findOneBy(["Utilisateur" => $this->getUser()]);

        // Crée le panier s'il n'existe pas déjà.
        if(!$panier) {
            $panier = new Panier();
            $panier->setEtat(false);
            $panier->setUtilisateur($this->getUser());
            // $panier->setDateAchat(new Datetime());
            $panierRepository->save($panier, true);

        }

        $contenuPanier = new ContenuPanier();
        $contenuPanier->setDate(new Datetime());
        $contenuPanier->setPanier($panier);
        $contenuPanier->setProduit($produit);
        $contenuPanier->setQuantite($request->get("contenu_panier")["quantite"]);
        $contenuPanierRepository->save($contenuPanier, true);

        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/contenu/panier/{id}', name: 'app_contenu_panier_show', methods: ['GET'])]
    public function show(ContenuPanier $contenuPanier): Response
    {
        return $this->render('contenu_panier/show.html.twig', [
            'contenu_panier' => $contenuPanier,
        ]);
    }

    #[Route('/contenu/panier/{id}/edit', name: 'app_contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contenuPanierRepository->save($contenuPanier, true);

            return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }

    #[Route('/contenu/panier/{id}', name: 'app_contenu_panier_delete', methods: ['POST'])]
    public function delete(Request $request, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contenuPanier->getId(), $request->request->get('_token'))) {
            $contenuPanierRepository->remove($contenuPanier, true);
        }

        return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
