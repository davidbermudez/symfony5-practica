<?php

namespace App\Repository;

use App\Entity\Trayecto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\DriverManager;
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

     /**
     * @return Trayecto[] Returns an array of Trayecto objects
     */
    public function findOneBySomeField($value): ?Trayecto
    {
        
        $val0 = $value['driver']->getId();
        dump($value['date_trayecto']);
        $val1 = $value['date_trayecto']->format('Y-m-d');
        $val2 = $value['time_at'];//->format('H:i');
        $val3 = $value['time_to'];//->format('H:i');
        $personal_query = "SELECT * FROM trayecto t INNER JOIN driver d ON t.driver_id = d.id 
        WHERE 
            t.driver_id <> $val0 AND 
            t.date_trayecto = '$val1' AND
            t.time_at = '$val2' AND
            t.time_to = '$val3' AND
            d.grupo_id = (SELECT e.grupo_id FROM driver e WHERE e.id = $val0);";
        /*
        $conn = DriverManager::getConnection($params, $config);
        $st = $con->query($personal_query);
        $rs = $st->fetchAll();
        */
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        //$query = "[colocas aqui tu query]";
        $consulta = $conn->prepare($personal_query);
        $consulta->execute(); 
        $queryResults = $consulta->fetchAll();
        /*
        $query = $this->QueryBuilder('t')
            ->select('t.id', 't.driver_id', 't.date_trayecto', 't.time_at', 't.time_to', 't.passenger')
            //->select('t')
            ->from('trayecto', 't')
            ->innerJoin('driver', 'd', 't.driver_id = d.id')
            ->where('t.driver != :val0 AND t.date_trayecto = :val1 AND t.time_at = :val2 AND t.time_to = :val3 AND d.grupo_id = (SELECT e.grupo_id FROM driver e WHERE e.if = :val0)')
            //->andWhere('t.date_trayecto = :val1')
            //->andWhere('t.time_at = :val2')
            //->andWhere('t.time_to = :val3')
            //->andWhere('d.grupo_id = (SELECT e.grupo_id FROM driver e WHERE e.if = :val0)')
            ->setParameters([
                'val0' => $value['driver'],
                'val1' => $value['date_trayecto'],
                'val2' => $value['time_at'],
                'val3' => $value['time_to'],
            ])
            ->getQuery()
            //->getResult()
        ;
        dump($query->getSQL());
        /*
        $query = $this->getEntityManager()
            ->createQuery("SELECT * FROM trayecto t INNER JOIN driver d ON t.driver_id = d.id 
            WHERE 
                t.driver_id <> :val0 AND 
                t.date_trayecto = :val1 AND
                t.time_at = :val2 AND
                t.time_to = :val3 AND
                d.grupo_id = (SELECT e.grupo_id FROM driver e WHERE e.id = :val0);
            ")
            ->setParameters([
                'val0' => $value['driver'],
                'val1' => $value['date_trayecto'],
                'val2' => $value['time_at'],
                'val3' => $value['time_to'],
            ]);
            */
        return $queryResults;
        //return new Traslado($query);
    }
}