<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Usuario.
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="AppBundle\Entity\UsuarioRepository")
 */
class Usuario implements UserInterface, \Serializable
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
     * @ORM\Column(name="usuario", type="string", length=25, unique=true)
     */
    private $usuario;

    /**
     * @var string
     *
     * @ORM\Column(name="clave", type="string", length=255)
     */
    private $clave;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Rol", inversedBy="usuarios")
     */
    private $rol;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Reservacion", mappedBy="usuario")
     */
    private $reservaciones;

    /**
     * @ORM\Column(type="integer")
     */
    private $noSolapin;
    
    /**
     * @ORM\Column(type="string")
     */
    private $nombre;
    
    /**
     * @ORM\Column(type="string")
     */
    private $apellido;
    
    /**
     * @ORM\Column(type="string")
     */
    private $segundoApellido;

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
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->usuario;
    }

    /**
     * Set usuario.
     *
     * @param string $usuario
     *
     * @return Usuario
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * Get usuario.
     *
     * @return string
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set clave.
     *
     * @param string $clave
     *
     * @return Usuario
     */
    public function setClave($clave)
    {
        $this->clave = $clave;

        return $this;
    }

    /**
     * Get clave.
     *
     * @return string
     */
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object.
     *
     * @link http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->usuario,
            $this->clave,
        ]);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object.
     *
     * @link http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->usuario,
            $this->clave) = unserialize($serialized);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->rol;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->clave;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    public function getClaveAnterior()
    {
        return;
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
     * @return Usuario
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
        return $this->usuario;
    }

    /**
     * Set noSolapin
     *
     * @param integer $noSolapin
     *
     * @return Usuario
     */
    public function setNoSolapin($noSolapin)
    {
        $this->noSolapin = $noSolapin;

        return $this;
    }

    /**
     * Get noSolapin
     *
     * @return integer
     */
    public function getNoSolapin()
    {
        return $this->noSolapin;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     *
     * @return Usuario
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
     * @return Usuario
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
     * @return Usuario
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

    public function getNombreCompleto()
    {
        return "$this->nombre $this->apellido $this->segundoApellido";
    }

    /**
     * Set rol
     *
     * @param \AppBundle\Entity\Rol $rol
     *
     * @return Usuario
     */
    public function setRol(\AppBundle\Entity\Rol $rol = null)
    {
        $this->rol = $rol;

        return $this;
    }

    /**
     * Get rol
     *
     * @return \AppBundle\Entity\Rol
     */
    public function getRol()
    {
        return $this->rol;
    }
}
