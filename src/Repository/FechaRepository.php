<?php

namespace App\Repository;

use App\Entity\Fecha;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Fecha>
 *
 * @method Fecha|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fecha|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fecha[]    findAll()
 * @method Fecha[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FechaRepository extends ServiceEntityRepository
{    

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fecha::class);
    }

    public function add(Fecha $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Fecha $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Fecha[] Returns an array of Fecha objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Fecha
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
