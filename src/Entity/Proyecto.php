<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Proyecto
 *
 * @ORM\Table(name="proyecto", indexes={@ORM\Index(name="proyecto_responsable", columns={"responsable_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\ProyectoRepository");
 */
class Proyecto
{
    /**
     * @var int
     *
     * @ORM\Column(name="proyecto_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $proyectoId;

    /**
     * @var string
     *
     * @ORM\Column(name="caso_id", type="string", length=100, nullable=false)
     */
    private $casoId;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
     */
    private $nombre;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_inicio", type="datetime", nullable=false)
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fecha_fin", type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable_id", referencedColumnName="id")
     * })
     */
    private $responsable;

    private $protocolos;

    public function __construct($nombre,$responsable,$fechaFin,$caso)
    {
      $this->nombre = $nombre;
      $this->responsable = $responsable;
      $this->fechaInicio = new \DateTime();
      $this->casoId = $caso;
    }

    public function getProyectoId(): ?int
    {
        return $this->proyectoId;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fechaFin;
    }

    public function setFechaFin(?\DateTimeInterface $fechaFin): self
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getProtocolos(): ?Array
    {
        return $this->protocolos;
    }

    public function setProtocolos(?Array $protocolos): self
    {
        $this->protocolos = $protocolos;
        return $this;
    }



}
