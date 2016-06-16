<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Entity\Conversion;
use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuPlato;
use AppBundle\Entity\Producto;
use AppBundle\Entity\ProductoPlato;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 *   Cookbook: How to create a custom Validation Constraint
 */
class MenuComprobarPlatosValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @param Menu $entity */
    public function validate($entity, Constraint $constraint)
    {
        $result = $this->entityManager->getRepository('AppBundle:Menu')->findOneBy([
            'fecha' => $entity->getFecha(),
            'tipoMenu' => $entity->getTipoMenu(),
        ]);

        if (!is_null($result) && $result != $entity) {
            $this->context->buildViolation("Ya existe un menu del mismo tipo para la fecha seleccionada")
                ->addViolation();
        }

        // Platos vacios
        /** @var MenuPlato $menuPlato */
        foreach ($entity->getMenuPlatos() as $menuPlato) {
            if (is_null($menuPlato->getPlato())) {
                $this->context->buildViolation("Debe seleccionar un plato")
                    ->addViolation();
            }
        }

        $tmp = [];

        // Platos duplicados
        /** @var MenuPlato $menuPlato */
        foreach ($entity->getMenuPlatos() as $menuPlato) {
            if (!in_array($menuPlato->getPlato()->getCategoria(), $tmp)) {
                $tmp[] = $menuPlato->getPlato()->getCategoria();
            } else {
                $this->context->buildViolation('Ha seleccionado más de una vez un plato de la misma categoria')
                    ->addViolation();
                break;
            }
        }

        // Validar precios
        $precio = $entity->getPrecioPlatos();

        if ($entity->getTipoMenu() == 'Desayuno' && $precio > 2) {
            $this->context->buildViolation('El precio de los platos para el desayuno no puede ser mayor que 2')
                ->addViolation();
        } else if ($entity->getTipoMenu() == 'Almuerzo' && $precio > 4) {
            $this->context->buildViolation('El precio de los platos para el almuerzo no puede ser mayor que 4')
                ->addViolation();
        } else if ($entity->getTipoMenu() == 'Comida' && $precio > 4) {
            $this->context->buildViolation('El precio de los platos para la comida no puede ser mayor que 4')
                ->addViolation();
        }


        // Validar norma nutricional de los platos
        $valores = $entity->getValoresNutricionales();

        // TODO: definir cifras para almuerzo y comida
        //if ($entity->getTipoMenu() == 'Desayuno') {
        /*  if ($valores['proteina'] < 75 || $valores['carbohidrato'] < 375 || $valores['grasa'] < 50 || $valores['energia'] < 150) {
              $this->context->buildViolation($constraint->message." Proteinas: $valores[proteina] de 75.
              Carbohidratos: $valores[carbohidrato] de 375. Grasa: $valores[grasa] de 50.
              Energia: $valores[energia] de 150")
                  ->addViolation();
          }*/
        //}


        // Validar existencia de productos en almacen
        /** @var MenuPlato $menuPlato */
        foreach ($entity->getMenuPlatos() as $menuPlato) {
            if (!is_null($menuPlato->getPlato())) {
                /** @var ProductoPlato $productoPlato */
                foreach ($menuPlato->getPlato()->getProductosPlato() as $productoPlato) {
                    // TODO: total de usuarios
                    $pesoBrutoRaciones = $productoPlato->getPesoBruto() * 40;

                    // Obtener el factor de conversion de la UM del Plato a la UM del Producto
                    // Obtengo las posibles conversiones y busco el factor de la relacion correcta
                    $conversiones = $productoPlato->getUnidadMedida()->getConversionesPlato();
                    $factor = 0;
                    /** @var Conversion $conv */
                    foreach ($conversiones as $conv) {
                        if ($conv->getUnidadMedidaProducto() == $productoPlato->getProducto()->getUnidadMedida()) {
                            $factor = $conv->getFactor();
                        }
                    }

                    $conversion = $pesoBrutoRaciones * $factor;

                    if ($conversion > $productoPlato->getProducto()->getCantidad()) {
                        $this->context->buildViolation("La cantidad de producto requerido es mayor que la existencia en almacen.
                         Producto:" . $productoPlato->getProducto() . ". Cantidad solicitada: $conversion de " .
                            $productoPlato->getProducto()->getCantidad())
                            ->addViolation();
                    }
                }
            }
        }


    }

}
