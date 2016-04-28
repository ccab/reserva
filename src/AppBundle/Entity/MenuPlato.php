<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuAlimento.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MenuPlato
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Plato", inversedBy="menuPlatos")
     */
    private $plato;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Menu", inversedBy="menuPlatos")
     */
    private $menu;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservacionMenuPlato", mappedBy="menuPlato")
     */
    private $reservacionMenuPlatos;

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
     * Set alimento.
     *
     * @param \AppBundle\Entity\Plato $plato
     *
     * @return MenuPlato
     */
    public function setPlato(\AppBundle\Entity\Plato $plato = null)
    {
        $this->plato = $plato;

        return $this;
    }

    /**
     * Get alimento.
     *
     * @return \AppBundle\Entity\Plato
     */
    public function getPlato()
    {
        return $this->plato;
    }

    /**
     * Set menu.
     *
     * @param \AppBundle\Entity\Menu $menu
     *
     * @return MenuPlato
     */
    public function setMenu(\AppBundle\Entity\Menu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu.
     *
     * @return \AppBundle\Entity\Menu
     */
    public function getMenu()
    {
        return $this->menu;
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
     * @return MenuPlato
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
     * Add reservacionMenuPlato
     *
     * @param \AppBundle\Entity\ReservacionMenuPlato $reservacionMenuPlato
     *
     * @return MenuPlato
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
}
