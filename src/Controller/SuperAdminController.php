<?php

namespace App\Controller;

use Datetime;
use App\Entity\Panier;
use App\Entity\Utilisateur;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('{_locale}')]
class SuperAdminController extends AbstractController
{
    #[Route('/super/admin', name: 'app_super_admin')]
    public function index(EntityManagerInterface $em): Response
    {
        $unpaid_paniers = $em->getRepository(Panier::class)->findByEtat(false);

        $date = new Datetime();
        $new_utilisateurs = $em->getRepository(Utilisateur::class)->findByDate($date->format("Y-m-d"));

        return $this->render('super_admin/index.html.twig', [
            'unpaid_paniers' => $unpaid_paniers,
            'utilisateurs' => $new_utilisateurs
        ]);
    }
}
