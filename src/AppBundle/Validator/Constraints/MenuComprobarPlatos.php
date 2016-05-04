<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class MenuComprobarPlatos extends Constraint {
    public $message = "Los platos para este menu no cumplen las medidas establecidas";

    public function validatedBy() {
        return 'menu_comprobar_platos';
    }
    
    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }
}
