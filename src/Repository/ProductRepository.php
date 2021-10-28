<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    const LIMIT = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Returns a set amount of products based on a set cursor
     * 
     * @param int $cursor The current cursor
     * 
     * @return Product[]|null
     */
    public function findByCursor(int $cursor): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(self::LIMIT)
            ->setFirstResult($cursor)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns a product as an associative array based on custom params
     * 
     * @param array $params The parameters to pass to the query
     * 
     * @return Product|null
     */
    public function findOneByWithArray(array $params): array
    {
        $qb = $this->createQueryBuilder('p');

        $i = 0;
        foreach ($params as $key => $param) {
            $qb
                ->andWhere("p.$key = :param$i")
                ->setParameter(":param$i", $param);
            ++$i;
        }
        return $qb->getQuery()->getArrayResult();
    }

    // /**
    //  * @return Product[] Returns an array of Product objects
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
    public function findOneBySomeField($value): ?Product
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
