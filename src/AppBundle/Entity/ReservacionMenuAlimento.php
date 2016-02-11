<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReservacionMenuAlimento.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReservacionMenuAlimento
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Reservacion", inversedBy="reservacionMenuAlimentos")
     */
    private $reservacion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MenuAlimento", inversedBy="reservacionMenuAlimentos")
     */
    private $menuAlimento;

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
     * Set reservacion.
     *
     * @param \AppBundle\Entity\Reservacion $reservacion
     *
     * @return ReservacionMenuAlimento
     */
    public function setReservacion(\AppBundle\Entity\Reservacion $reservacion = null)
    {
        $this->reservacion = $reservacion;

        return $this;
    }

    /**
     * Get reservacion.
     *
     * @return \AppBundle\Entity\Reservacion
     */
    public function getReservacion()
    {
        return $this->reservacion;
    }

    /**
     * Set menuAlimento.
     *
     * @param \AppBundle\Entity\MenuAlimento $menuAlimento
     *
     * @return ReservacionMenuAlimento
     */
    public function setMenuAlimento(\AppBundle\Entity\MenuAlimento $menuAlimento = null)
    {
        $this->menuAlimento = $menuAlimento;

        return $this;
    }

    /**
     * Get menuAlimento.
     *
     * @return \AppBundle\Entity\MenuAlimento
     */
    public function getMenuAlimento()
    {
        return $this->menuAlimento;
    }
}
