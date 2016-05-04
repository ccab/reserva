<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Conversion
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Conversion
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="factor", type="float")
     */
    private $factor;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UnidadMedida", inversedBy="conversionesPlato")
     */
    private $unidadMedidaPlato;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\UnidadMedida", inversedBy="conversionesProducto")
     */
    private $unidadMedidaProducto;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set factor
     *
     * @param float $factor
     *
     * @return Conversion
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    /**
     * Get factor
     *
     * @return float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * Set unidadMedidaPlato
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedidaPlato
     *
     * @return Conversion
     */
    public function setUnidadMedidaPlato(\AppBundle\Entity\UnidadMedida $unidadMedidaPlato = null)
    {
        $this->unidadMedidaPlato = $unidadMedidaPlato;

        return $this;
    }

    /**
     * Get unidadMedidaPlato
     *
     * @return \AppBundle\Entity\UnidadMedida
     */
    public function getUnidadMedidaPlato()
    {
        return $this->unidadMedidaPlato;
    }

    /**
     * Set unidadMedidaProducto
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedidaProducto
     *
     * @return Conversion
     */
    public function setUnidadMedidaProducto(\AppBundle\Entity\UnidadMedida $unidadMedidaProducto = null)
    {
        $this->unidadMedidaProducto = $unidadMedidaProducto;

        return $this;
    }

    /**
     * Get unidadMedidaProducto
     *
     * @return \AppBundle\Entity\UnidadMedida
     */
    public function getUnidadMedidaProducto()
    {
        return $this->unidadMedidaProducto;
    }

    public function __toString()
    {
        return "conversion";
    }
}
