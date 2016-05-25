<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Validator\Constraints as AppAssert;

/**
 * Menu.
 *
 * @ORM\Table()
 * @ORM\Entity
 * @AppAssert\MenuComprobarPlatos()
 */
class Menu
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="aprobado", type="boolean")
     */
    private $aprobado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TipoMenu", inversedBy="menus")
     */
    private $tipoMenu;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MenuPlato", mappedBy="menu", cascade={"persist", "remove"})
     */
    private $menuPlatos;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservacionVisitante", mappedBy="menu")
     */
    private $reservacionVisitante;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set aprobado.
     *
     * @param bool $aprobado
     *
     * @return Menu
     */
    public function setAprobado($aprobado)
    {
        $this->aprobado = $aprobado;

        return $this;
    }

    /**
     * Get aprobado.
     *
     * @return bool
     */
    public function getAprobado()
    {
        return $this->aprobado;
    }

    /**
     * Set fecha.
     *
     * @param \DateTime $fecha
     *
     * @return Menu
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha.
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set tipoMenu.
     *
     * @param \AppBundle\Entity\TipoMenu $tipoMenu
     *
     * @return Menu
     */
    public function setTipoMenu(\AppBundle\Entity\TipoMenu $tipoMenu = null)
    {
        $this->tipoMenu = $tipoMenu;

        return $this;
    }

    /**
     * Get tipoMenu.
     *
     * @return \AppBundle\Entity\TipoMenu
     */
    public function getTipoMenu()
    {
        return $this->tipoMenu;
    }

    /**
     * Add alimento.
     *
     * @param \AppBundle\Entity\Plato $alimento
     *
     * @return Menu
     */
    public function addAlimento(\AppBundle\Entity\Plato $alimento)
    {
        $this->alimentos[] = $alimento;

        return $this;
    }

    /**
     * Remove alimento.
     *
     * @param \AppBundle\Entity\Plato $alimento
     */
    public function removeAlimento(\AppBundle\Entity\Plato $alimento)
    {
        $this->alimentos->removeElement($alimento);
    }

    /**
     * Get alimentos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAlimentos()
    {
        return $this->alimentos;
    }

    /**
     * Add menuAlimento.
     *
     * @param \AppBundle\Entity\MenuPlato $menuPlato
     *
     * @return Menu
     */
    public function addMenuPlato(\AppBundle\Entity\MenuPlato $menuPlato)
    {
        $menuPlato->setMenu($this);

        $this->menuPlatos->add($menuPlato);

        return $this;
    }

    /**
     * Remove menuAlimento.
     *
     * @param \AppBundle\Entity\MenuPlato $menuPlato
     */
    public function removeMenuPlato(\AppBundle\Entity\MenuPlato $menuPlato)
    {
        $this->menuPlatos->removeElement($menuPlato);
    }

    /**
     * Get menuAlimentos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuPlatos()
    {
        return $this->menuPlatos;
    }
    

    public function __toString()
    {
        return "$this->tipoMenu del $this->fecha";
    }

    public function getPrecioPlatos()
    {
        $precio = 0;

        /** @var MenuPlato $menuPlato */
        foreach ($this->menuPlatos as $menuPlato) {
            $precio += $menuPlato->getPlato()->getPrecio();
        }
        
        return $precio;
    }

    public function getValoresNutricionales()
    {
        $valores = [
            'proteina'     => 0,
            'carbohidrato' => 0,
            'grasa'        => 0,
            'energia'      => 0,
        ];

        /** @var MenuPlato $menuPlato */
        foreach ($this->menuPlatos as $menuPlato) {
            $valores['proteina'] += $menuPlato->getPlato()->getValorNutricProteina();
            $valores['carbohidrato'] += $menuPlato->getPlato()->getValorNutricCarbohidrato();
            $valores['grasa'] += $menuPlato->getPlato()->getValorNutricGrasa();
            $valores['energia'] += $menuPlato->getPlato()->getValorNutricEnergia();
        }
        
        return $valores;
    }

    public function getFactorConversion()
    {
        $factor = 0;
        
        /** @var MenuPlato $menuPlato */
        foreach ($this->menuPlatos as $menuPlato) {
            /** @var ProductoPlato $productoPlato */
            foreach ($menuPlato->getPlato()->getProductosPlato() as $productoPlato) {
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

            }
        }
        
        return $factor;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuPlatos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reservacionVisitante = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reservacionVisitante
     *
     * @param \AppBundle\Entity\ReservacionVisitante $reservacionVisitante
     *
     * @return Menu
     */
    public function addReservacionVisitante(\AppBundle\Entity\ReservacionVisitante $reservacionVisitante)
    {
        $this->reservacionVisitante[] = $reservacionVisitante;

        return $this;
    }

    /**
     * Remove reservacionVisitante
     *
     * @param \AppBundle\Entity\ReservacionVisitante $reservacionVisitante
     */
    public function removeReservacionVisitante(\AppBundle\Entity\ReservacionVisitante $reservacionVisitante)
    {
        $this->reservacionVisitante->removeElement($reservacionVisitante);
    }

    /**
     * Get reservacionVisitante
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservacionVisitante()
    {
        return $this->reservacionVisitante;
    }
}
