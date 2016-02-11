<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadoReservacion.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class EstadoReservacion
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
     * @ORM\Column(name="nombre", type="string", length=50)
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="descripcion", type="text")
     */
    private $descripcion;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reservacion", mappedBy="estado")
     */
    private $reservaciones;

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
     * @return EstadoReservacion
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
     * @return EstadoReservacion
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
        $this->reservaciones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reservacione.
     *
     * @param \AppBundle\Entity\Reservacion $reservacione
     *
     * @return EstadoReservacion
     */
    public function addReservacione(\AppBundle\Entity\Reservacion $reservacione)
    {
        $this->reservaciones[] = $reservacione;

        return $this;
    }

    /**
     * Remove reservacione.
     *
     * @param \AppBundle\Entity\Reservacion $reservacione
     */
    public function removeReservacione(\AppBundle\Entity\Reservacion $reservacione)
    {
        $this->reservaciones->removeElement($reservacione);
    }

    /**
     * Get reservaciones.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservaciones()
    {
        return $this->reservaciones;
    }

    public function __toString()
    {
        return $this->nombre;
    }
}
