<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Reservacion.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ReservacionRepository")
 */
class Reservacion
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservacionMenuPlato", mappedBy="reservacion")
     */
    private $reservacionMenuPlatos;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $numeroComprobante;

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
     * Set fecha.
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
     * Get fecha.
     *
     * @return \DateTime
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set estado.
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
     * Get estado.
     *
     * @return \AppBundle\Entity\EstadoReservacion
     */
    public function getEstado()
    {
        return $this->estado;
    }
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->reservacionMenuPlatos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add reservacionMenuAlimento.
     *
     * @param \AppBundle\Entity\ReservacionMenuPlato $reservacionMenuAlimento
     *
     * @return Reservacion
     */
    public function addReservacionMenuAlimento(\AppBundle\Entity\ReservacionMenuPlato $reservacionMenuAlimento)
    {
        $this->reservacionMenuPlatos[] = $reservacionMenuAlimento;

        return $this;
    }

    /**
     * Remove reservacionMenuAlimento.
     *
     * @param \AppBundle\Entity\ReservacionMenuPlato $reservacionMenuAlimento
     */
    public function removeReservacionMenuAlimento(\AppBundle\Entity\ReservacionMenuPlato $reservacionMenuAlimento)
    {
        $this->reservacionMenuPlatos->removeElement($reservacionMenuAlimento);
    }

    /**
     * Get reservacionMenuAlimentos.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReservacionMenuPlatos()
    {
        return $this->reservacionMenuPlatos;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * Set usuario.
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
     * Get usuario.
     *
     * @return \AppBundle\Entity\Usuario
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set numeroComprobante
     *
     * @param integer $numeroComprobante
     *
     * @return Reservacion
     */
    public function setNumeroComprobante($numeroComprobante)
    {
        $this->numeroComprobante = $numeroComprobante;

        return $this;
    }

    /**
     * Get numeroComprobante
     *
     * @return integer
     */
    public function getNumeroComprobante()
    {
        return $this->numeroComprobante;
    }

    /**
     * Add reservacionMenuPlato
     *
     * @param \AppBundle\Entity\ReservacionMenuPlato $reservacionMenuPlato
     *
     * @return Reservacion
     */
    public function addReservacionMenuPlato(\AppBundle\Entity\ReservacionMenuPlato $reservacionMenuPlato)
    {
        $this->reservacionMenuPlatos[] = $reservacionMenuPlato;

        return $this;
    }

    /**
     * Remove reservacionMenuPlato
     *
     * @param \AppBundle\Entity\ReservacionMenuPlato $reservacionMenuPlato
     */
    public function removeReservacionMenuPlato(\AppBundle\Entity\ReservacionMenuPlato $reservacionMenuPlato)
    {
        $this->reservacionMenuPlatos->removeElement($reservacionMenuPlato);
    }

    public function getPrecioTotal()
    {
        $sum = 0;
        /** @var ReservacionMenuPlato $reservacionMenuPlato */
        foreach ($this->reservacionMenuPlatos as $reservacionMenuPlato) {
            $sum += $reservacionMenuPlato->getMenuPlato()->getPlato()->getPrecio();
        }

        return $sum;
    }

    public function getTiposDeMenu()
    {
        $tiposMenu = new ArrayCollection();

        /** @var ReservacionMenuPlato $reservacionMenuPlato */
        foreach ($this->reservacionMenuPlatos as $reservacionMenuPlato) {
            $tipoMenu = $reservacionMenuPlato->getMenuPlato()->getMenu()->getTipoMenu();
            if(!$tiposMenu->contains($tipoMenu)) {
                $tiposMenu->add($tipoMenu);
            }
        }
        
        return $tiposMenu;
    }

    public function getPlatosPorTipoMenu(TipoMenu $tipoMenu)
    {
        $platos = new ArrayCollection();

        /** @var ReservacionMenuPlato $reservacionMenuPlato */
        foreach ($this->reservacionMenuPlatos as $reservacionMenuPlato) {
            if($reservacionMenuPlato->getMenuPlato()->getMenu()->getTipoMenu() == $tipoMenu) {
                $platos->add($reservacionMenuPlato->getMenuPlato()->getPlato());
            }
        }
        
        return $platos;
    }

    public function getPlatos()
    {
        $platos = new ArrayCollection();

        /** @var ReservacionMenuPlato $reservacionMenuPlato */
        foreach ($this->reservacionMenuPlatos as $reservacionMenuPlato) {
            $platos->add($reservacionMenuPlato->getMenuPlato()->getPlato());
        }

        return $platos;
    }
}
