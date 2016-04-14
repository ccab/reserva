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
     * @var float
     *
     * @ORM\Column(name="cantidad", type="float")
     */
    private $cantidad;

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
}
