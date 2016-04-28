<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categoria
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Categoria
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Plato", mappedBy="categoria")
     */
    protected $platos;

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
     * @return Categoria
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
     * Constructor
     */
    public function __construct()
    {
        $this->platos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add alimento
     *
     * @param \AppBundle\Entity\Plato $alimento
     *
     * @return Categoria
     */
    public function addAlimento(\AppBundle\Entity\Plato $alimento)
    {
        $this->platos[] = $alimento;

        return $this;
    }

    /**
     * Remove alimento
     *
     * @param \AppBundle\Entity\Plato $alimento
     */
    public function removeAlimento(\AppBundle\Entity\Plato $alimento)
    {
        $this->platos->removeElement($alimento);
    }

    /**
     * Get alimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlatos()
    {
        return $this->platos;
    }

    public function __toString()
    {
        return $this->nombre;
    }

    /**
     * Add plato
     *
     * @param \AppBundle\Entity\Plato $plato
     *
     * @return Categoria
     */
    public function addPlato(\AppBundle\Entity\Plato $plato)
    {
        $this->platos[] = $plato;

        return $this;
    }

    /**
     * Remove plato
     *
     * @param \AppBundle\Entity\Plato $plato
     */
    public function removePlato(\AppBundle\Entity\Plato $plato)
    {
        $this->platos->removeElement($plato);
    }
}
