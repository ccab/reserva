<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReservacionMenuAlimento.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class ReservacionMenuPlato
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Reservacion", inversedBy="reservacionMenuPlatos")
     */
    private $reservacion;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\MenuPlato", inversedBy="reservacionMenuPlatos")
     */
    private $menuPlato;

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
     * @return ReservacionMenuPlato
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
     * @param \AppBundle\Entity\MenuPlato $menuPlato
     *
     * @return ReservacionMenuPlato
     */
    public function setMenuPlato(\AppBundle\Entity\MenuPlato $menuPlato = null)
    {
        $this->menuPlato = $menuPlato;

        return $this;
    }

    /**
     * Get menuAlimento.
     *
     * @return \AppBundle\Entity\MenuPlato
     */
    public function getMenuPlato()
    {
        return $this->menuPlato;
    }
}
