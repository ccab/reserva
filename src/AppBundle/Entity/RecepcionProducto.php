<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RecepcionProducto
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\RecepcionProductoRepository")
 */
class RecepcionProducto
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
     * @ORM\Column(name="cantidad", type="float")
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Recepcion", inversedBy="recepcion_productos")
     */
    private $recepcion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Producto", inversedBy="recepcion_productos")
     */
    private $producto;


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
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return RecepcionProducto
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return float
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set recepcion
     *
     * @param \AppBundle\Entity\Recepcion $recepcion
     *
     * @return RecepcionProducto
     */
    public function setRecepcion(\AppBundle\Entity\Recepcion $recepcion = null)
    {
        $this->recepcion = $recepcion;

        return $this;
    }

    /**
     * Get recepcion
     *
     * @return \AppBundle\Entity\Recepcion
     */
    public function getRecepcion()
    {
        return $this->recepcion;
    }

    /**
     * Set producto
     *
     * @param \AppBundle\Entity\Producto $producto
     *
     * @return RecepcionProducto
     */
    public function setProducto(\AppBundle\Entity\Producto $producto = null)
    {
        $this->producto = $producto;

        return $this;
    }

    /**
     * Get producto
     *
     * @return \AppBundle\Entity\Producto
     */
    public function getProducto()
    {
        return $this->producto;
    }
}
