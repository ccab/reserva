<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class ProductoRepository extends EntityRepository
{
    public function queryBySearchParams($data)
    {
        $query = $this->createQueryBuilder('p');

        if (isset($data['codigo'])) {
            $query->andWhere('p.codigo = :codigo')
                ->setParameter('codigo', $data['codigo']);
        }


        return $query;
    }

    public function findBySearchParams($page = 1, $data)
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($this->queryBySearchParams($data), false));
        $paginator->setMaxPerPage(10);
        $paginator->setCurrentPage($page);

        return $paginator;
    }
}
