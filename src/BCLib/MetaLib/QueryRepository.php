<?php

namespace BCLib\MetaLib;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class QueryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
    }

    public function sendCategoriesQuery($id = null, $ttl = 3600)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('Category', 'Subcategory', 'Resource')
            ->from('\BCLib\MetaLib\Models\Category', 'Category')
            ->leftJoin('Category._subcategories', 'Subcategory')
            ->leftJoin('Subcategory._resources', 'Resource')
            ->orderBy('Category._name', 'ASC')
            ->addOrderBy('Subcategory._name', 'ASC')
            ->addOrderBy('Resource._name', 'ASC');
        if (!is_null($id)) {
            $qb->where($qb->expr()->eq('Category._name', ':id'))
                ->setParameter('id', $id);
        }
        return $this->_sendQuery($qb, $ttl);
    }

    /**
     * @param array $params possible values:
     *                      ['id' => resource number,
     *                      'name' => resource name,
     *                      'first_letter' => first letter(s) in name]
     *
     * @param int   $ttl    Query cache time-to-live
     *
     * @return array
     */
    public function sendResourcesQuery($params, $ttl = 3600)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('Resource')
            ->from('\BCLib\MetaLib\Models\Resource', 'Resource')
            ->orderBy('Resource._name', 'ASC');

        foreach ($params as $key => $value) {
            switch ($key) {
                case 'id':
                    $qb->where($qb->expr()->eq('Resource._number', ':id'))
                        ->setParameter(':id', $value);
                    break;
                case 'first_letter':
                    $qb->where($qb->expr()->like('Resource._name', ':letter'))
                        ->setParameter(':letter', "$value%");
                    break;
                case 'name':
                    $qb->where($qb->expr()->like('Resource._name', ':name'))
                        ->setParameter(':name', "%$value%");
                    break;
            }
        }

        return $this->_sendQuery($qb, $ttl);
    }

    protected function _setQueryCache(Query $query, $ttl)
    {
        if (!is_null($this->_em->getConfiguration()->getQueryCacheImpl())) {
            $query->useResultCache(true, $ttl);
        }
        return $query;
    }

    protected function _sendQuery(QueryBuilder $qb, $ttl)
    {
        $query = $qb->getQuery();
        $query = $this->_setQueryCache($query, $ttl);
        return $query->getResult();
    }
} 