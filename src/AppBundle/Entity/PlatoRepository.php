<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class PlatoRepository extends EntityRepository
{
    public function queryBySearchParams($data)
    {
        $query = $this->createQueryBuilder('p');

        if (isset($data['nombre'])) {
            $query->andWhere('p.nombre = :nombre')
                ->setParameter('nombre', $data['nombre']);
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
