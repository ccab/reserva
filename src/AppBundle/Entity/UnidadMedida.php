<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UnidadMedida.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UnidadMedidaRepository")
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
     * @Assert\Regex(pattern="/\d/", match=false, message="No puede insertar números en este campo")
     * @Assert\Length(min="1", max="15")
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5)
     * @Assert\Length(min="1", max="15")
     * @Assert\Regex(pattern="/\d/", match=false, message="No puede insertar números en este campo")
     */
    private $abreviatura;

    /**
     * @ORM\OneToMany(targetEntity="Producto", mappedBy="unidadMedida")
     */
    protected $productos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoPlato", mappedBy="unidadMedida")
     */
    protected $productoPlatos;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Conversion", mappedBy="unidadMedidaPlato")
     */
    protected $conversionesPlato;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Conversion", mappedBy="unidadMedidaProducto")
     */
    protected $conversionesProducto;

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
        return $this->abreviatura;
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

    /**
     * Add conversionesPlato
     *
     * @param \AppBundle\Entity\Conversion $conversionesPlato
     *
     * @return UnidadMedida
     */
    public function addConversionesPlato(\AppBundle\Entity\Conversion $conversionesPlato)
    {
        $this->conversionesPlato[] = $conversionesPlato;

        return $this;
    }

    /**
     * Remove conversionesPlato
     *
     * @param \AppBundle\Entity\Conversion $conversionesPlato
     */
    public function removeConversionesPlato(\AppBundle\Entity\Conversion $conversionesPlato)
    {
        $this->conversionesPlato->removeElement($conversionesPlato);
    }

    /**
     * Get conversionesPlato
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConversionesPlato()
    {
        return $this->conversionesPlato;
    }

    /**
     * Add conversionesProducto
     *
     * @param \AppBundle\Entity\Conversion $conversionesProducto
     *
     * @return UnidadMedida
     */
    public function addConversionesProducto(\AppBundle\Entity\Conversion $conversionesProducto)
    {
        $this->conversionesProducto[] = $conversionesProducto;

        return $this;
    }

    /**
     * Remove conversionesProducto
     *
     * @param \AppBundle\Entity\Conversion $conversionesProducto
     */
    public function removeConversionesProducto(\AppBundle\Entity\Conversion $conversionesProducto)
    {
        $this->conversionesProducto->removeElement($conversionesProducto);
    }

    /**
     * Get conversionesProducto
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConversionesProducto()
    {
        return $this->conversionesProducto;
    }

    /**
     * Set abreviatura
     *
     * @param string $abreviatura
     *
     * @return UnidadMedida
     */
    public function setAbreviatura($abreviatura)
    {
        $this->abreviatura = $abreviatura;

        return $this;
    }

    /**
     * Get abreviatura
     *
     * @return string
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }
}
