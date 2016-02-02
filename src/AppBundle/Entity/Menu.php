<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Menu
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Menu
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
     * @var boolean
     *
     * @ORM\Column(name="aprobado", type="boolean")
     */
    private $aprobado;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha", type="date")
     */
    private $fecha;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\TipoMenu", inversedBy="menus")
     */
    private $tipoMenu;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\MenuAlimento", mappedBy="menu", cascade={"persist", "remove"})
     */
    private $menuAlimentos;

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
     * Set aprobado
     *
     * @param boolean $aprobado
     *
     * @return Menu
     */
    public function setAprobado($aprobado)
    {
        $this->aprobado = $aprobado;

        return $this;
    }

    /**
     * Get aprobado
     *
     * @return boolean
     */
    public function getAprobado()
    {
        return $this->aprobado;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     *
     * @return Menu
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
     * Set tipoMenu
     *
     * @param \AppBundle\Entity\TipoMenu $tipoMenu
     *
     * @return Menu
     */
    public function setTipoMenu(\AppBundle\Entity\TipoMenu $tipoMenu = null)
    {
        $this->tipoMenu = $tipoMenu;

        return $this;
    }

    /**
     * Get tipoMenu
     *
     * @return \AppBundle\Entity\TipoMenu
     */
    public function getTipoMenu()
    {
        return $this->tipoMenu;
    }

    /**
     * Add alimento
     *
     * @param \AppBundle\Entity\Alimento $alimento
     *
     * @return Menu
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

    /**
     * Add menuAlimento
     *
     * @param \AppBundle\Entity\MenuAlimento $menuAlimento
     *
     * @return Menu
     */
    public function addMenuAlimento(\AppBundle\Entity\MenuAlimento $menuAlimento)
    {
        $menuAlimento->setMenu($this);

        $this->menuAlimentos->add($menuAlimento);

        return $this;
    }

    /**
     * Remove menuAlimento
     *
     * @param \AppBundle\Entity\MenuAlimento $menuAlimento
     */
    public function removeMenuAlimento(\AppBundle\Entity\MenuAlimento $menuAlimento)
    {
        $this->menuAlimentos->removeElement($menuAlimento);
    }

    /**
     * Get menuAlimentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMenuAlimentos()
    {
        return $this->menuAlimentos;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->menuAlimentos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->aprobado = false;
    }

}
