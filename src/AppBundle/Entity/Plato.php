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
     * @var float
     *
     * @ORM\Column(name="precio", type="float")
     */
    private $precio;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $norma;
    
    /**
     * @ORM\Column(type="float")
     */
    private $valorNutricProteina;

    /**
     * @ORM\Column(type="float")
     */
    private $valorNutricCarbohidrato;

    /**
     * @ORM\Column(type="float")
     */
    private $valorNutricGrasa;

    /**
     * @ORM\Column(type="float")
     */
    private $valorNutricEnergia;
    
    /**
     * @ORM\Column(type="float")
     */
    private $temperatura;
    
    /**
     * @ORM\Column(type="float")
     */
    private $tiempo;
    
    /**
     * @ORM\Column(type="text")
     */
    private $observaciones;
    
    /**
     * @ORM\Column(type="text")
     */
    private $preparacion;
    
    /**
     * @ORM\Column(type="text")
     */
    private $coccion;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ProductoPlato", mappedBy="plato", cascade={"persist", "remove"})
     */
    private $productosPlato;

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
     * Add productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     *
     * @return Plato
     */
    public function addProductosPlato(\AppBundle\Entity\ProductoPlato $productoAlimento)
    {
        $productoAlimento->setPlato($this);

        $this->productosPlato->add($productoAlimento);

        return $this;
    }

    /**
     * Remove productoAlimento.
     *
     * @param \AppBundle\Entity\ProductoPlato $productoAlimento
     */
    public function removeProductosPlato(\AppBundle\Entity\ProductoPlato $productoAlimento)
    {
        $this->productosPlato->removeElement($productoAlimento);
    }

    /**
     * Get productoAlimentos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductosPlato()
    {
        return $this->productosPlato;
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
        $this->productosPlato = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * Set norma
     *
     * @param integer $norma
     *
     * @return Plato
     */
    public function setNorma($norma)
    {
        $this->norma = $norma;

        return $this;
    }

    /**
     * Get norma
     *
     * @return integer
     */
    public function getNorma()
    {
        return $this->norma;
    }

    /**
     * Set valorNutricProteina
     *
     * @param float $valorNutricProteina
     *
     * @return Plato
     */
    public function setValorNutricProteina($valorNutricProteina)
    {
        $this->valorNutricProteina = $valorNutricProteina;

        return $this;
    }

    /**
     * Get valorNutricProteina
     *
     * @return float
     */
    public function getValorNutricProteina()
    {
        return $this->valorNutricProteina;
    }

    /**
     * Set valorNutricCarbohidrato
     *
     * @param float $valorNutricCarbohidrato
     *
     * @return Plato
     */
    public function setValorNutricCarbohidrato($valorNutricCarbohidrato)
    {
        $this->valorNutricCarbohidrato = $valorNutricCarbohidrato;

        return $this;
    }

    /**
     * Get valorNutricCarbohidrato
     *
     * @return float
     */
    public function getValorNutricCarbohidrato()
    {
        return $this->valorNutricCarbohidrato;
    }

    /**
     * Set valorNutricGrasa
     *
     * @param float $valorNutricGrasa
     *
     * @return Plato
     */
    public function setValorNutricGrasa($valorNutricGrasa)
    {
        $this->valorNutricGrasa = $valorNutricGrasa;

        return $this;
    }

    /**
     * Get valorNutricGrasa
     *
     * @return float
     */
    public function getValorNutricGrasa()
    {
        return $this->valorNutricGrasa;
    }

    /**
     * Set valorNutricEnergia
     *
     * @param float $valorNutricEnergia
     *
     * @return Plato
     */
    public function setValorNutricEnergia($valorNutricEnergia)
    {
        $this->valorNutricEnergia = $valorNutricEnergia;

        return $this;
    }

    /**
     * Get valorNutricEnergia
     *
     * @return float
     */
    public function getValorNutricEnergia()
    {
        return $this->valorNutricEnergia;
    }

    /**
     * Set temperatura
     *
     * @param float $temperatura
     *
     * @return Plato
     */
    public function setTemperatura($temperatura)
    {
        $this->temperatura = $temperatura;

        return $this;
    }

    /**
     * Get temperatura
     *
     * @return float
     */
    public function getTemperatura()
    {
        return $this->temperatura;
    }

    /**
     * Set tiempo
     *
     * @param float $tiempo
     *
     * @return Plato
     */
    public function setTiempo($tiempo)
    {
        $this->tiempo = $tiempo;

        return $this;
    }

    /**
     * Get tiempo
     *
     * @return float
     */
    public function getTiempo()
    {
        return $this->tiempo;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     *
     * @return Plato
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set preparacion
     *
     * @param string $preparacion
     *
     * @return Plato
     */
    public function setPreparacion($preparacion)
    {
        $this->preparacion = $preparacion;

        return $this;
    }

    /**
     * Get preparacion
     *
     * @return string
     */
    public function getPreparacion()
    {
        return $this->preparacion;
    }

    /**
     * Set coccion
     *
     * @param string $coccion
     *
     * @return Plato
     */
    public function setCoccion($coccion)
    {
        $this->coccion = $coccion;

        return $this;
    }

    /**
     * Get coccion
     *
     * @return string
     */
    public function getCoccion()
    {
        return $this->coccion;
    }
}
