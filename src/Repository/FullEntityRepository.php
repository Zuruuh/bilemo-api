<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * @method Entity[]|null findByCursor(int $cursor)
 * @method [Entity]|null findOneByWithArray(array $params)
 */
abstract class FullEntityRepository extends ServiceEntityRepository
{
    public const CURSOR_PAGINATION_LIMIT = null;
    public const ENTITY = 'entity';

    public const INVALID_LIMIT = "Entity pagination limit is set to null. Did you create a 'CURSOR_PAGINATION_LIMIT' constant in your %s class ?";

    /**
     * Returns a set amount of the set entity based on passed cursor
     * 
     * @param int   $cursor The current cursor
     * @param array $params The query parameters
     * 
     * @return Entity[]|null
     */
    public function findByCursor(int $cursor, array $params = []): array|null
    {
        $limit = static::CURSOR_PAGINATION_LIMIT;
        if (!$limit) {
            throw new Exception(
                sprintf(self::INVALID_LIMIT, static::class)
            );
        }
        $qb = $this->applyQueryBuilderParams(
            $this->createQueryBuilder(static::ENTITY),
            $params
        );

        $query = $qb->orderBy(static::ENTITY . '.id', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult($cursor)
            ->getQuery();

        return $query->getArrayResult();
        //->getArrayResult();
    }

    /**
     * Returns an entity as an associative array based on custom params
     * 
     * @param array $params The parameters to pass to the query
     * 
     * @return array<Entity>|null
     */
    public function findOneByWithArray(array $params)
    {
        $qb = $this->applyQueryBuilderParams(
            $this->createQueryBuilder(static::ENTITY),
            $params
        );

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Applies an array of params to a query builder
     * 
     * @param QueryBuilder $qb     The query builder to apply the params to
     * @param array        $params The params to apply to the query builder
     * 
     * @return QueryBuilder
     */
    private function applyQueryBuilderParams(QueryBuilder $qb, array $params): QueryBuilder
    {
        $i = 0;
        foreach ($params as $key => $param) {
            $qb
                ->andWhere(static::ENTITY . ".$key = :param$i")
                ->setParameter(":param$i", $param);
            ++$i;
        }

        return $qb;
    }
}
