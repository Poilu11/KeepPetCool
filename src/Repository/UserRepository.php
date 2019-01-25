<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserNear($type, $lat, $long, $radius)
    {
        return $this->createQueryBuilder('u')
            ->where('u.type = :type')
            ->setParameter('type', $type)
            ->andWhere('SQRT( (:lat - u.latitude)*(:lat - u.latitude)*111*111 + (:long - u.longitude)*(:long - u.longitude)*111*111) < :radius')
            ->orderBy('SQRT( (:lat - u.latitude)*(:lat - u.latitude)*111*111 + (:long - u.longitude)*(:long - u.longitude)*111*111)','ASC')
            ->setParameter('lat', $lat)
            ->setParameter('long', $long)
            ->setParameter('radius', $radius)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
