<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('{_locale}')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/produit/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository, SluggerInterface $slugger, TranslatorInterface $t): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photoFile = $form->get('photo')->getData();

            // Upload de la photo.
            if ($photoFile) {
                // Renomme le fichier.
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    // Stocke le fichier dans le dossier indiqué.
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash("danger", $t->trans('photo.incorrect'));
                }

                // Sauvegarde le nom du fichier en base de donnée.
                $produit->setPhoto($newFilename);
                $produitRepository->save($produit, true);
            }

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/produit/{id}', name: 'app_produit_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Produit $produit): Response
    {
        $contenuPanier = new ContenuPanier();
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Crée ou modifie le contenu panier non payé et crée un nouveau panier si besoin.
            return $this->forward('App\Controller\ContenuPanierController::new', [
                'produit' => $produit,
                'request' => $request
            ]);
        }

        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'form_contenu_panier' => $form,
     
        ]);
    }


    //ci dessous nous avons essayé (et pas réussi) à créer un dropdown allant de 1 jusqu'à la quantité du stock disponible.
    #[Route('/produit/{id}/add', name: 'app_produit_add', methods: ['GET', 'POST'])]
    public function add(Request $request, Produit $produit): Response
    {
        $array = array();
        for ($x = 1; $x <= $produit->getStock(); $x++)
       {
          array_push($array, $x); }
      
        $form = $this->createFormBuilder() 
            ->add('quantite', EntityType::class, [
           'class' => Produit::class, 
           'choice_label'=> 'stock'])
           ->getForm();
       
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'form_add' => $form,
        ]);
    }





    #[Route('/produit/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository, SluggerInterface $slugger): Response
    {
        //crée un formulaire pour editer les données dejà entrées sur le produit
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $produitRepository->save($produit, true);

            $photoFile = $form->get('photo')->getData();

            // Upload de la photo.
            if ($photoFile) {
                // Renomme le fichier.
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    // Stocke le fichier dans le dossier indiqué.
                    $photoFile->move(
                        $this->getParameter('photos_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash("danger", $t->trans('photo.incorrect'));
                }

                // Sauvegarde le nom du fichier en base de donnée.
                $produit->setPhoto($newFilename);
            }

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/produit/{id}/delete', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
