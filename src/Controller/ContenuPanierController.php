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
use Symfony\Contracts\Translation\TranslatorInterface;


#[Route('{_locale}')]
class ContenuPanierController extends AbstractController
{
    #[Route('/contenu/panier', name: 'app_contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Récupère uniquement le contenu de panier non payé de l'utilisateur connecté.
        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findByUserEtat($user, false),
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

    public function delete($id, Request $request, ContenuPanier $contenuPanier, ContenuPanierRepository $contenuPanierRepository): Response
    { 
        //bouton delete pour supprimer un produit d'un panier
        if ($this->isCsrfTokenValid('delete'.$contenuPanier->getId(), $request->request->get('_token'))) {
           $contenuPanierRepository->remove($contenuPanier, true);
        }

        return $this->redirectToRoute('app_contenu_panier_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/contenu/panier/{id}/buy', name: 'app_contenu_panier_buy', methods: ['PUT','GET'])]
    public function buy(int $id, Request $request, EntityManagerInterface $em): Response
    {

        $panier = $em->getRepository(Panier::class)->find($id);
        $contenuPersist = null;
        foreach($panier->getContenuPaniers() as $contenu) {
            // Décrementation du stock en faisant la différence entre le stock et la quantité lors de l'achat
            $quantite =  $contenu->getQuantite();
            $stock = $contenu->getProduit()->getStock();

            if($stock - $quantite >= 0) {
                $contenu->getProduit()->setStock($stock - $quantite);
                $contenuPersist = $em->persist($contenu);
            } else {
                $error = 'Error Panier.';
                return $this->redirectToRoute('app_contenu_panier_index', ['error' => $error], Response::HTTP_SEE_OTHER);
            }
        }
           // mise à jour de l'etat du panier
        $panier->setEtat(true);
        $panier->setDateAchat(new DateTime());
        $em->persist($contenu);
        $em->flush();

        return $this->redirectToRoute('app_contenu_panier_index',[], Response::HTTP_SEE_OTHER);
    }



  



}