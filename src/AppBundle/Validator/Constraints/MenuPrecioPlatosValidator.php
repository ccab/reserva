<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\MenuPlato;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 *   Cookbook: How to create a custom Validation Constraint
 */
class MenuPrecioPlatosValidator extends ConstraintValidator {
    
    public function validate($entity, Constraint $constraint) {
        $count = 0;

        /** @var MenuPlato $menuPlato */
        foreach ($entity->getMenuPlatos() as $menuPlato) {
            $count += $menuPlato->getPlato()->getPrecio();
        }

        if ($entity->getTipoMenu() == 'Desayuno' && $count > 2) {
            $this->context->buildViolation('El precio de los platos para el desayuno no puede ser mayor que 2')
                ->addViolation();
        } else if ($entity->getTipoMenu() == 'Almuerzo' && $count > 4) {
            $this->context->buildViolation('El precio de los platos para el almuerzo no puede ser mayor que 4')
                ->addViolation();
        } else if ($entity->getTipoMenu() == 'Comida' && $count > 4) {
            $this->context->buildViolation('El precio de los platos para la comida no puede ser mayor que 4')
                ->addViolation();
        }
    }

}
