<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;

class ReservacionVisitanteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUltimoComprobante($fecha)
    {
        $query = $this->createQueryBuilder('r')
            ->select('MAX(r.numeroComprobante)')
            ->where('r.fecha = :fecha')
            ->setParameter('fecha', $fecha)
            ->getQuery();

        return $query->getSingleScalarResult();
    }

}
