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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Alimento", mappedBy="categoria")
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
        $this->alimentos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add alimento
     *
     * @param \AppBundle\Entity\Alimento $alimento
     *
     * @return Categoria
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

    public function __toString()
    {
        return $this->nombre;
    }
}
