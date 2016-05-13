<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class ReservacionRepository extends \Doctrine\ORM\EntityRepository
{
    public function findPorRangoDeFecha($rango)
    {
        $q = $this->createQueryBuilder('r')
            ->join('r.estado', 'e')
            ->where('r.fecha > :inicio')
            ->andWhere('r.fecha < :fin')
            ->andWhere('e.nombre = :estado')
            ->setParameter(':inicio', $rango['inicio'])
            ->setParameter(':fin', $rango['fin'])
            ->setParameter('estado', 'Cobrada')
            ->getQuery();

        return $q->getResult();
    }
    
    public function findPorTipoPlato($tipoMenu, $categAlimento, $fecha)
    {
        $q = $this->createQueryBuilder('r')
            ->select('count(r.id)')
            ->join('r.reservacionMenuPlatos', 'rmp')
            ->join('rmp.menuPlato', 'mp')
            ->join('mp.menu', 'm')
            ->join('m.tipoMenu', 't')
            ->join('mp.plato', 'p')
            ->join('p.categoria', 'c')
            ->where('t.nombre = :tipoMenu')
            ->andWhere('c.nombre = :tipoAlimento')
            ->andWhere('r.fecha = :fecha')
            ->setParameter('tipoMenu', $tipoMenu)
            ->setParameter('tipoAlimento', $categAlimento)
            ->setParameter('fecha', $fecha)
            ->getQuery();

        return $q->getSingleScalarResult();
    }

    public function findEfectuarCobro($data)
    {
        $query = $this->createQueryBuilder('r');

        if (isset($data['usuario'])) {
            $query->join('r.usuario', 'u')
                ->where('u.noSolapin = :noSolapin')
                ->setParameter('noSolapin', $data['usuario']->getNoSolapin());
        }
        if (isset($data['fechaInicial'])) {
            $query->andWhere('r.fecha >= :fechaInicial')
                ->andWhere('r.fecha <= :fechaFinal')
                ->setParameter('fechaInicial', $data['fechaInicial'])
                ->setParameter('fechaFinal', $data['fechaFinal']);
        }
        /*if (isset($data['tipoMenu'])) {
            $query->join('r.', 'u')
                ->where('u.noSolapin = :noSolapin')
                ->setParameter('noSolapin', $data['usuario']->getNoSolapin());
        }*/

        return $query->getQuery()->getResult();
    }

}
