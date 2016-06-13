<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Recepcion
 *
 * @ORM\Table()
 * @ORM\Entity()
 */
class Recepcion
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
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecepcionProducto", mappedBy="recepcion")
     */
    private $recepcion_productos;


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
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Recepcion
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->recepcion_productos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add recepcionProducto
     *
     * @param \AppBundle\Entity\RecepcionProducto $recepcionProducto
     *
     * @return Recepcion
     */
    public function addRecepcionProducto(\AppBundle\Entity\RecepcionProducto $recepcionProducto)
    {
        $this->recepcion_productos[] = $recepcionProducto;

        return $this;
    }

    /**
     * Remove recepcionProducto
     *
     * @param \AppBundle\Entity\RecepcionProducto $recepcionProducto
     */
    public function removeRecepcionProducto(\AppBundle\Entity\RecepcionProducto $recepcionProducto)
    {
        $this->recepcion_productos->removeElement($recepcionProducto);
    }

    /**
     * Get recepcionProductos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecepcionProductos()
    {
        return $this->recepcion_productos;
    }
}
