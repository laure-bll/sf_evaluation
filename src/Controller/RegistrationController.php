<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\RegistrationFormType;
use App\Security\AuthAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AuthAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new Utilisateur();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
                if($form->isValid()) {
                // Récupère tous les utilisateurs de la bdd.
                $users = $entityManager->getRepository(Utilisateur::class)->findAll();
                
                // Le role du premier utilisateur créé sera automatiquement SUPER ADMIN.
                if(count($users) == 0) {
                    $user->setRoles(["ROLE_SUPER_ADMIN", "ROLE_ADMIN", "ROLE_USER"]);
                } else {
                    $user->setRoles(["ROLE_USER"]);
                }

                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            } else {
                // todo : trad
                $this->addFlash("danger", "Sorry, your account could not be created.");
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
