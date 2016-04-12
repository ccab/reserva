<?php

namespace AppBundle\Entity;

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

}
