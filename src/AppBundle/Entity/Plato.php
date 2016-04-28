<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Plato.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Plato
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoPlato", mappedBy="plato", cascade={"persist", "remove"})
     */
    private $productoPlatos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MenuPlato", mappedBy="plato", cascade={"persist", "remove"})
     */
    private $menuPlatos;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Categoria", inversedBy="platos")
     */
    private $categoria;

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
     * @return Plato
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
     * @return Plato
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
     * @return Plato
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
     * Set precio.
     *
     * @param float $precio
     *
     * @return Plato
     */
    public function setPrecio($precio)
    {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio.
     *
     * @return float
     */
    public function getPrecio()
    {
        return $this->precio;
    }

    /**
     * Set unidadMedida.
     *
     * @param \AppBundle\Entity\UnidadMedida $unidadMedida
     *
     * @return Plato
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
     * Add productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     *
     * @return Plato
     */
    public function addProductoPlato(\AppBundle\Entity\ProductoPlato $productoAlimento)
    {
        $productoAlimento->setPlato($this);

        $this->productoPlatos->add($productoAlimento);

        return $this;
    }

    /**
     * Remove productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     */
    public function removeProductoPlato(\AppBundle\Entity\ProductoPlato $productoAlimento)
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
     * Constructor.
     */
    public function __construct()
    {
        $this->productoPlatos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->menuPlatos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add menuAlimento.
     *
     * @param \AppBundle\Entity\MenuPlato $menuAlimento
     *
     * @return Plato
     */
    public function addMenuAlimento(\AppBundle\Entity\MenuPlato $menuAlimento)
    {
        $this->menuPlatos[] = $menuAlimento;

        return $this;
    }

    /**
     * Remove menuAlimento.
     *
     * @param \AppBundle\Entity\MenuPlato $menuAlimento
     */
    public function removeMenuAlimento(\AppBundle\Entity\MenuPlato $menuAlimento)
    {
        $this->menuPlatos->removeElement($menuAlimento);
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

    /**
     * Set cantidad.
     *
     * @param float $cantidad
     *
     * @return Plato
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
     * Set categoria
     *
     * @param \AppBundle\Entity\Categoria $categoria
     *
     * @return Plato
     */
    public function setCategoria(\AppBundle\Entity\Categoria $categoria = null)
    {
        $this->categoria = $categoria;

        return $this;
    }

    /**
     * Get categoria
     *
     * @return \AppBundle\Entity\Categoria
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Add menuPlato
     *
     * @param \AppBundle\Entity\MenuPlato $menuPlato
     *
     * @return Plato
     */
    public function addMenuPlato(\AppBundle\Entity\MenuPlato $menuPlato)
    {
        $this->menuPlatos[] = $menuPlato;

        return $this;
    }

    /**
     * Remove menuPlato
     *
     * @param \AppBundle\Entity\MenuPlato $menuPlato
     */
    public function removeMenuPlato(\AppBundle\Entity\MenuPlato $menuPlato)
    {
        $this->menuPlatos->removeElement($menuPlato);
    }
}
