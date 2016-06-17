<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ReservacionVisitante
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\ReservacionVisitanteRepository")
 * @AppAssert\VisitanteVerificarMenu()
 */
class ReservacionVisitante
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
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100)
     * @Assert\Length(min="1", max="15")
     * @Assert\Regex(pattern="/\d/", match=false, message="No puede insertar números en este campo")
     */
    private $nombre;

    /**
     * @var string
     *
     * @ORM\Column(name="apellido", type="string", length=100)
     * @Assert\Length(min="1", max="15")
     * @Assert\Regex(pattern="/\d/", match=false, message="No puede insertar números en este campo")
     */
    private $apellido;

    /**
     * @var string
     *
     * @ORM\Column(name="segundoApellido", type="string", length=100)
     * @Assert\Length(min="1", max="15")
     * @Assert\Regex(pattern="/\d/", match=false, message="No puede insertar números en este campo")
     */
    private $segundoApellido;

    /**
     * @var integer
     *
     * @ORM\Column(name="numeroComprobante", type="integer")
     */
    private $numeroComprobante;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Menu", inversedBy="reservacionVisitante")
     */
    private $menu;


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
     * @return ReservacionVisitante
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
     * Set nombre
     *
     * @param string $nombre
     *
     * @return ReservacionVisitante
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
     * Set apellido
     *
     * @param string $apellido
     *
     * @return ReservacionVisitante
     */
    public function setApellido($apellido)
    {
        $this->apellido = $apellido;

        return $this;
    }

    /**
     * Get apellido
     *
     * @return string
     */
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Set segundoApellido
     *
     * @param string $segundoApellido
     *
     * @return ReservacionVisitante
     */
    public function setSegundoApellido($segundoApellido)
    {
        $this->segundoApellido = $segundoApellido;

        return $this;
    }

    /**
     * Get segundoApellido
     *
     * @return string
     */
    public function getSegundoApellido()
    {
        return $this->segundoApellido;
    }

    /**
     * Set numeroComprobante
     *
     * @param integer $numeroComprobante
     *
     * @return ReservacionVisitante
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
     * Set menu
     *
     * @param \AppBundle\Entity\Menu $menu
     *
     * @return ReservacionVisitante
     */
    public function setMenu(\AppBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return \AppBundle\Entity\Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    public function getPrecioTotal()
    {
        return $this->menu->getPrecioPlatos();
    }
}
