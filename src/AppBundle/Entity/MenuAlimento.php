<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuAlimento
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class MenuAlimento
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Alimento", inversedBy="menuAlimentos")
     */
    private $alimento;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Menu", inversedBy="menuAlimentos")
     */
    private $menu;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ReservacionMenuAlimento", mappedBy="menuAlimento")
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
     * Set alimento
     *
     * @param \AppBundle\Entity\Alimento $alimento
     *
     * @return MenuAlimento
     */
    public function setAlimento(\AppBundle\Entity\Alimento $alimento = null)
    {
        $this->alimento = $alimento;

        return $this;
    }

    /**
     * Get alimento
     *
     * @return \AppBundle\Entity\Alimento
     */
    public function getAlimento()
    {
        return $this->alimento;
    }

    /**
     * Set menu
     *
     * @param \AppBundle\Entity\Menu $menu
     *
     * @return MenuAlimento
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
     * @return MenuAlimento
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
}
