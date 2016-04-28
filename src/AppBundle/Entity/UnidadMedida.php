<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnidadMedida.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class UnidadMedida
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
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text")
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity="Producto", mappedBy="unidadMedida")
     */
    protected $productos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoPlato", mappedBy="unidadMedida")
     */
    protected $productoPlatos;

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
     * Set nombre.
     *
     * @param string $nombre
     *
     * @return UnidadMedida
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre.
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion.
     *
     * @param string $descripcion
     *
     * @return UnidadMedida
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion.
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->productos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add producto.
     *
     * @param \AppBundle\Entity\Producto $producto
     *
     * @return UnidadMedida
     */
    public function addProducto(\AppBundle\Entity\Producto $producto)
    {
        $this->productos[] = $producto;

        return $this;
    }

    /**
     * Remove producto.
     *
     * @param \AppBundle\Entity\Producto $producto
     */
    public function removeProducto(\AppBundle\Entity\Producto $producto)
    {
        $this->productos->removeElement($producto);
    }

    /**
     * Get productos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductos()
    {
        return $this->productos;
    }

    public function __toString()
    {
        return $this->nombre;
    }

    /**
     * Add productoPlato
     *
     * @param \AppBundle\Entity\ProductoPlato $productoPlato
     *
     * @return UnidadMedida
     */
    public function addProductoPlato(\AppBundle\Entity\ProductoPlato $productoPlato)
    {
        $this->productoPlatos[] = $productoPlato;

        return $this;
    }

    /**
     * Remove productoPlato
     *
     * @param \AppBundle\Entity\ProductoPlato $productoPlato
     */
    public function removeProductoPlato(\AppBundle\Entity\ProductoPlato $productoPlato)
    {
        $this->productoPlatos->removeElement($productoPlato);
    }

    /**
     * Get productoPlatos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductoPlatos()
    {
        return $this->productoPlatos;
    }
}
