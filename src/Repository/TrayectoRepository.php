<?php

namespace App\Repository;

use App\Entity\Trayecto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use App\Entity\Driver;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Trayecto>
 *
 * @method Trayecto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trayecto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trayecto[]    findAll()
 * @method Trayecto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrayectoRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trayecto::class);
    }

    public function add(Trayecto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trayecto $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTrayectoPaginator(Driver $driver, int $offset):Paginator
    {
        $query = $this->createQueryBuilder('t')
            ->andWhere('t.driver = :driver')
            ->setParameter('driver', $driver)
            ->orderBy('t.date_trayecto', 'DESC')
            ->setMaxResults(self::PAGINATOR_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;
        return new Paginator($query);
    }

//    /**
//     * @return Trayecto[] Returns an array of Trayecto objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Trayecto
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
