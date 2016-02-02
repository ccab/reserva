<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Reservacion
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Reservacion
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EstadoReservacion", inversedBy="reservaciones")
     */
    private $estado;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Usuario", inversedBy="reservaciones")
     */
    private $usuario;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservacionMenuAlimento", mappedBy="reservacion")
     */
    private $reservacionMenuAlimentos;


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
     * @return Reservacion
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
     * Set estado
     *
     * @param \AppBundle\Entity\EstadoReservacion $estado
     *
     * @return Reservacion
     */
    public function setEstado(\AppBundle\Entity\EstadoReservacion $estado = null)
    {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return \AppBundle\Entity\EstadoReservacion
     */
    public function getEstado()
    {
        return $this->estado;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->reservacionMenuAlimentos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reservacionMenuAlimento
     *
     * @param \AppBundle\Entity\ReservacionMenuAlimento $reservacionMenuAlimento
     *
     * @return Reservacion
     */
    public function addReservacionMenuAlimento(\AppBundle\Entity\ReservacionMenuAlimento $reservacionMenuAlimento)
    {
        $this->reservacionMenuAlimentos[] = $reservacionMenuAlimento;

        return $this;
    }

    /**
     * Remove reservacionMenuAlimento
     *
     * @param \AppBundle\Entity\ReservacionMenuAlimento $reservacionMenuAlimento
     */
    public function removeReservacionMenuAlimento(\AppBundle\Entity\ReservacionMenuAlimento $reservacionMenuAlimento)
    {
        $this->reservacionMenuAlimentos->removeElement($reservacionMenuAlimento);
    }

    /**
     * Get reservacionMenuAlimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservacionMenuAlimentos()
    {
        return $this->reservacionMenuAlimentos;
    }

    public function __toString()
    {
        return (string)$this->id;
    }

    /**
     * Set usuario
     *
     * @param \AppBundle\Entity\Usuario $usuario
     *
     * @return Reservacion
     */
    public function setUsuario(\AppBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
}
