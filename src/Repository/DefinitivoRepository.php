<?php

namespace App\Repository;

use App\Entity\Definitivo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Statement;
use Doctrine\DBAL\Driver\ResultStatement;
use App\Entity\Driver;
use App\Entity\Fecha;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Definitivo>
 *
 * @method Trayecto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trayecto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trayecto[]    findAll()
 * @method Trayecto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DefinitivoRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Definitivo::class);
    }

    public function add(Definitivo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Definitivo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function getTrayectoPaginator(Driver $driver, int $offset):Paginator
    {
        $query = $this->createQueryBuilder('t')            
            ->join(Fecha::class, 'f', Join::WITH, 't.fecha = f.id')
            ->andWhere('t.driver = :driver')
            ->setParameter('driver', $driver)
            ->orderBy('f.date_trayecto', 'DESC')
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
    
    public function compara(Driver $driver1, Driver $driver2): int
    {
        // Return: Count Trayectos driver1 i driver an Driver2 as passanger
        //$personal_query = "SELECT Count(t.id) as Total FROM trayecto t WHERE t.driver_id = :driver1 AND t.passenger = TRUE AND t.fecha_id IN "
        //"(SELECT k.fecha_id FROM trayecto k INNER JOIN driver d ON k.driver_id = d.id INNER JOIN fecha f ON k.fecha_id = f.id WHERE k.driver_id = :driver2 AND k.passenger = FALSE )";        
        $fields = array('t.fecha');
        $em2 = $this->createQueryBuilder('d')
            ->select('IDENTITY(d.fecha)')
            ->where('d.driver = :val1')
            ->andwhere('d.passenger = true')
            ->setparameters([
                'val1' => $driver2,
            ]);
        $query2 = $em2->getQuery()->getResult();
        //dump($query2);

        $em = $this->createQueryBuilder('d')
            ->select('COUNT(d.id) as Total')
            ->where('d.driver = :val0')
            ->andwhere('d.passenger = false')
            ->andwhere('d.fecha IN (:val1)')
            //->groupBy('t.fecha')
            ->setParameters([
                'val0' => $driver1,
                'val1' => $em2->getQuery()->getArrayResult(),
            ]);
        
        $query = $em->getQuery()->getResult();
        //dump($query);
        
        return $query[0]["Total"];
        
    }

}