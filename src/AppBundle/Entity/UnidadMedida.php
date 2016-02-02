<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnidadMedida
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class UnidadMedida
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Alimento", mappedBy="unidadMedida")
     */
    protected $alimentos;

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
     * Set nombre
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
     * @return UnidadMedida
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
     * Constructor
     */
    public function __construct()
    {
        $this->productos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add producto
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
     * Remove producto
     *
     * @param \AppBundle\Entity\Producto $producto
     */
    public function removeProducto(\AppBundle\Entity\Producto $producto)
    {
        $this->productos->removeElement($producto);
    }

    /**
     * Get productos
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
     * Add alimento
     *
     * @param \AppBundle\Entity\Alimento $alimento
     *
     * @return UnidadMedida
     */
    public function addAlimento(\AppBundle\Entity\Alimento $alimento)
    {
        $this->alimentos[] = $alimento;

        return $this;
    }

    /**
     * Remove alimento
     *
     * @param \AppBundle\Entity\Alimento $alimento
     */
    public function removeAlimento(\AppBundle\Entity\Alimento $alimento)
    {
        $this->alimentos->removeElement($alimento);
    }

    /**
     * Get alimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAlimentos()
    {
        return $this->alimentos;
    }
}
