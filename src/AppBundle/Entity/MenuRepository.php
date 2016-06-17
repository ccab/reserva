<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class MenuRepository extends EntityRepository
{
    public function queryBySearchParams($data)
    {
        $query = $this->createQueryBuilder('m');
            
        if (isset($data['tipo'])) {
            $query->join('m.tipoMenu', 't')
                ->where('t.id = :id')
                ->setParameter('id', $data['tipo']);
        }
        
        if (isset($data['fecha'])) {
            $query->andWhere('m.fecha = :fecha')
                ->setParameter('fecha', $data['fecha']);
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
