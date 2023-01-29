<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UtilisateurController extends AbstractController
{
    #[Route('/super/admin/utilisateur/{id}/edit', name: 'app_role_edit', methods: ['POST'])]
    public function edit(UtilisateurRepository $utilisateurRepository, Utilisateur $utilisateur, Request $request): Response
    {
        if ($this->isCsrfTokenValid('edit'.$utilisateur->getId(), $request->request->get('_token'))) {
            $admin = boolval($request->request->get('_admin'));

            // Récupère les roles de l'utilisateur.
            $roles = $utilisateur->getRoles();

            // Vérifie si le role admin doit lui être attribué ou retiré.
            if($admin) {
                // Evite les doublons en vérifiant que l'utilisateur n'a pas déjà le role admin.
                if(!in_array("ROLE_ADMIN", $roles)) {
                    array_push($roles, "ROLE_ADMIN");
                }
            } else {
                // Récupère le role admin afin de pouvoir le retirer.
                if(in_array("ROLE_ADMIN", $roles)) {
                    $index = array_search("ROLE_ADMIN", $roles);
                    array_splice($roles, $index);
                }
            }

            // Sauvegarde le changement en bdd.
            $utilisateur->setRoles($roles);
            $utilisateurRepository->save($utilisateur, true);
        }
        
        // Retourne sur le tableau de bord du super admin.
        return $this->redirectToRoute('app_super_admin');
    }

    #[Route('/utilisateur/{id}/update', name: 'app_utilisateur_update', methods: ['GET', 'POST'])]
    public function update(UtilisateurRepository $utilisateurRepository, Utilisateur $utilisateur, Request $request): Response
    {
        $form = $this->createForm(UtilisateurType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if($form->isValid()) {
                $utilisateurRepository->save($utilisateur, true);

                return $this->redirectToRoute('app_panier_index');
            }
            else {
                dd($form);
            }
        }

        return $this->renderForm('utilisateur/update.html.twig', [
            'utilisateur' => $utilisateur,
            'form' => $form,
        ]);
    }
}
