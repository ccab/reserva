<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class ReservacionRepository extends \Doctrine\ORM\EntityRepository
{
    public function findPorRangoDeFecha($rango, $estado = 'Cobrada')
    {
        $query = $this->createQueryBuilder('r')
            ->join('r.estado', 'e')
            ->andWhere('e.nombre = :estado')
            ->setParameter('estado', $estado);

        if (!is_null($rango)) {
            $query->andWhere('r.fechaCobrada >= :inicio')
                ->andWhere('r.fechaCobrada <= :fin')
                ->setParameter(':inicio', $rango['inicio'])
                ->setParameter(':fin', $rango['fin']);
        }

        return $query->getQuery()->getResult();
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
        $query = $this->createQueryBuilder('r')
                ->select('r, rmp, mp, m, t')
                ->join('r.usuario', 'u')
                ->join('r.estado', 'e')
                ->join('r.reservacionMenuPlatos', 'rmp')
                ->join('rmp.menuPlato', 'mp')
                ->join('mp.menu', 'm')
                ->join('m.tipoMenu', 't')
                ->where('u.noSolapin = :noSolapin')
                ->andWhere('e.nombre = :estado')
                ->andWhere('r.fecha >= :fechaInicial')
                ->andWhere('r.fecha <= :fechaFinal')
                ->setParameter('noSolapin', $data['usuario']->getNoSolapin())
                ->setParameter('fechaInicial', new \DateTime('Monday next week'))
                ->setParameter('fechaFinal', new \DateTime('Sunday next week'))
                ->setParameter('estado', 'Creada')
                ->orderBy('r.fecha');
        
        return $query->getQuery()->getResult();
    }
    
    public function findEfectuarCobroIds($data)
    {
        $query = $this->createQueryBuilder('r')
                ->select('r.id')
                ->join('r.usuario', 'u')
                ->join('r.estado', 'e')
                ->where('u.noSolapin = :noSolapin')
                ->andWhere('e.nombre = :estado')
                ->andWhere('r.fecha >= :fechaInicial')
                ->andWhere('r.fecha <= :fechaFinal')
                ->setParameter('noSolapin', $data['solapin'])
                ->setParameter('fechaInicial', new \DateTime('Monday next week'))
                ->setParameter('fechaFinal', new \DateTime('Sunday next week'))
                ->setParameter('estado', 'Creada')
                ->orderBy('r.fecha');
        
        return $query->getQuery()->getResult();
    }

}
