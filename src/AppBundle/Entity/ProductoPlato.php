<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductoAlimento.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ProductoPlato
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
     * @ORM\Column(type="float")
     */
    private $pesoBruto;
    
    /**
     * @ORM\Column(type="float")
     */
    private $pesoNeto;

    /**
     * @ORM\ManyToOne(targetEntity="UnidadMedida", inversedBy="productoPlatos")
     */
    private $unidadMedida;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="productoPlatos")
     */
    private $producto;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Plato", inversedBy="productoPlatos")
     */
    private $plato;

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
     * Set cantidad.
     *
     * @param float $cantidad
     *
     * @return ProductoPlato
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad.
     *
     * @return float
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set producto.
     *
     * @param \AppBundle\Entity\Producto $producto
     *
     * @return ProductoPlato
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null)
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * Get producto.
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto()
    {
        return $this->producto;
    }

    /**
     * Set alimento.
     *
     * @param \AppBundle\Entity\Plato $plato
     *
     * @return ProductoPlato
     */
    public function setPlato(\AppBundle\Entity\Plato $plato = null)
    {
        $this->plato = $plato;

        return $this;
    }

    /**
     * Get alimento.
     *
     * @return \AppBundle\Entity\Plato
     */
    public function getPlato()
    {
        return $this->plato;
    }

    /**
     * Set pesoBruto
     *
     * @param float $pesoBruto
     *
     * @return ProductoPlato
     */
    public function setPesoBruto($pesoBruto)
    {
        $this->pesoBruto = $pesoBruto;

        return $this;
    }

    /**
     * Get pesoBruto
     *
     * @return float
     */
    public function getPesoBruto()
    {
        return $this->pesoBruto;
    }

    /**
     * Set pesoNeto
     *
     * @param float $pesoNeto
     *
     * @return ProductoPlato
     */
    public function setPesoNeto($pesoNeto)
    {
        $this->pesoNeto = $pesoNeto;

        return $this;
    }

    /**
     * Get pesoNeto
     *
     * @return float
     */
    public function getPesoNeto()
    {
        return $this->pesoNeto;
    }

    /**
     * Set unidadMedida
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedida
     *
     * @return ProductoPlato
     */
    public function setUnidadMedida(\AppBundle\Entity\UnidadMedida $unidadMedida = null)
    {
        $this->unidadMedida = $unidadMedida;

        return $this;
    }

    /**
     * Get unidadMedida
     *
     * @return \AppBundle\Entity\UnidadMedida
     */
    public function getUnidadMedida()
    {
        return $this->unidadMedida;
    }
}
