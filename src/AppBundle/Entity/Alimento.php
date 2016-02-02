<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Alimento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Alimento
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
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=50)
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
     * @var float
     *
     * @ORM\Column(name="precio", type="float")
     */
    private $precio;

    /**
     * @var float
     *
     * @ORM\Column(name="cantidad", type="float")
     */
    private $cantidad;

    /**
     * @ORM\ManyToOne(targetEntity="UnidadMedida", inversedBy="alimentos")
     */
    private $unidadMedida;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoAlimento", mappedBy="alimento", cascade={"persist", "remove"})
     */
    private $productoAlimentos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MenuAlimento", mappedBy="alimento", cascade={"persist", "remove"})
     */
    private $menuAlimentos;


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
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Alimento
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Alimento
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     *
     * @return Alimento
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set precio
     *
     * @param float $precio
     *
     * @return Alimento
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set unidadMedida
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedida
     *
     * @return Alimento
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


    /**
     * Add productoAlimento
     *
     * @param \AppBundle\Entity\ProductoAlimento $productoAlimento
     *
     * @return Alimento
     */
    public function addProductoAlimento(\AppBundle\Entity\ProductoAlimento $productoAlimento)
    {
        $productoAlimento->setAlimento($this);

        $this->productoAlimentos->add($productoAlimento);

        return $this;
    }

    /**
     * Remove productoAlimento
     *
     * @param \AppBundle\Entity\ProductoAlimento $productoAlimento
     */
    public function removeProductoAlimento(\AppBundle\Entity\ProductoAlimento $productoAlimento)
    {
        $this->productoAlimentos->removeElement($productoAlimento);
    }

    /**
     * Get productoAlimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductoAlimentos()
    {
        return $this->productoAlimentos;
    }

    public function __toString()
    {
        return $this->nombre;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productoAlimentos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->menuAlimentos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add menuAlimento
     *
     * @param \AppBundle\Entity\MenuAlimento $menuAlimento
     *
     * @return Alimento
     */
    public function addMenuAlimento(\AppBundle\Entity\MenuAlimento $menuAlimento)
    {
        $this->menuAlimentos[] = $menuAlimento;

        return $this;
    }

    /**
     * Remove menuAlimento
     *
     * @param \AppBundle\Entity\MenuAlimento $menuAlimento
     */
    public function removeMenuAlimento(\AppBundle\Entity\MenuAlimento $menuAlimento)
    {
        $this->menuAlimentos->removeElement($menuAlimento);
    }

    /**
     * Get menuAlimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuAlimentos()
    {
        return $this->menuAlimentos;
    }

    /**
     * Set cantidad
     *
     * @param float $cantidad
     *
     * @return Alimento
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
}
