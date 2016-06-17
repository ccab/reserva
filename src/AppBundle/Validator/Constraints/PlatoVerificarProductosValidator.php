<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Plato;
use AppBundle\Entity\ProductoPlato;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 *   Cookbook: How to create a custom Validation Constraint
 */
class PlatoVerificarProductosValidator extends ConstraintValidator {

    /** @param Plato $entity */
    public function validate($entity, Constraint $constraint) {
        if (count($entity->getProductosPlato()) == 0) {
            $this->context->buildViolation("Debe seleccionar un producto")
                ->addViolation();
        }

        $tmp = [];
        foreach ($entity->getProductosPlato() as $productoPlato) {
            if (!in_array($productoPlato->getProducto(), $tmp)) {
                $tmp[] = $productoPlato->getProducto();
            } else {
                $this->context->buildViolation("Ha seleccionado el mismo producto más de una vez")
                    ->addViolation();
            }
        }
    }

}
