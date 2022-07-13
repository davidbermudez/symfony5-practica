<?php

namespace App\Repository;

use App\Entity\DriverConsent;
use App\Entity\Driver;
use App\Entity\Consent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;

/**
 * @extends ServiceEntityRepository<DriverConsent>
 *
 * @method DriverConsent|null find($id, $lockMode = null, $lockVersion = null)
 * @method DriverConsent|null findOneBy(array $criteria, array $orderBy = null)
 * @method DriverConsent[]    findAll()
 * @method DriverConsent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DriverConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DriverConsent::class);
    }

    public function add(DriverConsent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DriverConsent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return DriverConsent[] Returns an array of DriverConsent objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DriverConsent
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findConsentPending($value): array
    {
        
        //dump($value);
        //dump($value['driver']);
        $em = $this->createQueryBuilder('d')
            ->select('d')
            ->join(Consent::class, 'c', Join::WITH, 'd.consent = c.id')
            ->where('c.enable = true')
            ->andwhere('d.driver != :val0')
            ->andwhere('d.choice is null')
            ->setParameters([
                'val0' => $value['driver'],                
            ]);        
        $q = $em->getQuery()->getSQL();        
        $query = $em->getQuery()->getArrayResult();
        dump($query);
        return $query;
    }
}
