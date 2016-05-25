<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Conversion;
use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuPlato;
use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoPlato;
use AppBundle\Entity\ReservacionVisitante;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 *   Cookbook: How to create a custom Validation Constraint
 */
class VisitanteVerificarMenuValidator extends ConstraintValidator {
    private $entityManager;

    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /** @param ReservacionVisitante $entity */
    public function validate($entity, Constraint $constraint) {

        $almuerzo = $this->entityManager
            ->getRepository('AppBundle:TipoMenu')
            ->findOneByNombre('Almuerzo');
        $menu = $this->entityManager
            ->getRepository('AppBundle:Menu')
            ->findOneBy([
                'tipoMenu' => $almuerzo->getId(),
                'fecha' => $entity->getFecha(),
            ]);

        $ultimoComprobante = $this->entityManager
            ->getRepository('AppBundle:ReservacionVisitante')
            ->findUltimoComprobante($entity->getFecha());

        dump($ultimoComprobante);

        if (!$menu) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        } else {
            $entity->setMenu($menu);
        }
        if ($ultimoComprobante > 40) {
            $this->context->buildViolation('Ha alcanzado 40 reservaciones para visitantes')
                ->addViolation();
        } else {
            $entity->setNumeroComprobante(++$ultimoComprobante);
        }



    }

}
