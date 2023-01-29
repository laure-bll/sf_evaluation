<?php

namespace App\Repository;

use App\Entity\ContenuPanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContenuPanier>
 *
 * @method ContenuPanier|null find($id, $lockMode = null, $lockVersion = null)
 * @method ContenuPanier|null findOneBy(array $criteria, array $orderBy = null)
 * @method ContenuPanier[]    findAll()
 * @method ContenuPanier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContenuPanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContenuPanier::class);
    }

    public function save(ContenuPanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContenuPanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * @return ContenuPanier[] Returns an array of ContenuPanier objects's connected user from Panier etat false
    */
   public function findByUserEtat($user, $etat): array
   {
       return $this->createQueryBuilder('c')
            ->leftJoin('c.panier', 'panier')
            ->andWhere('panier.Utilisateur = :u')
            ->setParameter('u', $user)
            ->andWhere('panier.etat = :e')
            ->setParameter('e', $etat)
            ->getQuery()
            ->getResult()
       ;
   }

    /**
    * @return ContenuPanier[] Returns an array of all ContenuPanier objects
    */
    public function findByEtat($etat): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.panier', 'panier')
            ->andWhere('panier.etat = :e')
            ->setParameter('e', $etat)
            ->getQuery()
            ->getResult()
        ;
    }
}
