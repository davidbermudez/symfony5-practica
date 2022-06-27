<?php

namespace App\Repository;

use App\Entity\Trayecto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Join;
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
    public function findTrayectos2($value): array
    {
        // Return: Trayecto(s) same date_trayecto, same time_at, same time_to, same grupo, distinct driver
        dump($value);
        dump($value['driver']);
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
        dump($q);
        $query = $em->getQuery()->getArrayResult();
        dump($query);
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
        //$gem = $em->getEntityManager();
        //dump($gem);
        //$dql = $em->getDql();
        //dump($dql);
        //$q = $em->getQuery()->getSQL();
        //dump($q);
        //$query = $em->getQuery()->getArrayResult();
        $query = new Trayecto;
        $query = $em->getQuery()->getResult();

        dump($query);
        //$query2 = $em->getQuery()->getArrayResult();
        //dump($query2);

        return $query;
    }
    
    public function findAvailables($value): array
    {
        // Return: Trayecto(s) and email driver for date_trayecto >= now, same grupo, distinct driver
        $em = $this->createQueryBuilder('t')
            ->select('t', 'd.id')
            ->join(Driver::class, 'd', Join::WITH, 't.driver = d.id')
            ->where('t.driver != :val0')
            ->andwhere('t.date_trayecto >= :val1')            
            ->andwhere('d.grupo = :val2')            
            ->setParameters([
                'val0' => $value['driver'],
                'val1' => $value['date_trayecto'],                
                'val2' => $value['grupo'],
            ]);
        $query = $em->getQuery()->getArrayResult();
        //$query = $em->getQuery()->getResult();
        //$query = $em->getQuery();
        dump($em->getQuery()->getSQL());
        $return = [];
        $i = 0;        
        foreach($query as $element){
            $localizado = false;
            foreach($element as $key){                
                if(gettype($key)=="array"){
                    // es un trayecto
                    // Comprobar si ya está
                    $date_trayecto = $key["date_trayecto"];
                    $r = 0;
                    foreach($return as $bucle){
                        if($date_trayecto == $bucle["date_trayecto"]){
                            if($key["time_at"] == $return[$r]["time_at"] &&
                            $key["time_to"] == $return[$r]["time_to"]){
                                $localizado = true;                                
                            }
                        }
                        $r++;
                    }
                    if(!$localizado){
                        $return[$i]["id"] = $key["id"];
                        $return[$i]["date_trayecto"] = $key["date_trayecto"];
                        $return[$i]["time_at"] = $key["time_at"];
                        $return[$i]["time_to"] = $key["time_to"];
                        $return[$i]["drivers"] = [];                        
                        $j = $i;
                    } else {
                        $j = 0;
                    }
                    // solo sumamos cuando procesamos el array
                    $i++;
                } elseif (gettype($key)=="integer") {                    
                    array_push($return[$j]["drivers"], $key);
                }
                dump($j, $key);                
            }
        }

        dump($return);
        //return $query;
        return $return;
    }

}