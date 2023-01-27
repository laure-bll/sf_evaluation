<?php

namespace App\Controller;

use Datetime;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\PanierRepository;
use App\Form\QuantiteContenuPanierType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContenuPanierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContenuPanierController extends AbstractController
{
    #[Route('/contenu/panier', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Récupère uniquement le contenu de panier non payé de l'utilisateur connecté.
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findByEtatFalse($user),
        ]);
    }

    public function new(Request $request, PanierRepository $panierRepository, ContenuPanierRepository $contenuPanierRepository, EntityManagerInterface $em, Produit $produit): Response
    {
        // Vérifie si l'utilisateur connecté a déjà un panier.
        $panier = $em->getRepository(Panier::class)->findOneBy(["Utilisateur" => $this->getUser(), "etat" => false]);

        // Crée le panier s'il n'existe pas déjà.
        if(!$panier) {
            $panier = new Panier();
            $panier->setEtat(false);
            $panier->setUtilisateur($this->getUser());
            // $panier->setDateAchat(new Datetime());
            $panierRepository->save($panier, true);
        }

        $updateContenuPanier = null;

        // todo : limiter la quantite selon le stock du produit
        foreach($panier->getContenuPaniers() as $contenu) {
            if($contenu->getProduit() === $produit) {
                $updateContenuPanier = $contenu;
            }
        }

        // Si aucun contenu panier non payé existe, un contenu panier est créé et rempli.
        if(is_null($updateContenuPanier)) {
            $contenuPanier = new ContenuPanier();
            $contenuPanier->setDate(new Datetime());
            $contenuPanier->setPanier($panier);
            $contenuPanier->setProduit($produit);
            $contenuPanier->setQuantite($request->get("contenu_panier")["quantite"]);
        }
        else {
            // Si un contenu panier non payé existe déjà, la quantité du produit est mise à jour avec celle existante.
            $contenuPanier = $updateContenuPanier;
            $quantite = $request->get("contenu_panier")["quantite"] + $updateContenuPanier->getQuantite();
            $contenuPanier->setQuantite($quantite);
        }

        $contenuPanierRepository->save($contenuPanier, true);
        return $this->redirectToRoute("app_contenu_panier_index");
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
