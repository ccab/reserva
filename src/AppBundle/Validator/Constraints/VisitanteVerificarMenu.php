<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class VisitanteVerificarMenu extends Constraint {
    public $message = "No existe un menu de Almuerzo para la fecha seleccionada";

    public function validatedBy() {
        return 'visitante_verificar_menu';
    }
    
    public function getTargets() {
        return self::CLASS_CONSTRAINT;
    }
}
