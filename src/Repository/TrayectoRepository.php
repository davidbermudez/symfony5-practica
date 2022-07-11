<?php

namespace App\Repository;

use App\Entity\Trayecto;
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
            ->join(Fecha::class, 'f', Join::WITH, 't.fecha = f.id')
            ->andWhere('t.driver = :driver')
            ->andWhere('t.confirm = true')
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

    /**
    * @return Trayecto[] Returns an array of Trayecto objects
    */
    public function findTrayectos2($value): array
    {
        // Return: Trayecto(s) same date_trayecto, same time_at, same time_to, same grupo, distinct driver
        //dump($value);
        //dump($value['driver']);
        $em = $this->createQueryBuilder('t')
            ->select('t')
            ->join(Driver::class, 'd', Join::WITH, 't.driver = d.id')
            ->where('t.driver != :val0')
            ->andwhere('t.date_trayecto = :val1')
            ->andwhere('t.time_at = :val2')
            ->andwhere('t.time_to = :val3')
            ->andwhere('d.grupo = :val4')
            ->setParameters([
                'val0' => $value['driver'],
                'val1' => $value['date_trayecto'],
                'val2' => $value['time_at'],
                'val3' => $value['time_to'],
                'val4' => $value['grupo'],
            ]);
        //$gem = $em->getEntityManager();
        //dump($gem);
        //$dql = $em->getDql();
        //dump($dql);
        $q = $em->getQuery()->getSQL();
        //dump($q);
        $query = $em->getQuery()->getArrayResult();
        //dump($query);
        return $query;
    }

    /**
    * @return Trayecto[] Returns an array of Trayecto objects
    */
    public function findTrayectos3($value): array
    {
        // Return: Trayecto(s) same date_trayecto, same time_at, same time_to, same grupo, distinct driver, and distinct id trayecto        
        $em = $this->createQueryBuilder('t')
            ->select('t')
            ->join(Driver::class, 'd', Join::WITH, 't.driver = d.id')
            ->where('t.driver != :val0')
            ->andwhere('t.date_trayecto = :val1')
            ->andwhere('t.time_at = :val2')
            ->andwhere('t.time_to = :val3')
            ->andwhere('d.grupo = :val4')
            ->andwhere('t.id != :val5')
            ->setParameters([
                'val0' => $value['driver'],
                'val1' => $value['date_trayecto'],
                'val2' => $value['time_at'],
                'val3' => $value['time_to'],
                'val4' => $value['grupo'],
                'val5' => $value['exclude'],
            ]);
        $query = $em->getQuery()->getResult();
        return $query;
    }
    
    public function findAvailables($value): array
    {
        // Return: Trayecto(s) for date_trayecto >= now, same grupo
        $em = $this->createQueryBuilder('t')
            //->select('t', 'd.id')
            ->join(Fecha::class, 'f', Join::WITH, 't.fecha = f.id')
            ->join(Driver::class, 'd', Join::WITH, 't.driver = d.id')
            //->where('t.driver != :val0')
            //->andwhere('f.date_trayecto >= :val1')
            ->where('t.confirm is null')
            ->andwhere('d.grupo = :val2')
            ->orderBy('f.date_trayecto', 'ASC')
            ->addOrderBy('f.time_at', 'ASC')
            ->addOrderBy('t.driver', 'ASC')
            ->setMaxResults(10)
            //->orderBy('f.time_at', 'ASC')
            //->groupBy('t.fecha')
            ->setParameters([
                //'val0' => $value['driver'],
                //'val1' => $value['date_trayecto'],                
                'val2' => $value['grupo'],
            ]);
        //$query = $em->getQuery()->getArrayResult();
        //$query = $em->getQuery()->getResult();
        //$query = $em->getQuery();
        //dump($em->getQuery()->getSQL());
        $query = $em->getQuery()->getResult();
        //dump($query);
        //return $query;
        
        //return new Trayecto($query->getQuery());
        $return = [];
        $i = 0;
        foreach($query as $element){
            $localizado = false;
            $fecha = $element->getFecha();
            $r = 0;
            foreach($return as $bucle){
                if($bucle["fecha"] == $fecha){
                    //dump("Repetido");
                    $localizado = true;
                }
                $r++;
            }
            if(!$localizado){
                $return[$i]["trayecto_id"] = $element->getId();
                $return[$i]["fecha"] = $element->getFecha();

                $return[$i]["passenger"] = [];
                $return[$i]["driver"] = [];
                array_push($return[$i]["passenger"], $element->isPassenger());                
                array_push($return[$i]["driver"], $element->getDriver());
                $j = $i;
                $i++;
            } else {
                array_push($return[$j]["passenger"], $element->isPassenger());
                array_push($return[$j]["driver"], $element->getDriver());
            }
        }        

        //dump($return);
        //return $query;
        return $return;
        
    }

    public function compara(Driver $driver1, Driver $driver2): int
    {
        // Return: Count Trayectos driver1 i driver an Driver2 as passanger        
        //$personal_query = "SELECT Count(t.id) as Total FROM trayecto t WHERE t.driver_id = :driver1 AND t.passenger = TRUE AND t.fecha_id IN "
        //"(SELECT k.fecha_id FROM trayecto k INNER JOIN driver d ON k.driver_id = d.id INNER JOIN fecha f ON k.fecha_id = f.id WHERE k.driver_id = :driver2 AND k.passenger = FALSE )";        
        $fields = array('t.fecha');
        $em2 = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.fecha)')
            ->where('t.driver = :val1')
            ->andwhere('t.confirm = true')
            ->andwhere('t.passenger = true')
            ->setparameters([
                'val1' => $driver2,
            ]);
        $query2 = $em2->getQuery()->getResult();
        //dump($query2);

        $em = $this->createQueryBuilder('t')
            ->select('COUNT(t.id) as Total')
            ->where('t.driver = :val0')
            ->andwhere('t.passenger = false')
            ->andwhere('t.fecha IN (:val1)')
            //->groupBy('t.fecha')
            ->setParameters([
                'val0' => $driver1,
                'val1' => $em2->getQuery()->getArrayResult(),
            ]);
        
        $query = $em->getQuery()->getResult();
        //dump($query);
        
        return $query[0]["Total"];   
    }


    public function pasajero_de(Driver $driver1, Driver $driver2): array
    {
        $fields = array('t.fecha');
        $em2 = $this->createQueryBuilder('t')
            ->select('IDENTITY(t.fecha)')
            ->where('t.driver = :val1')
            ->andwhere('t.confirm = true')
            ->andwhere('t.passenger = true')
            ->setparameters([
                'val1' => $driver1,
            ]);
        $query2 = $em2->getQuery()->getResult();
        //dump($query2);

        $em = $this->createQueryBuilder('t')
            //->select('COUNT(t.id) as Total')
            ->select('t')
            ->where('t.driver = :val0')
            ->andwhere('t.passenger = false')
            ->andwhere('t.fecha IN (:val1)')
            //->groupBy('t.fecha')
            ->setParameters([
                'val0' => $driver2,
                'val1' => $em2->getQuery()->getArrayResult(),
            ]);            
        
        $query = $em->getQuery()->getResult();
        //dump($query);
        
        return $query;
    }

}