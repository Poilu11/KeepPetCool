<?php

namespace App\Repository;

use App\Entity\Presentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Presentation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Presentation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Presentation[]    findAll()
 * @method Presentation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PresentationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Presentation::class);
    }

    public function findAllPresentationsByUserType($type){

        $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('presentation.createdAt', 'ASC')
            ->setParameter('type', $type)
            ->where('user.type =:type')
            ->getQuery();
        
        return $qb->execute();

    }

    public function findActivePresentationsByUserType($type){

        $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('presentation.createdAt', 'ASC')
            ->setParameter('type', $type)
            ->where('presentation.isActive = true')
            ->andWhere('user.type =:type')
            ->getQuery();
        
        return $qb->execute();

    }


    // /**
    //  * @return Presentation[] Returns an array of Presentation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Presentation
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
