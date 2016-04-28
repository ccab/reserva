<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plato.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Producto
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
     * @ORM\Column(name="codigo", type="string", length=20)
     */
    private $codigo;

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
     * @ORM\Column(name="cantidad", type="float")
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="UnidadMedida", inversedBy="productos")
     */
    private $unidadMedida;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoPlato", mappedBy="producto")
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
     * Set codigo.
     *
     * @param string $codigo
     *
     * @return Producto
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo.
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set nombre.
     *
     * @param string $nombre
     *
     * @return Producto
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
     * @return Producto
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
     * Set unidadMedida.
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedida
     *
     * @return Producto
     */
    public function setUnidadMedida(\AppBundle\Entity\UnidadMedida $unidadMedida = null)
    {
        $this->unidadMedida = $unidadMedida;

        return $this;
    }

    /**
     * Get unidadMedida.
     *
     * @return \AppBundle\Entity\UnidadMedida
     */
    public function getUnidadMedida()
    {
        return $this->unidadMedida;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->productoPlatos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     *
     * @return Producto
     */
    public function addProductoAlimento(\AppBundle\Entity\ProductoPlato $productoAlimento)
    {
        $this->productoPlatos[] = $productoAlimento;

        return $this;
    }

    /**
     * Remove productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     */
    public function removeProductoAlimento(\AppBundle\Entity\ProductoPlato $productoAlimento)
    {
        $this->productoPlatos->removeElement($productoAlimento);
    }

    /**
     * Get productoAlimentos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductoPlatos()
    {
        return $this->productoPlatos;
    }

    public function __toString()
    {
        return $this->nombre;
    }

    /**
     * Set cantidad.
     *
     * @param float $cantidad
     *
     * @return Producto
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
     * Add productoPlato
     *
     * @param \AppBundle\Entity\ProductoPlato $productoPlato
     *
     * @return Producto
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
}
