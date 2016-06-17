<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PlatoVerificarProductos extends Constraint {
    public $message = "Los productos no son válidos";

    public function validatedBy() {
        return get_class($this).'Validator';
    }
    
    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }
}
