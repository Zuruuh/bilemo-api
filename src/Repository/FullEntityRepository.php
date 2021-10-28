<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Exception;

/**
 * @method Entity[]|null findByCursor(int $cursor)
 * @method [Entity]|null findOneByWithArray(array $params)
 */
abstract class FullEntityRepository extends ServiceEntityRepository
{
    const LIMIT = null;

    const INVALID_LIMIT = "Entity pagination limit is set to null. Did you create a 'LIMIT' constant in your %s class ?";

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    /**
     * Returns a set amount of the set entity based on passed cursor
     * 
     * @param int $cursor The current cursor
     * 
     * @return Entity[]|null
     */
    public function findByCursor(int $cursor): array|null
    {
        $limit = static::LIMIT;
        if (!$limit) {
            throw new Exception(
                sprintf(self::INVALID_LIMIT, static::class)
            );
        }
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($cursor)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Returns an entity as an associative array based on custom params
     * 
     * @param array $params The parameters to pass to the query
     * 
     * @return [Entity]|null
     */
    public function findOneByWithArray(array $params): array|null
    {
        $qb = $this->createQueryBuilder('e');

        $i = 0;
        foreach ($params as $key => $param) {
            $qb
                ->andWhere("e.$key = :param$i")
                ->setParameter(":param$i", $param);
            ++$i;
        }
        return $qb->getQuery()->getArrayResult();
    }
}
