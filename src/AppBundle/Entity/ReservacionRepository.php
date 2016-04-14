<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class ReservacionRepository extends \Doctrine\ORM\EntityRepository
{
    public function findPorRangoDeFecha($rango)
    {
        $q = $this->createQueryBuilder('r')
            ->where('r.fecha > :inicio')
            ->andWhere('r.fecha < :fin')
            ->setParameter(':inicio', $rango['inicio'])
            ->setParameter(':fin', $rango['fin'])
            ->getQuery();

        return $q->getResult();
    }
    
    public function findPorTipoAlimento($tipoMenu, $categAlimento, $fecha)
    {
        $q = $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.reservacionMenuAlimentos', 'rma')
            ->join('rma.menuAlimento', 'ma')
            ->join('ma.menu', 'm')
            ->join('m.tipoMenu', 't')
            ->join('ma.alimento', 'a')
            ->join('a.categoria', 'c')
            ->where('t.nombre = :tipoMenu')
            ->andWhere('c.nombre = :tipoAlimento')
            ->andWhere('r.fecha = :fecha')
            ->setParameter('tipoMenu', $tipoMenu)
            ->setParameter('tipoAlimento', $categAlimento)
            ->setParameter('fecha', $fecha)
            ->getQuery();

        return $q->getSingleScalarResult();
    }
    
    

}
