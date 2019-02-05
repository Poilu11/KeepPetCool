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

    public function findAllPresentations($active = null){

        if($active !== null)
        {
            $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('user.connectedAt', 'DESC')
            ->setParameter('active', $active)
            ->where('presentation.isActive =:active')
            ->getQuery();
        }
        else
        {
            $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('user.connectedAt', 'DESC')
            ->getQuery();
        }

        return $qb->execute();

    }

    public function findAllPresentationsByUserType($type, $active = null){

        if($active !== null)
        {
            $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('user.connectedAt', 'DESC')
            ->setParameter('type', $type)
            ->setParameter('active', $active)
            ->where('user.type =:type')
            ->andwhere('presentation.isActive =:active')
            ->getQuery();

        }
        else
        {
            $qb = $this->createQueryBuilder('presentation')
            ->innerJoin('presentation.user', 'user')
            ->orderBy('user.connectedAt', 'DESC')
            ->setParameter('type', $type)
            ->where('user.type =:type')
            ->getQuery();

        }
        
        return $qb->execute();

    }

    // public function findActivePresentationsByUserType($type){

    //     $qb = $this->createQueryBuilder('presentation')
    //         ->innerJoin('presentation.user', 'user')
    //         ->orderBy('presentation.user.connectedAt', 'DESC')
    //         ->setParameter('type', $type)
    //         ->where('presentation.isActive = true')
    //         ->andWhere('user.type =:type')
    //         ->getQuery();
        
    //     return $qb->execute();

    // }

    /*
    public function findPresByUserNear($type, $lat, $long, $radius)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.type = :type')
            ->setParameter('type', $type)
            ->andWhere('SQRT( (:lat - u.latitude)*(:lat - u.latitude)*111*111 + (:long - u.longitude)*(:long - u.longitude)*111*111) < :radius')
            ->orderBy('SQRT( (:lat - u.latitude)*(:lat - u.latitude)*111*111 + (:long - u.longitude)*(:long - u.longitude)*111*111)','ASC')
            ->setParameter('lat', $lat)
            ->setParameter('long', $long)
            ->setParameter('radius', $radius)
            ->getQuery()
            ->getResult();
    }
    */


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
