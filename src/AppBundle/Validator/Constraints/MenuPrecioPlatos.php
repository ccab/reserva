<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MenuPrecioPlatos extends Constraint {
    public $message = "El precio de los platos es mas de lo establecido";
    
    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }
}
